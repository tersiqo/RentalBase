<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Client;
use App\Models\ClientPaymentMethod;
use App\Models\IdentityGuarantee;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerOrderDetailIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function createFullOrderDetailFixture(): array
    {
        $client = Client::create([
            'business_name' => 'Rental Maju Jaya',
            'subdomain' => 'rentalmaju',
            'status' => 'aktif',
        ]);

        $customer = User::factory()->create([
            'role' => 'customer',
            'client_id' => $client->id,
            'name' => 'Budi Santoso',
            'email' => 'budi_' . uniqid() . '@example.com',
        ]);

        $otherCustomer = User::factory()->create([
            'role' => 'customer',
            'client_id' => $client->id,
            'name' => 'Siti Rahma',
            'email' => 'siti_' . uniqid() . '@example.com',
        ]);

        $category = Category::create([
            'client_id' => $client->id,
            'name' => 'Kamera & Lensa',
            'slug' => 'kamera-lensa-' . uniqid(),
        ]);

        $product = Product::create([
            'client_id' => $client->id,
            'category_id' => $category->id,
            'name' => 'Sony Alpha A7 IV',
            'slug' => 'sony-alpha-a7-iv-' . uniqid(),
            'rental_price_per_day' => 250000,
            'status' => 'aktif',
        ]);

        $paymentMethod = ClientPaymentMethod::create([
            'client_id' => $client->id,
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_holder' => 'PT Rental Maju',
            'is_active' => true,
        ]);

        $order = Order::create([
            'client_id' => $client->id,
            'user_id' => $customer->id,
            'order_number' => 'ORD-20261015-888',
            'rental_start_date' => now()->addDay(),
            'rental_end_date' => now()->addDays(3),
            'duration_days' => 2,
            'total_amount' => 500000,
            'shipping_method' => 'kurir',
            'shipping_address' => 'Jl. Soekarno Hatta No. 45, Malang',
            'status' => 'dikirim',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price_per_day' => 250000,
            'subtotal' => 500000,
        ]);

        IdentityGuarantee::create([
            'order_id' => $order->id,
            'customer_id' => $customer->id,
            'full_name' => 'Budi Santoso',
            'identity_number' => '3578012345670001',
            'identity_document_image' => 'guarantees/ktp_budi.jpg',
            'face_image' => 'guarantees/face_budi.jpg',
            'identity_address' => 'Jl. Soekarno Hatta No. 45, Malang',
            'status' => 'diverifikasi',
        ]);

        Shipment::create([
            'order_id' => $order->id,
            'shipping_method' => 'kurir',
            'courier_name' => 'JNE Express',
            'tracking_number' => 'JNE123456789ID',
            'delivery_address' => 'Jl. Soekarno Hatta No. 45, Malang',
            'status' => 'dikirim',
            'shipped_at' => now(),
        ]);

        Payment::create([
            'order_id' => $order->id,
            'client_payment_method_id' => $paymentMethod->id,
            'amount' => 500000,
            'payment_proof' => 'payments/proof_1.jpg',
            'status' => 'terverifikasi',
            'paid_at' => now(),
        ]);

        return compact('client', 'customer', 'otherCustomer', 'product', 'paymentMethod', 'order');
    }

    public function test_it_09_012_customer_can_view_order_detail_stepper_and_tracking(): void
    {
        $fixture = $this->createFullOrderDetailFixture();

        $response = $this->actingAs($fixture['customer'])
            ->withSession(['active_client_id' => $fixture['client']->id, 'active_client_subdomain' => $fixture['client']->subdomain])
            ->get("/my-orders/{$fixture['order']->id}");

        $response->assertStatus(200);
        $response->assertViewIs('customer.orders.show');
        $response->assertViewHas('order');
        $response->assertViewHas('client');
    }

    public function test_order_detail_renders_stepper_progress_and_order_summary(): void
    {
        $fixture = $this->createFullOrderDetailFixture();

        $response = $this->actingAs($fixture['customer'])
            ->withSession(['active_client_id' => $fixture['client']->id, 'active_client_subdomain' => $fixture['client']->subdomain])
            ->get("/my-orders/{$fixture['order']->id}");

        $response->assertSee('ORD-20261015-888');
        $response->assertSee('Detail Pesanan');
        $response->assertSee('Progres Pesanan');
        $response->assertSee('Dikirim');
        $response->assertSee('Rincian Sewa');
        $response->assertSee('Sony Alpha A7 IV');
        $response->assertSee('Rp 500.000');
    }

    public function test_order_detail_renders_identity_verification_guarantee_panel(): void
    {
        $fixture = $this->createFullOrderDetailFixture();

        $response = $this->actingAs($fixture['customer'])
            ->withSession(['active_client_id' => $fixture['client']->id, 'active_client_subdomain' => $fixture['client']->subdomain])
            ->get("/my-orders/{$fixture['order']->id}");

        $response->assertSee('Verifikasi Identitas Penyewa');
        $response->assertSee('Budi Santoso');
        $response->assertSee('3578012345670001');
    }

    public function test_order_detail_renders_shipment_logistics_and_courier_tracking(): void
    {
        $fixture = $this->createFullOrderDetailFixture();

        $response = $this->actingAs($fixture['customer'])
            ->withSession(['active_client_id' => $fixture['client']->id, 'active_client_subdomain' => $fixture['client']->subdomain])
            ->get("/my-orders/{$fixture['order']->id}");

        $response->assertSee('Informasi Pengiriman');
        $response->assertSee('JNE Express');
        $response->assertSee('JNE123456789ID');
        $response->assertSee('Konfirmasi Terima Barang');
    }

    public function test_order_detail_renders_payment_method_instruction_panel(): void
    {
        $fixture = $this->createFullOrderDetailFixture();

        $response = $this->actingAs($fixture['customer'])
            ->withSession(['active_client_id' => $fixture['client']->id, 'active_client_subdomain' => $fixture['client']->subdomain])
            ->get("/my-orders/{$fixture['order']->id}");

        $response->assertSee('Pembayaran');
        $response->assertSee('BCA');
    }

    public function test_unauthorized_customer_cannot_view_other_customers_order(): void
    {
        $fixture = $this->createFullOrderDetailFixture();

        $response = $this->actingAs($fixture['otherCustomer'])
            ->withSession(['active_client_id' => $fixture['client']->id, 'active_client_subdomain' => $fixture['client']->subdomain])
            ->get("/my-orders/{$fixture['order']->id}");

        $response->assertStatus(404);
    }

    public function test_order_detail_renders_cancellation_and_refund_status_panel(): void
    {
        $fixture = $this->createFullOrderDetailFixture();

        $cancelledOrder = Order::create([
            'client_id' => $fixture['client']->id,
            'user_id' => $fixture['customer']->id,
            'order_number' => 'ORD-20261015-999',
            'rental_start_date' => now()->addDay(),
            'rental_end_date' => now()->addDays(2),
            'duration_days' => 1,
            'total_amount' => 250000,
            'shipping_method' => 'kurir',
            'shipping_address' => 'Jl. Soekarno Hatta No. 45, Malang',
            'status' => 'batal',
            'cancellation_reason' => 'Unit rusak saat inspeksi sebelum dikirim.',
            'refund_status' => 'refund_selesai',
            'refund_proof' => 'refunds/transfer_refund_999.jpg',
        ]);

        $response = $this->actingAs($fixture['customer'])
            ->withSession(['active_client_id' => $fixture['client']->id, 'active_client_subdomain' => $fixture['client']->subdomain])
            ->get("/my-orders/{$cancelledOrder->id}");

        $response->assertStatus(200);
        $response->assertSee('Status Pembatalan');
        $response->assertSee('Unit rusak saat inspeksi sebelum dikirim.');
        $response->assertSee('transfer_refund_999.jpg');
    }
}

