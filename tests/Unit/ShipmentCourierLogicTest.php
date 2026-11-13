<?php

namespace Tests\Unit;

use App\Models\Client;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * UT-MOD-09 — Modul Pengiriman & Logistik (Unit Testing)
 * 
 * Menguji:
 * 1. Relasi model Shipment ke model Order (belongsTo)
 * 2. Field nomor resi (tracking_number), kurir, dan metode pengiriman (shipping_method)
 * 3. Casting atribut datetime (shipped_at, delivered_at)
 * 4. Nilai default atribut status pengiriman ('diproses')
 * 5. Relasi inverse model Order ke Shipment (hasOne)
 */
class ShipmentCourierLogicTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;
    private User $customer;
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Client::create([
            'business_name' => 'Baby Rental Studio',
            'subdomain' => 'babyrental',
            'status' => 'aktif',
        ]);

        $this->customer = User::create([
            'name' => 'Zaskia Customer',
            'email' => 'zaskia.customer@example.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        $this->order = Order::create([
            'client_id' => $this->client->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-SHIP-001',
            'status' => 'siap_kirim',
            'rental_start_date' => now()->addDays(1),
            'rental_end_date' => now()->addDays(3),
            'duration_days' => 2,
            'total_amount' => 180000,
            'shipping_address' => 'Jl. Bendungan Sigura-gura No. 12, Malang',
            'shipping_method' => 'kurir',
        ]);
    }

    /**
     * UT-SHIP-001: Memastikan model Shipment memiliki relasi belongsTo ke model Order secara valid.
     */
    public function test_ut_ship_001_shipment_belongs_to_order_relationship(): void
    {
        $shipment = Shipment::create([
            'order_id' => $this->order->id,
            'shipping_method' => 'kurir',
            'tracking_number' => 'JNE-REG-99182371',
            'delivery_address' => 'Jl. Bendungan Sigura-gura No. 12, Malang',
            'courier_name' => 'JNE Express',
            'status' => 'diproses',
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $shipment->order());
        $this->assertEquals($this->order->id, $shipment->order->id);
    }

    /**
     * UT-SHIP-002: Memastikan field nomor resi (tracking_number), kurir, dan metode pengiriman tersimpan dengan akurat.
     */
    public function test_ut_ship_002_shipment_courier_and_tracking_number_attributes(): void
    {
        // 1. Pengiriman kurir ekspedisi dengan nomor resi aktif
        $shipmentCourier = Shipment::create([
            'order_id' => $this->order->id,
            'shipping_method' => 'kurir',
            'tracking_number' => 'JT-EXP-55241890',
            'delivery_address' => 'Jl. Ijen No. 50, Malang',
            'courier_name' => 'J&T Express',
            'status' => 'dikirim',
        ]);

        $this->assertEquals('kurir', $shipmentCourier->shipping_method);
        $this->assertEquals('JT-EXP-55241890', $shipmentCourier->tracking_number);
        $this->assertEquals('J&T Express', $shipmentCourier->courier_name);

        // 2. Pengambilan mandiri (ambil_toko) tanpa nomor resi (nullable)
        $pickupOrder = Order::create([
            'client_id' => $this->client->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-SHIP-PICKUP-002',
            'status' => 'siap_kirim',
            'rental_start_date' => now()->addDays(1),
            'rental_end_date' => now()->addDays(3),
            'duration_days' => 2,
            'total_amount' => 150000,
            'shipping_address' => 'Ambil di Toko',
            'shipping_method' => 'ambil_toko',
        ]);

        $shipmentPickup = Shipment::create([
            'order_id' => $pickupOrder->id,
            'shipping_method' => 'ambil_toko',
            'tracking_number' => null,
            'delivery_address' => 'Store Pickup',
            'courier_name' => null,
            'status' => 'diproses',
        ]);

        $this->assertEquals('ambil_toko', $shipmentPickup->shipping_method);
        $this->assertNull($shipmentPickup->tracking_number);
        $this->assertNull($shipmentPickup->courier_name);
    }

    /**
     * UT-SHIP-003: Memastikan atribut datetime shipped_at dan delivered_at dikonversi ke instance Carbon.
     */
    public function test_ut_ship_003_shipment_timestamps_casting(): void
    {
        $shipment = Shipment::create([
            'order_id' => $this->order->id,
            'shipping_method' => 'kurir',
            'tracking_number' => 'SICEPAT-009182',
            'delivery_address' => 'Jl. Soekarno Hatta No. 9, Malang',
            'courier_name' => 'SiCepat',
            'status' => 'terkirim',
            'shipped_at' => '2026-11-13 08:30:00',
            'delivered_at' => '2026-11-13 14:45:00',
        ]);

        $this->assertInstanceOf(Carbon::class, $shipment->shipped_at);
        $this->assertInstanceOf(Carbon::class, $shipment->delivered_at);
        $this->assertEquals('2026-11-13 08:30:00', $shipment->shipped_at->toDateTimeString());
        $this->assertEquals('2026-11-13 14:45:00', $shipment->delivered_at->toDateTimeString());
    }

    /**
     * UT-SHIP-004: Memastikan atribut status memiliki default value 'diproses' saat dibuat.
     */
    public function test_ut_ship_004_shipment_default_status_is_diproses(): void
    {
        $shipment = Shipment::create([
            'order_id' => $this->order->id,
            'shipping_method' => 'kurir',
            'delivery_address' => 'Jl. Soekarno Hatta No. 9, Malang',
        ]);

        $this->assertEquals('diproses', $shipment->status);
    }

    /**
     * UT-SHIP-005: Memastikan relasi inverse Order hasOne ke model Shipment terhubung dengan presisi.
     */
    public function test_ut_ship_005_order_has_one_shipment_relationship(): void
    {
        $shipment = Shipment::create([
            'order_id' => $this->order->id,
            'shipping_method' => 'kurir',
            'tracking_number' => 'POS-IND-11029',
            'delivery_address' => 'Jl. Bendungan Sigura-gura No. 12, Malang',
            'courier_name' => 'POS Indonesia',
            'status' => 'diproses',
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOne::class, $this->order->shipment());
        $this->assertEquals($shipment->id, $this->order->shipment->id);
    }
}
