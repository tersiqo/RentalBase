<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Order;
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

    public function test_it_07_005_customer_can_cancel_own_unpaid_order_successfully(): void
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

    public function test_customer_cannot_cancel_active_rented_order(): void
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
}
