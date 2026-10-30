<?php

namespace Tests\Feature\Admin;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\EquipmentUnit;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemUnit;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogisticsAndActivityIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_processing_shipment_creates_activity_log(): void
    {
        $client = Client::create([
            'business_name' => 'Toko Bayi Ceria',
            'subdomain' => 'tokobayiceria',
            'status' => 'aktif',
        ]);

        $admin = User::factory()->create([
            'client_id' => $client->id,
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        $customer = User::factory()->create([
            'client_id' => $client->id,
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        $product = Product::create([
            'client_id' => $client->id,
            'name' => 'Stroller Bayi Lipat',
            'slug' => 'stroller-bayi-lipat',
            'rental_price_per_day' => 50000,
            'late_fee_per_hour' => 10000,
            'stock' => 5,
            'status' => 'aktif',
        ]);

        $unit = EquipmentUnit::create([
            'client_id' => $client->id,
            'product_id' => $product->id,
            'unit_code' => 'STR-001',
            'condition' => 'baik',
            'status' => 'siap_disewa',
        ]);

        $order = Order::create([
            'client_id' => $client->id,
            'user_id' => $customer->id,
            'order_number' => 'ORD-TEST-001',
            'rental_start_date' => now(),
            'rental_end_date' => now()->addDays(3),
            'duration_days' => 3,
            'total_amount' => 150000,
            'shipping_address' => 'Jl. Merdeka No. 10 Jakarta',
            'shipping_method' => 'kurir',
            'status' => 'siap_kirim',
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price_per_day' => 50000,
            'subtotal' => 150000,
        ]);

        OrderItemUnit::create([
            'order_item_id' => $item->id,
            'equipment_unit_id' => $unit->id,
        ]);

        $response = $this->actingAs($admin)->post('/admin/shipments/' . $order->id . '/process', [
            'courier_name' => 'JNE',
            'tracking_number' => 'JNE123456789',
        ]);

        $response->assertRedirect(route('admin.shipments.index'));
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'dikirim',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'client_id' => $client->id,
            'user_id' => $admin->id,
            'module' => 'shipments',
            'action' => 'order_shipped',
        ]);
    }
}
