<?php

namespace Tests\Unit;

use App\Events\OrderCancelled;
use App\Models\ActivityLog;
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
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CustomerOrderCancellationUnitTest extends TestCase
{
    use RefreshDatabase;

    protected Client $client;
    protected ClientPaymentMethod $paymentMethod;
    protected User $customer;
    protected User $otherCustomer;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->client = Client::create([
            'business_name' => 'Rental Outdoor Jaya',
            'subdomain' => 'outdoorjaya',
            'status' => 'aktif',
        ]);

        $this->paymentMethod = ClientPaymentMethod::create([
            'client_id' => $this->client->id,
            'bank_name' => 'BCA',
            'account_number' => '8881234567',
            'account_holder' => 'Rental Outdoor Jaya',
            'is_active' => true,
        ]);

        $this->customer = User::create([
            'name' => 'Yanuar Alda',
            'email' => 'yanuar.order@test.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        $this->otherCustomer = User::create([
            'name' => 'Other Customer',
            'email' => 'other.cust@test.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        $category = Category::create([
            'client_id' => $this->client->id,
            'name' => 'Tenda Camping',
            'slug' => 'tenda-camping',
        ]);

        $this->product = Product::create([
            'client_id' => $this->client->id,
            'category_id' => $category->id,
            'name' => 'Tenda Dome 4P',
            'slug' => 'tenda-dome-4p',
            'rental_price_per_day' => 75000,
            'status' => 'aktif',
        ]);
    }

    private function createOrderFixture(string $status = 'menunggu_pembayaran', array $attributes = []): Order
    {
        return Order::create(array_merge([
            'client_id' => $this->client->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-TEST-' . uniqid(),
            'status' => $status,
            'rental_start_date' => now()->addDay(),
            'rental_end_date' => now()->addDays(3),
            'duration_days' => 2,
            'total_amount' => 150000,
            'shipping_method' => 'ambil_toko',
            'shipping_address' => 'Outlet Toko Rental',
        ], $attributes));
    }

    /**
     * UT-07-021: getCanCancelAttribute status logic matrix
     */
    public function test_order_can_cancel_attribute_status_matrix(): void
    {
        // 1. Status menunggu_pembayaran -> true
        $orderUnpaid = $this->createOrderFixture('menunggu_pembayaran');
        $this->assertTrue($orderUnpaid->can_cancel);

        // 2. Status belum_dibayar -> true
        $orderBelumBayar = $this->createOrderFixture('belum_dibayar');
        $this->assertTrue($orderBelumBayar->can_cancel);

        // 3. Status menunggu_konfirmasi TANPA pembayaran terverifikasi -> true
        $orderMenungguKonfirmasi = $this->createOrderFixture('menunggu_konfirmasi');
        $this->assertTrue($orderMenungguKonfirmasi->can_cancel);

        // 4. Status menunggu_konfirmasi DENGAN pembayaran terverifikasi -> false
        Payment::create([
            'order_id' => $orderMenungguKonfirmasi->id,
            'client_payment_method_id' => $this->paymentMethod->id,
            'amount' => 150000,
            'payment_proof' => 'payments/proof.jpg',
            'status' => 'terverifikasi',
        ]);
        $orderMenungguKonfirmasi->refresh();
        $this->assertFalse($orderMenungguKonfirmasi->can_cancel);

        // 5. Status sedang_disewa, dikirim, selesai, batal -> false
        $orderSewa = $this->createOrderFixture('sedang_disewa');
        $this->assertFalse($orderSewa->can_cancel);

        $orderSelesai = $this->createOrderFixture('selesai');
        $this->assertFalse($orderSelesai->can_cancel);

        $orderBatal = $this->createOrderFixture('batal');
        $this->assertFalse($orderBatal->can_cancel);
    }

    /**
     * UT-07-022: Auto-deallocation of physical units and restoration to 'tersedia'
     */
    public function test_customer_cancel_order_deallocates_units_and_restores_status(): void
    {
        $order = $this->createOrderFixture('menunggu_pembayaran');

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price_per_day' => 75000,
            'subtotal' => 150000,
        ]);

        $unit = EquipmentUnit::create([
            'client_id' => $this->client->id,
            'product_id' => $this->product->id,
            'unit_code' => 'UNIT-TND-001',
            'serial_number' => 'SN-TND-001',
            'status' => 'dipesan',
            'condition' => 'baik',
        ]);

        $oiu = OrderItemUnit::create([
            'order_item_id' => $orderItem->id,
            'equipment_unit_id' => $unit->id,
        ]);

        $this->assertDatabaseHas('order_item_units', ['id' => $oiu->id]);
        $this->assertEquals('dipesan', $unit->fresh()->status);

        // Customer cancels order
        $response = $this->actingAs($this->customer)
            ->from("/my-orders/{$order->id}")
            ->post("/my-orders/{$order->id}/cancel", [
                'reason' => 'Batal sewa karena cuaca buruk',
            ]);

        $response->assertRedirect("/my-orders/{$order->id}");
        $response->assertSessionHas('success');

        // Order item units must be deleted (auto-deallocated)
        $this->assertDatabaseMissing('order_item_units', ['id' => $oiu->id]);

        // Equipment unit must be restored to 'tersedia'
        $this->assertEquals('tersedia', $unit->fresh()->status);
    }

    /**
     * UT-07-023: Refund status determination (tidak_ada vs menunggu_refund)
     */
    public function test_customer_cancel_order_refund_status_determination(): void
    {
        // Case A: Unpaid order -> refund_status: 'tidak_ada'
        $orderUnpaid = $this->createOrderFixture('menunggu_pembayaran');

        $this->actingAs($this->customer)
            ->post("/my-orders/{$orderUnpaid->id}/cancel", [
                'reason' => 'Tidak jadi sewa',
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $orderUnpaid->id,
            'status' => 'batal',
            'refund_status' => 'tidak_ada',
            'cancellation_reason' => 'Tidak jadi sewa',
        ]);

        // Case B: Order with payment record -> refund_status: 'menunggu_refund'
        $orderWithPayment = $this->createOrderFixture('menunggu_konfirmasi');
        Payment::create([
            'order_id' => $orderWithPayment->id,
            'client_payment_method_id' => $this->paymentMethod->id,
            'amount' => 150000,
            'payment_proof' => 'payments/proof.jpg',
            'status' => 'menunggu_verifikasi',
        ]);

        $this->actingAs($this->customer)
            ->post("/my-orders/{$orderWithPayment->id}/cancel", [
                'reason' => 'Salah transfer ingin refund',
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $orderWithPayment->id,
            'status' => 'batal',
            'refund_status' => 'menunggu_refund',
            'cancellation_reason' => 'Salah transfer ingin refund',
        ]);
    }

    /**
     * UT-07-024: Authorization guard blocks other customers from cancelling orders
     */
    public function test_cancel_order_authorization_guard_blocks_non_owner(): void
    {
        $order = $this->createOrderFixture('menunggu_pembayaran');

        // Other customer tries to cancel
        $response = $this->actingAs($this->otherCustomer)
            ->post("/my-orders/{$order->id}/cancel", [
                'reason' => 'Mencoba membatalkan pesanan orang lain',
            ]);

        $response->assertStatus(403);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'menunggu_pembayaran',
        ]);
    }

    /**
     * UT-07-025: Guard blocks cancellation of already rented/delivered orders
     */
    public function test_cancel_order_guard_blocks_active_rented_orders(): void
    {
        $orderRented = $this->createOrderFixture('sedang_disewa');

        $response = $this->actingAs($this->customer)
            ->from("/my-orders/{$orderRented->id}")
            ->post("/my-orders/{$orderRented->id}/cancel", [
                'reason' => 'Batal mendadak di tengah masa sewa',
            ]);

        $response->assertRedirect("/my-orders/{$orderRented->id}");
        $response->assertSessionHas('error', 'Pesanan ini tidak dapat dibatalkan.');

        $this->assertDatabaseHas('orders', [
            'id' => $orderRented->id,
            'status' => 'sedang_disewa',
        ]);
    }

    /**
     * UT-07-026: ActivityLog audit trail and OrderCancelled event dispatch
     */
    public function test_cancel_order_audit_trail_and_event_dispatch(): void
    {
        Event::fake([OrderCancelled::class]);

        $order = $this->createOrderFixture('menunggu_pembayaran');

        $this->actingAs($this->customer)
            ->post("/my-orders/{$order->id}/cancel", [
                'reason' => 'Jadwal hiking ditunda',
            ]);

        // Verify ActivityLog created
        $this->assertDatabaseHas('activity_logs', [
            'client_id' => $this->client->id,
            'user_id' => $this->customer->id,
            'action' => 'order_cancelled_by_customer',
            'module' => 'orders',
        ]);

        $log = ActivityLog::where('action', 'order_cancelled_by_customer')->latest()->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString($order->order_number, $log->description);
        $this->assertStringContainsString('Jadwal hiking ditunda', $log->description);

        // Verify OrderCancelled event dispatched
        Event::assertDispatched(OrderCancelled::class, function ($event) use ($order) {
            return $event->order->id === $order->id;
        });
    }
}
