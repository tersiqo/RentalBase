<?php

namespace Tests\Feature\Admin;

use App\Events\OrderShipped;
use App\Models\Category;
use App\Models\Client;
use App\Models\EquipmentUnit;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemUnit;
use App\Models\Product;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * MOD-09 — Pengiriman dan Status Pesanan
 *
 * TINGKAT PENGUJIAN: INTEGRATION TESTING (Pengujian Integrasi Komponen)
 * (Di Laravel ditempatkan di folder tests/Feature)
 *
 * File ini menguji keterhubungan antara:
 * Route web.php <-> AdminMiddleware <-> ShipmentController <-> Model Order/Shipment <-> Database SQLite
 *
 * Database: SQLite in-memory (RefreshDatabase) -> AMAN, Supabase tidak tersentuh.
 *
 * Total: 10 Test Case (IT-SHP-001 s/d IT-SHP-010)
 */
class AdminShipmentTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;
    private User $admin;
    private User $customer;
    private Category $category;
    private Product $product;
    private int $seq = 0;

    /**
     * Menyiapkan data awal sebelum setiap test dijalankan.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        // Bekukan waktu di 10 November 2026 pukul 10:00:00 WIB
        $this->travelTo(Carbon::parse('2026-11-10 10:00:00'));

        $this->client = Client::create([
            'business_name' => 'Kamera Rental Nusantara',
            'subdomain'     => 'kamerarental',
            'status'        => 'aktif',
        ]);

        $this->admin = User::factory()->create([
            'client_id' => $this->client->id,
            'name'      => 'Budi Admin',
            'role'      => 'admin_rental',
            'status'    => 'aktif',
        ]);

        $this->customer = User::factory()->create([
            'client_id' => $this->client->id,
            'name'      => 'Ahmad Pelanggan',
            'role'      => 'customer',
            'status'    => 'aktif',
        ]);

        $this->category = Category::create([
            'client_id' => $this->client->id,
            'name'      => 'Kamera Mirrorless',
            'slug'      => 'kamera-mirrorless',
        ]);

        $this->product = Product::create([
            'client_id'            => $this->client->id,
            'category_id'          => $this->category->id,
            'name'                 => 'Sony A7 IV',
            'slug'                 => 'sony-a7-iv',
            'rental_price_per_day' => 250000,
            'late_fee_per_hour'    => 25000,
            'status'               => 'aktif',
        ]);
    }

    /**
     * Helper: Membuat Order dan mengalokasikan unit fisik.
     */
    private function createOrder(array $overrides = [], int $quantity = 1, ?int $allocated = null): Order
    {
        $allocated ??= $quantity;
        $this->seq++;

        $order = Order::create(array_merge([
            'client_id'         => $this->client->id,
            'user_id'           => $this->customer->id,
            'order_number'      => sprintf('ORD-TEST-%03d', $this->seq),
            'rental_start_date' => now()->addDay()->setTime(9, 0), // Besok 09:00 (Jadwal H-1 sudah buka)
            'rental_end_date'   => now()->addDays(3)->setTime(9, 0),
            'duration_days'     => 3,
            'total_amount'      => 750000,
            'shipping_address'  => 'Jl. Sudirman No. 45, Jakarta',
            'shipping_method'   => 'kurir',
            'status'            => 'siap_kirim',
        ], $overrides));

        $item = OrderItem::create([
            'order_id'      => $order->id,
            'product_id'    => $this->product->id,
            'quantity'      => $quantity,
            'price_per_day' => 250000,
            'subtotal'      => 750000 * $quantity,
        ]);

        for ($i = 1; $i <= $allocated; $i++) {
            $unit = EquipmentUnit::create([
                'client_id'  => $this->client->id,
                'product_id' => $this->product->id,
                'unit_code'  => sprintf('SONY-%03d-%02d', $this->seq, $i),
                'condition'  => 'baik',
                'status'     => 'tersedia',
            ]);

            OrderItemUnit::create([
                'order_item_id'     => $item->id,
                'equipment_unit_id' => $unit->id,
            ]);
        }

        return $order;
    }

    // =====================================================================
    // 10 SKENARIO INTEGRATION TESTING
    // =====================================================================

    /**
     * IT-SHP-001: Admin aktif sukses membuka halaman pengiriman (HTTP 200).
     */
    public function test_it_shp_001_admin_aktif_sukses_akses_halaman_pengiriman(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.shipments.index'));

        $response->assertOk();
        $response->assertViewIs('admin.shipments.index');
        $response->assertViewHas('orders');
    }

    /**
     * IT-SHP-002: Pesanan siap_kirim tampil lengkap (nomor order, nama customer, alamat).
     */
    public function test_it_shp_002_pesanan_siap_kirim_tampil_dengan_data_lengkap(): void
    {
        $order = $this->createOrder(['order_number' => 'ORD-KIRIM-888']);

        $response = $this->actingAs($this->admin)->get(route('admin.shipments.index'));

        $response->assertOk();
        $response->assertSee('ORD-KIRIM-888');
        $response->assertSee('Ahmad Pelanggan');
        $response->assertSee('Jl. Sudirman No. 45, Jakarta');
    }

    /**
     * IT-SHP-003: Pesanan dengan status lain (selesai / dibatalkan) tidak ditampilkan.
     */
    public function test_it_shp_003_pesanan_status_lain_tidak_ditampilkan(): void
    {
        $orderSelesai = $this->createOrder(['status' => 'selesai', 'order_number' => 'ORD-SELESAI-999']);
        $orderBatal   = $this->createOrder(['status' => 'dibatalkan', 'order_number' => 'ORD-BATAL-999']);

        $response = $this->actingAs($this->admin)->get(route('admin.shipments.index'));

        $response->assertDontSee('ORD-SELESAI-999');
        $response->assertDontSee('ORD-BATAL-999');
    }

    /**
     * IT-SHP-004: Sukses memproses pengiriman Kurir dengan ekspedisi & nomor resi valid.
     */
    public function test_it_shp_004_proses_kirim_kurir_sukses_simpan_shipment_dan_ubah_status(): void
    {
        Event::fake([OrderShipped::class]);
        $order = $this->createOrder();

        $response = $this->actingAs($this->admin)->post(route('admin.shipments.process', $order), [
            'courier_name'    => 'JNE Express',
            'tracking_number' => 'JNE-9928172635',
        ]);

        $response->assertRedirect(route('admin.shipments.index'));
        $response->assertSessionHas('success');

        // Status pesanan berubah menjadi 'dikirim'
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'dikirim']);

        // Data tersimpan di tabel shipments
        $this->assertDatabaseHas('shipments', [
            'order_id'        => $order->id,
            'shipping_method' => 'kurir',
            'courier_name'    => 'JNE Express',
            'tracking_number' => 'JNE-9928172635',
            'status'          => 'dikirim',
        ]);

        // Unit fisik berubah status menjadi 'disewa'
        $unitId = $order->orderItems->first()->orderItemUnits->first()->equipment_unit_id;
        $this->assertDatabaseHas('equipment_units', ['id' => $unitId, 'status' => 'disewa']);

        // Event pengiriman terpicu
        Event::assertDispatched(OrderShipped::class);
    }

    /**
     * IT-SHP-005: Sukses memproses penyerahan Ambil Sendiri di toko tanpa input resi.
     */
    public function test_it_shp_005_proses_penyerahan_ambil_sendiri_sukses_tanpa_resi(): void
    {
        // Pesanan Ambil Sendiri pada Hari-H (mulai sewa hari ini pukul 18:00)
        $order = $this->createOrder([
            'shipping_method'   => 'ambil_toko',
            'shipping_address'  => 'Pelanggan ambil di toko',
            'rental_start_date' => now()->setTime(18, 0),
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.shipments.process', $order), []);

        $response->assertRedirect(route('admin.shipments.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'sedang_disewa']);
        $this->assertDatabaseHas('shipments', [
            'order_id'        => $order->id,
            'shipping_method' => 'ambil_toko',
            'courier_name'    => 'Ambil Sendiri di Toko',
            'tracking_number' => 'PICKUP-' . $order->order_number,
        ]);
    }

    /**
     * IT-SHP-006: Validasi menolak pengiriman Kurir jika nama ekspedisi atau nomor resi kosong.
     */
    public function test_it_shp_006_validasi_menolak_kurir_jika_data_tidak_lengkap(): void
    {
        $order = $this->createOrder();

        $response = $this->actingAs($this->admin)
            ->from(route('admin.shipments.index'))
            ->post(route('admin.shipments.process', $order), [
                'courier_name'    => '',
                'tracking_number' => '',
            ]);

        $response->assertRedirect(route('admin.shipments.index'));
        $response->assertSessionHasErrors(['courier_name', 'tracking_number']);

        // Data tidak boleh berubah
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'siap_kirim']);
        $this->assertDatabaseCount('shipments', 0);
    }

    /**
     * IT-SHP-007: Sistem menolak pengiriman jika unit fisik barang belum dialokasikan lengkap.
     */
    public function test_it_shp_007_sistem_menolak_pengiriman_jika_unit_belum_lengkap(): void
    {
        // Pesanan butuh 2 unit barang, tapi admin baru mengalokasikan 1 unit
        $order = $this->createOrder([], 2, 1);

        $response = $this->actingAs($this->admin)->post(route('admin.shipments.process', $order), [
            'courier_name'    => 'JNE Express',
            'tracking_number' => 'JNE-123456',
        ]);

        // Dialihkan ke halaman alokasi unit dengan pesan error
        $response->assertRedirect(route('admin.orders.show', $order->id));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'siap_kirim']);
        $this->assertDatabaseCount('shipments', 0);
    }

    /**
     * IT-SHP-008: Sistem menolak pengiriman jika mendahului jadwal resmi (Kurir pada H-2).
     */
    public function test_it_shp_008_sistem_menolak_pengiriman_sebelum_jadwal_resmi(): void
    {
        // Mulai sewa masih 2 hari lagi (H-2): belum boleh dikirim via kurir
        $order = $this->createOrder([
            'rental_start_date' => now()->addDays(2)->setTime(9, 0),
        ]);

        $response = $this->actingAs($this->admin)
            ->from(route('admin.shipments.index'))
            ->post(route('admin.shipments.process', $order), [
                'courier_name'    => 'JNE Express',
                'tracking_number' => 'JNE-123456',
            ]);

        $response->assertRedirect(route('admin.shipments.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'siap_kirim']);
        $this->assertDatabaseCount('shipments', 0);
    }

    /**
     * IT-SHP-009: Pengguna tamu atau customer dilarang mengakses fungsi admin pengiriman.
     */
    public function test_it_shp_009_customer_dan_tamu_dilarang_mengakses_fitur_admin(): void
    {
        $order = $this->createOrder();

        // 1. Tamu (belum login) mengakses URL -> redirect ke landing page / login
        $this->get(route('admin.shipments.index'))->assertRedirect('/');

        // 2. Customer mencoba memproses pengiriman -> ditolak
        $this->actingAs($this->customer)
            ->post(route('admin.shipments.process', $order), [
                'courier_name'    => 'JNE',
                'tracking_number' => '123',
            ])
            ->assertRedirect('/');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'siap_kirim']);
        $this->assertDatabaseCount('shipments', 0);
    }

    /**
     * IT-SHP-010: Temuan Bug Bisnis: Pesanan belum dibayar seharusnya tidak bisa dikirim.
     *
     * PERHATIAN: Test ini dirancang untuk membuktikan celah/defect pada controller.
     * Karena ShipmentController::process() tidak memvalidasi status pesanan,
     * test ini kemungkinan FAILED. Hasil Failed adalah bukti autentik adanya bug bisnis.
     */
    public function test_it_shp_010_pesanan_belum_dibayar_seharusnya_tidak_bisa_dikirim(): void
    {
        $order = $this->createOrder(['status' => 'menunggu_pembayaran']);

        $this->actingAs($this->admin)->post(route('admin.shipments.process', $order), [
            'courier_name'    => 'JNE Express',
            'tracking_number' => 'JNE-123456',
        ]);

        // Status pesanan seharusnya tetap 'menunggu_pembayaran' dan pengiriman tidak dibuat
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'menunggu_pembayaran']);
        $this->assertDatabaseCount('shipments', 0);
    }
}
