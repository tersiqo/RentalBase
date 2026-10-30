<?php

namespace Tests\Feature\Orders;

use App\Models\Client;
use App\Models\EquipmentUnit;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemUnit;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrderConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_prevents_overlapping_unit_allocation_double_booking(): void
    {
        $client = Client::create(['business_name' => 'Rental Kamera Jogja', 'subdomain' => 'jogjarental']);
        $admin = User::factory()->create([
            'client_id' => $client->id,
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);
        $customer1 = User::factory()->create(['role' => 'customer']);
        $customer2 = User::factory()->create(['role' => 'customer']);

        $category = \App\Models\Category::create(['client_id' => $client->id, 'name' => 'Kamera', 'slug' => 'kamera']);
        $product = Product::create([
            'client_id' => $client->id,
            'category_id' => $category->id,
            'name' => 'Sony A7 IV',
            'slug' => 'sony-a7-iv',
            'rental_price_per_day' => 250000,
            'status' => 'tersedia',
        ]);

        $unit = EquipmentUnit::create([
            'client_id' => $client->id,
            'product_id' => $product->id,
            'unit_code' => 'SONY-01',
            'status' => 'tersedia',
        ]);

        // Order 1: Tanggal 10 Nov - 15 Nov
        $order1 = Order::create([
            'client_id' => $client->id,
            'user_id' => $customer1->id,
            'order_number' => 'ORD-NOV-001',
            'rental_start_date' => Carbon::parse('2026-11-10 10:00:00'),
            'rental_end_date' => Carbon::parse('2026-11-15 10:00:00'),
            'duration_days' => 5,
            'total_amount' => 1250000,
            'shipping_address' => 'Jogja',
            'status' => 'siap_kirim',
        ]);
        $item1 = OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price_per_day' => 250000,
            'subtotal' => 1250000,
        ]);

        // Alokasikan unit ke Order 1
        $response1 = $this->actingAs($admin)->post("/admin/orders/{$order1->id}/allocate", [
            'order_item_id' => $item1->id,
            'equipment_unit_id' => $unit->id,
        ]);
        $response1->assertSessionHas('success');
        $this->assertDatabaseHas('order_item_units', [
            'order_item_id' => $item1->id,
            'equipment_unit_id' => $unit->id,
        ]);

        // Order 2: Tanggal bertabrakan (12 Nov - 17 Nov)
        $order2 = Order::create([
            'client_id' => $client->id,
            'user_id' => $customer2->id,
            'order_number' => 'ORD-NOV-002',
            'rental_start_date' => Carbon::parse('2026-11-12 10:00:00'),
            'rental_end_date' => Carbon::parse('2026-11-17 10:00:00'),
            'duration_days' => 5,
            'total_amount' => 1250000,
            'shipping_address' => 'Sleman',
            'status' => 'menunggu_verifikasi',
        ]);
        $item2 = OrderItem::create([
            'order_id' => $order2->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price_per_day' => 250000,
            'subtotal' => 1250000,
        ]);

        // Coba alokasikan unit yang sama ke Order 2 pada jadwal bertabrakan -> HARUS DITOLAK
        $response2 = $this->actingAs($admin)->post("/admin/orders/{$order2->id}/allocate", [
            'order_item_id' => $item2->id,
            'equipment_unit_id' => $unit->id,
        ]);

        $response2->assertSessionHas('error');
        $response2->assertSessionHas('error', 'Maaf, unit yang dipilih sudah tidak tersedia pada periode tersebut. Silakan pilih unit atau jadwal lain.');

        // Pastikan tidak ada data parsial atau double-booking di database
        $this->assertDatabaseMissing('order_item_units', [
            'order_item_id' => $item2->id,
            'equipment_unit_id' => $unit->id,
        ]);
    }

    public function test_allows_allocation_on_non_overlapping_dates(): void
    {
        $client = Client::create(['business_name' => 'Rental Kamera Jogja', 'subdomain' => 'jogjarental']);
        $admin = User::factory()->create([
            'client_id' => $client->id,
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);
        $customer1 = User::factory()->create(['role' => 'customer']);
        $customer3 = User::factory()->create(['role' => 'customer']);

        $category = \App\Models\Category::create(['client_id' => $client->id, 'name' => 'Kamera', 'slug' => 'kamera-2']);
        $product = Product::create([
            'client_id' => $client->id,
            'category_id' => $category->id,
            'name' => 'Sony A7 IV',
            'slug' => 'sony-a7-iv',
            'rental_price_per_day' => 250000,
            'status' => 'tersedia',
        ]);

        $unit = EquipmentUnit::create([
            'client_id' => $client->id,
            'product_id' => $product->id,
            'unit_code' => 'SONY-02',
            'status' => 'tersedia',
        ]);

        // Order 1: 10 Nov - 15 Nov
        $order1 = Order::create([
            'client_id' => $client->id,
            'user_id' => $customer1->id,
            'order_number' => 'ORD-NON-001',
            'rental_start_date' => Carbon::parse('2026-11-10 10:00:00'),
            'rental_end_date' => Carbon::parse('2026-11-15 10:00:00'),
            'duration_days' => 5,
            'total_amount' => 1250000,
            'shipping_address' => 'Jogja',
            'status' => 'siap_kirim',
        ]);
        $item1 = OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price_per_day' => 250000,
            'subtotal' => 1250000,
        ]);

        $this->actingAs($admin)->post("/admin/orders/{$order1->id}/allocate", [
            'order_item_id' => $item1->id,
            'equipment_unit_id' => $unit->id,
        ]);

        // Order 3: 20 Nov - 25 Nov (Tidak tumpang tindih)
        $order3 = Order::create([
            'client_id' => $client->id,
            'user_id' => $customer3->id,
            'order_number' => 'ORD-NON-003',
            'rental_start_date' => Carbon::parse('2026-11-20 10:00:00'),
            'rental_end_date' => Carbon::parse('2026-11-25 10:00:00'),
            'duration_days' => 5,
            'total_amount' => 1250000,
            'shipping_address' => 'Jogja',
            'status' => 'menunggu_verifikasi',
        ]);
        $item3 = OrderItem::create([
            'order_id' => $order3->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price_per_day' => 250000,
            'subtotal' => 1250000,
        ]);

        $response3 = $this->actingAs($admin)->post("/admin/orders/{$order3->id}/allocate", [
            'order_item_id' => $item3->id,
            'equipment_unit_id' => $unit->id,
        ]);

        $response3->assertSessionHas('success');
        $this->assertDatabaseHas('order_item_units', [
            'order_item_id' => $item3->id,
            'equipment_unit_id' => $unit->id,
        ]);
    }

    public function test_admin_can_process_order_refund_with_proof(): void
    {
        $client = Client::create(['business_name' => 'Rental Kamera Jogja', 'subdomain' => 'jogjarental']);
        $admin = User::factory()->create([
            'client_id' => $client->id,
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);
        $customer = User::factory()->create(['role' => 'customer']);

        $order = Order::create([
            'client_id' => $client->id,
            'user_id' => $customer->id,
            'order_number' => 'ORD-REFUND-001',
            'rental_start_date' => now()->addDay(),
            'rental_end_date' => now()->addDays(2),
            'duration_days' => 1,
            'total_amount' => 200000,
            'shipping_address' => 'Jogja',
            'status' => 'batal',
            'refund_status' => 'menunggu_refund',
        ]);

        $file = UploadedFile::fake()->create('bukti_refund.png', 500, 'image/png');

        $response = $this->actingAs($admin)->post("/admin/orders/{$order->id}/refund", [
            'refund_proof' => $file,
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('refund_selesai', $order->fresh()->refund_status);
        $this->assertNotNull($order->fresh()->refund_proof);
        Storage::disk('public')->assertExists($order->fresh()->refund_proof);
    }
}
