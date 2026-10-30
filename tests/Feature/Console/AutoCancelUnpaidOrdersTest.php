<?php

namespace Tests\Feature\Console;

use App\Models\Client;
use App\Models\EquipmentUnit;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemUnit;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AutoCancelUnpaidOrdersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_cancels_orders_unpaid_after_two_hours(): void
    {
        $client = Client::create(['business_name' => 'Toko A', 'subdomain' => 'tokoa']);
        $category = \App\Models\Category::create(['client_id' => $client->id, 'name' => 'Kamera', 'slug' => 'kamera']);
        $user = User::factory()->create(['client_id' => $client->id, 'role' => 'customer']);
        $product = Product::create([
            'client_id' => $client->id,
            'category_id' => $category->id,
            'name' => 'Kamera DSLR',
            'slug' => 'kamera-dslr',
            'rental_price_per_day' => 100000,
            'late_fee_per_hour' => 10000,
            'status' => 'tersedia',
        ]);
        $unit = EquipmentUnit::create([
            'client_id' => $client->id,
            'product_id' => $product->id,
            'unit_code' => 'CAM-01',
            'status' => 'disewa',
        ]);

        $order = Order::create([
            'client_id' => $client->id,
            'user_id' => $user->id,
            'order_number' => 'ORD-TEST-001',
            'rental_start_date' => now()->addDay(),
            'rental_end_date' => now()->addDays(3),
            'duration_days' => 2,
            'total_amount' => 200000,
            'shipping_address' => 'Alamat Pengiriman',
            'status' => 'menunggu_pembayaran',
            'updated_at' => Carbon::now()->subHours(3),
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price_per_day' => 100000,
            'subtotal' => 200000,
        ]);

        OrderItemUnit::create([
            'order_item_id' => $item->id,
            'equipment_unit_id' => $unit->id,
        ]);

        $exitCode = Artisan::call('orders:auto-cancel-unpaid');

        $this->assertEquals(0, $exitCode);
        $this->assertEquals('dibatalkan', $order->fresh()->status);
        $this->assertStringContainsString('2 jam', $order->fresh()->cancellation_reason);
        $this->assertEquals('tersedia', $unit->fresh()->status);
        $this->assertDatabaseMissing('order_item_units', ['order_item_id' => $item->id]);
    }

    public function test_does_not_cancel_recent_unpaid_order(): void
    {
        $client = Client::create(['business_name' => 'Toko A', 'subdomain' => 'tokoa']);
        $user = User::factory()->create(['client_id' => $client->id, 'role' => 'customer']);

        $order = Order::create([
            'client_id' => $client->id,
            'user_id' => $user->id,
            'order_number' => 'ORD-RECENT-001',
            'rental_start_date' => now()->addDay(),
            'rental_end_date' => now()->addDays(3),
            'duration_days' => 2,
            'total_amount' => 200000,
            'shipping_address' => 'Alamat Pengiriman',
            'status' => 'menunggu_pembayaran',
            'updated_at' => Carbon::now()->subMinutes(30), // Baru 30 menit
        ]);

        Artisan::call('orders:auto-cancel-unpaid');

        $this->assertEquals('menunggu_pembayaran', $order->fresh()->status);
    }

    public function test_does_not_cancel_paid_order(): void
    {
        $client = Client::create(['business_name' => 'Toko A', 'subdomain' => 'tokoa']);
        $user = User::factory()->create(['client_id' => $client->id, 'role' => 'customer']);

        $order = Order::create([
            'client_id' => $client->id,
            'user_id' => $user->id,
            'order_number' => 'ORD-PAID-001',
            'rental_start_date' => now()->addDay(),
            'rental_end_date' => now()->addDays(3),
            'duration_days' => 2,
            'total_amount' => 200000,
            'shipping_address' => 'Alamat Pengiriman',
            'status' => 'siap_kirim',
            'updated_at' => Carbon::now()->subHours(5),
        ]);

        Artisan::call('orders:auto-cancel-unpaid');

        $this->assertEquals('siap_kirim', $order->fresh()->status);
    }

    public function test_command_is_idempotent(): void
    {
        $client = Client::create(['business_name' => 'Toko A', 'subdomain' => 'tokoa']);
        $user = User::factory()->create(['client_id' => $client->id, 'role' => 'customer']);

        $order = Order::create([
            'client_id' => $client->id,
            'user_id' => $user->id,
            'order_number' => 'ORD-IDEM-001',
            'rental_start_date' => now()->addDay(),
            'rental_end_date' => now()->addDays(3),
            'duration_days' => 2,
            'total_amount' => 200000,
            'shipping_address' => 'Alamat Pengiriman',
            'status' => 'menunggu_pembayaran',
            'updated_at' => Carbon::now()->subHours(4),
        ]);

        Artisan::call('orders:auto-cancel-unpaid');
        $this->assertEquals('dibatalkan', $order->fresh()->status);

        // Jalankan kedua kalinya
        $exitCode = Artisan::call('orders:auto-cancel-unpaid');
        $this->assertEquals(0, $exitCode);
        $this->assertEquals('dibatalkan', $order->fresh()->status);
    }
}
