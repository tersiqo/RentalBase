<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Client;
use App\Models\ClientPaymentMethod;
use App\Models\EquipmentUnit;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemUnit;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerCancelOrderIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function createCustomerWithOrder(string $status = 'menunggu_pembayaran'): array
    {
        $client = Client::create([
            'business_name' => 'Rental Outdoor Jaya',
            'subdomain' => 'outdoorjaya',
            'status' => 'aktif',
        ]);

        $customer = User::factory()->create([
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        $order = Order::create([
            'client_id' => $client->id,
            'user_id' => $customer->id,
            'order_number' => 'ORD-TEST-CANCEL-' . uniqid(),
            'status' => $status,
            'rental_start_date' => now()->addDay(),
            'rental_end_date' => now()->addDays(3),
            'duration_days' => 2,
            'total_amount' => 300000,
            'shipping_method' => 'ambil_toko',
            'shipping_address' => 'Ambil langsung di Outlet Toko Outdoor Jaya',
        ]);

        return [$client, $customer, $order];
    }

    /**
     * IT-07-023: Customer membatalkan pesanan sewa miliknya sendiri yang belum dibayar
     */
    public function test_it_07_023_customer_can_cancel_own_unpaid_order_successfully(): void
    {
        [$client, $customer, $order] = $this->createCustomerWithOrder('menunggu_pembayaran');

        $response = $this->actingAs($customer)
            ->from("/my-orders/{$order->id}")
            ->post("/my-orders/{$order->id}/cancel", [
                'reason' => 'Perubahan jadwal kegiatan mendadak.',
            ]);

        $response->assertRedirect("/my-orders/{$order->id}");
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'batal',
            'cancellation_reason' => 'Perubahan jadwal kegiatan mendadak.',
            'refund_status' => 'tidak_ada',
        ]);
    }

    /**
     * Backward compatibility alias
     */
    public function test_it_07_005_customer_can_cancel_own_unpaid_order_successfully(): void
    {
        $this->test_it_07_023_customer_can_cancel_own_unpaid_order_successfully();
    }

    /**
     * IT-07-024: Pelepasan unit fisik otomatis (auto-deallocation) saat customer membatalkan pesanan
     */
    public function test_it_07_024_customer_cancel_order_deallocates_physical_units(): void
    {
        [$client, $customer, $order] = $this->createCustomerWithOrder('menunggu_pembayaran');

        $category = Category::create([
            'client_id' => $client->id,
            'name' => 'Tenda Dome',
            'slug' => 'tenda-dome',
        ]);

        $product = Product::create([
            'client_id' => $client->id,
            'category_id' => $category->id,
            'name' => 'Tenda Kapasitas 4 Orang',
            'slug' => 'tenda-4p',
            'rental_price_per_day' => 150000,
            'status' => 'aktif',
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price_per_day' => 150000,
            'subtotal' => 300000,
        ]);

        $unit = EquipmentUnit::create([
            'client_id' => $client->id,
            'product_id' => $product->id,
            'unit_code' => 'UNIT-TND-01',
            'serial_number' => 'SN-TND-01',
            'status' => 'dipesan',
            'condition' => 'baik',
        ]);

        $oiu = OrderItemUnit::create([
            'order_item_id' => $orderItem->id,
            'equipment_unit_id' => $unit->id,
        ]);

        $this->assertDatabaseHas('order_item_units', ['id' => $oiu->id]);

        $response = $this->actingAs($customer)
            ->from("/my-orders/{$order->id}")
            ->post("/my-orders/{$order->id}/cancel", [
                'reason' => 'Batal sewa karena cuaca buruk',
            ]);

        $response->assertRedirect("/my-orders/{$order->id}");
        $response->assertSessionHas('success');

        // Order item units must be removed
        $this->assertDatabaseMissing('order_item_units', ['id' => $oiu->id]);

        // Equipment unit status must be restored to 'tersedia'
        $this->assertEquals('tersedia', $unit->fresh()->status);
    }

    /**
     * IT-07-025: Pembatalan pesanan yang telah mengunggah bukti pembayaran memicu refund_status 'menunggu_refund'
     */
    public function test_it_07_025_customer_cancel_order_triggers_refund_queue_status(): void
    {
        [$client, $customer, $order] = $this->createCustomerWithOrder('menunggu_konfirmasi');

        $paymentMethod = ClientPaymentMethod::create([
            'client_id' => $client->id,
            'bank_name' => 'BCA',
            'account_number' => '8881234567',
            'account_holder' => 'Rental Outdoor Jaya',
            'is_active' => true,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'client_payment_method_id' => $paymentMethod->id,
            'amount' => 300000,
            'payment_proof' => 'payments/proof.jpg',
            'status' => 'menunggu_verifikasi',
        ]);

        $response = $this->actingAs($customer)
            ->from("/my-orders/{$order->id}")
            ->post("/my-orders/{$order->id}/cancel", [
                'reason' => 'Salah nominal transfer ingin refund',
            ]);

        $response->assertRedirect("/my-orders/{$order->id}");
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'batal',
            'refund_status' => 'menunggu_refund',
            'cancellation_reason' => 'Salah nominal transfer ingin refund',
        ]);
    }

    /**
     * Proteksi keamanan hak milik pesanan antar customer
     */
    public function test_customer_cannot_cancel_other_customer_order(): void
    {
        [$client, $ownerCustomer, $order] = $this->createCustomerWithOrder('menunggu_pembayaran');

        $otherCustomer = User::factory()->create([
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($otherCustomer)
            ->post("/my-orders/{$order->id}/cancel", [
                'reason' => 'Percobaan membatalkan pesanan orang lain',
            ]);

        $response->assertStatus(403);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'menunggu_pembayaran',
        ]);
    }

    /**
     * IT-07-026: Proteksi penolakan pembatalan pada pesanan yang sudah diserahterimakan/aktif disewa
     */
    public function test_it_07_026_customer_cannot_cancel_active_rented_order(): void
    {
        [$client, $customer, $order] = $this->createCustomerWithOrder('sedang_disewa');

        $response = $this->actingAs($customer)
            ->from("/my-orders/{$order->id}")
            ->post("/my-orders/{$order->id}/cancel", [
                'reason' => 'Ingin membatalkan saat barang sudah disewa',
            ]);

        $response->assertRedirect("/my-orders/{$order->id}");
        $response->assertSessionHas('error', 'Pesanan ini tidak dapat dibatalkan.');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'sedang_disewa',
        ]);
    }

    /**
     * Backward compatibility alias
     */
    public function test_customer_cannot_cancel_active_rented_order(): void
    {
        $this->test_it_07_026_customer_cannot_cancel_active_rented_order();
    }
}
