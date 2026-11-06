<?php

namespace Tests\Feature\Admin;

use App\Models\ActivityLog;
use App\Models\Category;
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

        $category = Category::create([
            'client_id' => $client->id,
            'name' => 'Stroller',
            'slug' => 'stroller',
        ]);

        $product = Product::create([
            'client_id' => $client->id,
            'category_id' => $category->id,
            'name' => 'Stroller Bayi Lipat',
            'slug' => 'stroller-bayi-lipat',
            'rental_price_per_day' => 50000,
            'late_fee_per_hour' => 10000,
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

    public function test_shipment_process_auto_syncs_rental_hours_when_handover_is_after_planned_start(): void
    {
        $client = Client::create([
            'business_name' => 'Rental Cepat Aman',
            'subdomain' => 'rentalcepat',
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

        $category = Category::create([
            'client_id' => $client->id,
            'name' => 'Bouncer',
            'slug' => 'bouncer',
        ]);

        $product = Product::create([
            'client_id' => $client->id,
            'category_id' => $category->id,
            'name' => 'Bouncer Elektrik',
            'slug' => 'bouncer-elektrik',
            'rental_price_per_day' => 40000,
            'status' => 'aktif',
        ]);

        $unit = EquipmentUnit::create([
            'client_id' => $client->id,
            'product_id' => $product->id,
            'unit_code' => 'BNC-001',
            'status' => 'siap_disewa',
        ]);

        // Rencana sewa dibuat 3 jam yang lalu (misal karena nunggu verifikasi KTP & bayar)
        $plannedStart = now()->subHours(3)->second(0);
        $plannedEnd = (clone $plannedStart)->addDays(2); // 3 hari sewa

        $order = Order::create([
            'client_id' => $client->id,
            'user_id' => $customer->id,
            'order_number' => 'ORD-SYNC-001',
            'rental_start_date' => $plannedStart,
            'rental_end_date' => $plannedEnd,
            'duration_days' => 3,
            'total_amount' => 120000,
            'shipping_address' => 'Pelanggan mengambil langsung di toko rental',
            'shipping_method' => Order::SHIPPING_AMBIL_TOKO,
            'status' => 'siap_kirim',
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price_per_day' => 40000,
            'subtotal' => 120000,
        ]);

        OrderItemUnit::create([
            'order_item_id' => $item->id,
            'equipment_unit_id' => $unit->id,
        ]);

        $response = $this->actingAs($admin)->post('/admin/shipments/' . $order->id . '/process', []);

        $response->assertRedirect(route('admin.shipments.index'));

        $order->refresh();
        $this->assertSame('sedang_disewa', $order->status);

        // Jam mulai & batas kembali otomatis di-sync ke waktu sekarang (handover)
        $this->assertTrue($order->rental_start_date->greaterThan($plannedStart));
        $this->assertSame(3, (int) $order->duration_days);
        $this->assertSame(
            $order->rental_start_date->format('H:i'),
            $order->rental_end_date->format('H:i')
        );

        $activity = ActivityLog::where('client_id', $client->id)
            ->where('action', 'order_handed_over')
            ->latest('id')
            ->first();
        $this->assertNotNull($activity);
        $this->assertStringContainsString('disinkronkan', $activity->description);
    }

    public function test_shipment_process_preserves_future_booking_dates_when_processed_ahead_of_time(): void
    {
        $client = Client::create([
            'business_name' => 'Rental Maju Jaya',
            'subdomain' => 'rentalmaju',
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

        $category = Category::create([
            'client_id' => $client->id,
            'name' => 'Carseat',
            'slug' => 'carseat',
        ]);

        $product = Product::create([
            'client_id' => $client->id,
            'category_id' => $category->id,
            'name' => 'Carseat Premium',
            'slug' => 'carseat-premium',
            'rental_price_per_day' => 60000,
            'status' => 'aktif',
        ]);

        $unit = EquipmentUnit::create([
            'client_id' => $client->id,
            'product_id' => $product->id,
            'unit_code' => 'CS-001',
            'status' => 'siap_disewa',
        ]);

        // Booking masa depan: 4 hari ke depan jam 10:00
        $futureStart = now()->addDays(4)->setTime(10, 0, 0);
        $futureEnd = now()->addDays(6)->setTime(10, 0, 0);

        $order = Order::create([
            'client_id' => $client->id,
            'user_id' => $customer->id,
            'order_number' => 'ORD-FUTURE-001',
            'rental_start_date' => $futureStart,
            'rental_end_date' => $futureEnd,
            'duration_days' => 3,
            'total_amount' => 180000,
            'shipping_address' => 'Jl. Pahlawan No. 5 Malang',
            'shipping_method' => Order::SHIPPING_KURIR,
            'status' => 'siap_kirim',
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price_per_day' => 60000,
            'subtotal' => 180000,
        ]);

        OrderItemUnit::create([
            'order_item_id' => $item->id,
            'equipment_unit_id' => $unit->id,
        ]);

        // Percobaan kirim 4 hari sebelum jadwal: HARUS DITOLAK sistem
        $response = $this->actingAs($admin)->post('/admin/shipments/' . $order->id . '/process', [
            'courier_name' => 'Kurir Toko',
            'tracking_number' => 'RESI-KURIR-001',
        ]);

        $response->assertSessionHas('error');
        $order->refresh();
        $this->assertSame('siap_kirim', $order->status);
        $this->assertSame($futureStart->format('Y-m-d H:i:s'), $order->rental_start_date->format('Y-m-d H:i:s'));

        // Ketika waktu mencapai jendela H-1 untuk kurir: BOLEH DIPROSES
        \Illuminate\Support\Carbon::setTestNow($futureStart->copy()->subDay()->setTime(14, 0, 0));

        try {
            $allowedResponse = $this->actingAs($admin)->post('/admin/shipments/' . $order->id . '/process', [
                'courier_name' => 'Kurir Toko',
                'tracking_number' => 'RESI-KURIR-001',
            ]);

            $allowedResponse->assertRedirect(route('admin.shipments.index'));
            $order->refresh();
            $this->assertSame('dikirim', $order->status);
            $this->assertSame($futureStart->format('Y-m-d H:i:s'), $order->rental_start_date->format('Y-m-d H:i:s'));
            $this->assertSame($futureEnd->format('Y-m-d H:i:s'), $order->rental_end_date->format('Y-m-d H:i:s'));
            $this->assertSame(3, (int) $order->duration_days);
        } finally {
            \Illuminate\Support\Carbon::setTestNow();
        }
    }
}
