<?php

namespace Tests\Feature\Admin;

use App\Events\IdentityApproved;
use App\Events\OrderConfirmed;
use App\Events\PaymentVerified;
use App\Models\Category;
use App\Models\Client;
use App\Models\EquipmentUnit;
use App\Models\IdentityGuarantee;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * MOD-07 & MOD-08 — Master Pesanan & Detail/Verifikasi Order Admin
 *
 * TINGKAT PENGUJIAN: INTEGRATION TESTING (Pengujian Integrasi Komponen)
 * Runner: Laravel HTTP Testing ($this->get(), $this->post(), $this->actingAs())
 *
 * Menguji:
 * 1. Master Pesanan: Akses HTTP 200, filter status, dan isolasi data multi-tenant.
 * 2. Detail Pesanan: Tampilan rincian order dan pencegahan akses silang toko (403 Forbidden).
 * 3. Verifikasi & Konfirmasi Order: Verifikasi KTP (Tahap 1) dan bukti transfer (Tahap 2).
 * 4. Penolakan Order: Perubahan status ke dibatalkan beserta pencatatan alasan.
 * 5. Alokasi Unit Fisik: Penugasan unit fisik individual bebas bentrok jadwal.
 * 6. Refund Manual: Unggah file bukti refund dan pembaruan status refund.
 *
 * Total: 10 Test Case (IT-ORD-001 s/d IT-ORD-010)
 */
class AdminOrderManagementTest extends TestCase
{
    use RefreshDatabase;

    private Client $clientA;
    private Client $clientB;
    private User $adminA;
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

        // Bekukan waktu sistem untuk kestabilan tanggal
        $this->travelTo(Carbon::parse('2026-11-10 10:00:00'));

        $this->clientA = Client::create([
            'business_name' => 'Rental Kamera Studio A',
            'subdomain'     => 'studio-a',
            'status'        => 'aktif',
        ]);

        $this->clientB = Client::create([
            'business_name' => 'Outdoor Camp Rental B',
            'subdomain'     => 'camp-b',
            'status'        => 'aktif',
        ]);

        $this->adminA = User::factory()->create([
            'client_id' => $this->clientA->id,
            'name'      => 'Admin Studio A',
            'role'      => 'admin_rental',
            'status'    => 'aktif',
        ]);

        $this->customer = User::factory()->create([
            'client_id' => $this->clientA->id,
            'name'      => 'Dimas Penyewa',
            'role'      => 'customer',
            'status'    => 'aktif',
        ]);

        $this->category = Category::create([
            'client_id' => $this->clientA->id,
            'name'      => 'Kamera Cinema',
            'slug'      => 'kamera-cinema',
        ]);

        $this->product = Product::create([
            'client_id'            => $this->clientA->id,
            'category_id'          => $this->category->id,
            'name'                 => 'Blackmagic Pocket 4K',
            'slug'                 => 'bmpcc-4k',
            'rental_price_per_day' => 300000,
            'late_fee_per_hour'    => 30000,
            'status'               => 'aktif',
        ]);
    }

    /**
     * Helper membuat Order lengkap dengan 1 OrderItem.
     */
    private function createOrder(array $overrides = [], ?Client $client = null): Order
    {
        $client ??= $this->clientA;
        $this->seq++;

        $order = Order::create(array_merge([
            'client_id'         => $client->id,
            'user_id'           => $this->customer->id,
            'order_number'      => sprintf('ORD-MGR-%03d', $this->seq),
            'rental_start_date' => now()->addDays(1)->setTime(10, 0),
            'rental_end_date'   => now()->addDays(4)->setTime(10, 0),
            'duration_days'     => 3,
            'total_amount'      => 900000,
            'shipping_address'  => 'Jl. Thamrin No. 88, Jakarta Pusat',
            'shipping_method'   => 'kurir',
            'status'            => 'menunggu_konfirmasi',
        ], $overrides));

        OrderItem::create([
            'order_id'      => $order->id,
            'product_id'    => $this->product->id,
            'quantity'      => 1,
            'price_per_day' => 300000,
            'subtotal'      => 900000,
        ]);

        return $order;
    }

    // =====================================================================
    // 10 SKENARIO INTEGRATION TESTING (LARAVEL HTTP)
    // =====================================================================

    /**
     * IT-ORD-001: Admin aktif sukses mengakses master pesanan dengan HTTP 200 dan pagination.
     */
    public function test_it_ord_001_admin_aktif_sukses_akses_master_pesanan(): void
    {
        $order = $this->createOrder();

        $response = $this->actingAs($this->adminA)->get(route('admin.orders.index'));

        $response->assertOk();
        $response->assertViewIs('admin.orders.index');
        $response->assertViewHas('orders');
        $response->assertSee($order->order_number);
    }

    /**
     * IT-ORD-002: Filter status pesanan menyaring data secara akurat di tabel pesanan.
     */
    public function test_it_ord_002_filter_status_pesanan_bekerja_akurat(): void
    {
        $orderKonfirmasi = $this->createOrder(['status' => 'menunggu_konfirmasi', 'order_number' => 'ORD-FLT-KONF']);
        $orderSiapKirim  = $this->createOrder(['status' => 'siap_kirim', 'order_number' => 'ORD-FLT-KIRIM']);

        // Filter: hanya menunggu_konfirmasi
        $response = $this->actingAs($this->adminA)->get(route('admin.orders.index', ['status' => 'menunggu_konfirmasi']));

        $response->assertOk();
        $response->assertSee('ORD-FLT-KONF');
        $response->assertDontSee('ORD-FLT-KIRIM');
    }

    /**
     * IT-ORD-003: Multi-Tenant: Admin Toko A tidak melihat daftar pesanan milik Toko B.
     */
    public function test_it_ord_003_multi_tenant_admin_toko_a_tidak_melihat_pesanan_toko_b(): void
    {
        $orderA = $this->createOrder(['order_number' => 'ORD-TOKO-A']);
        $orderB = $this->createOrder(['order_number' => 'ORD-TOKO-B'], $this->clientB);

        $response = $this->actingAs($this->adminA)->get(route('admin.orders.index'));

        $response->assertOk();
        $response->assertSee('ORD-TOKO-A');
        $response->assertDontSee('ORD-TOKO-B');
    }

    /**
     * IT-ORD-004: Admin dapat membuka detail pesanan dan data relasi customer serta barang tampil lengkap.
     */
    public function test_it_ord_004_admin_dapat_mengakses_detail_pesanan(): void
    {
        $order = $this->createOrder();

        $response = $this->actingAs($this->adminA)->get(route('admin.orders.show', $order));

        $response->assertOk();
        $response->assertViewIs('admin.orders.show');
        $response->assertSee('Dimas Penyewa');
        $response->assertSee('Blackmagic Pocket 4K');
    }

    /**
     * IT-ORD-005: Multi-Tenant: Admin Toko A dilarang mengakses detail pesanan Toko B (403 Forbidden).
     */
    public function test_it_ord_005_admin_toko_a_dilarang_akses_detail_pesanan_toko_b(): void
    {
        $orderB = $this->createOrder([], $this->clientB);

        $response = $this->actingAs($this->adminA)->get(route('admin.orders.show', $orderB));

        $response->assertForbidden();
    }

    /**
     * IT-ORD-006: Konfirmasi Tahap 1: Verifikasi KTP pesanan baru dan alihkan status ke menunggu pembayaran.
     */
    public function test_it_ord_006_konfirmasi_tahap_1_verifikasi_ktp_dan_ubah_ke_menunggu_bayar(): void
    {
        Event::fake([IdentityApproved::class, OrderConfirmed::class]);

        $order = $this->createOrder(['status' => 'menunggu_konfirmasi']);
        $ktp = IdentityGuarantee::create([
            'order_id'                => $order->id,
            'customer_id'             => $order->user_id,
            'full_name'               => 'Dimas Penyewa',
            'identity_number'         => '3171010101010001',
            'identity_document_image' => 'identity/ktp.jpg',
            'face_image'              => 'identity/face.jpg',
            'identity_address'        => 'Jl. Thamrin No. 88',
            'status'                  => 'menunggu',
        ]);

        $response = $this->actingAs($this->adminA)->post(route('admin.orders.confirm', $order));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Status pesanan beralih ke menunggu_pembayaran
        $this->assertDatabaseHas('orders', [
            'id'     => $order->id,
            'status' => 'menunggu_pembayaran',
        ]);

        // Status KTP jaminan terverifikasi
        $this->assertDatabaseHas('identity_guarantees', [
            'id'     => $ktp->id,
            'status' => 'diverifikasi',
        ]);

        Event::assertDispatched(IdentityApproved::class);
    }

    /**
     * IT-ORD-007: Konfirmasi Tahap 2: Verifikasi bukti bayar dan alihkan pesanan ke status siap kirim.
     */
    public function test_it_ord_007_konfirmasi_tahap_2_verifikasi_bayar_dan_ubah_ke_siap_kirim(): void
    {
        Event::fake([PaymentVerified::class, OrderConfirmed::class]);

        $order = $this->createOrder(['status' => 'menunggu_verifikasi']);
        $payment = Payment::create([
            'order_id'       => $order->id,
            'payment_method' => 'transfer_bank',
            'amount'         => 900000,
            'payment_proof'  => 'payments/transfer.jpg',
            'status'         => 'menunggu_konfirmasi',
        ]);

        $response = $this->actingAs($this->adminA)->post(route('admin.orders.confirm', $order));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Status pesanan siap kirim
        $this->assertDatabaseHas('orders', [
            'id'     => $order->id,
            'status' => 'siap_kirim',
        ]);

        // Status pembayaran terverifikasi
        $this->assertDatabaseHas('payments', [
            'id'     => $payment->id,
            'status' => 'terverifikasi',
        ]);

        Event::assertDispatched(PaymentVerified::class);
    }

    /**
     * IT-ORD-008: Penolakan pesanan berhasil mengubah status ke dibatalkan dan mencatat alasan pembatalan.
     */
    public function test_it_ord_008_tolak_pesanan_ubah_status_dibatalkan_dan_simpan_alasan(): void
    {
        $order = $this->createOrder(['status' => 'menunggu_konfirmasi']);

        $response = $this->actingAs($this->adminA)->post(route('admin.orders.reject', $order), [
            'cancellation_reason' => 'Foto KTP tidak jelas / buram',
        ]);

        $response->assertRedirect(route('admin.orders.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'id'                  => $order->id,
            'status'              => 'dibatalkan',
            'cancellation_reason' => 'Foto KTP tidak jelas / buram',
        ]);
    }

    /**
     * IT-ORD-009: Alokasi unit fisik individual berhasil menghubungkan unit ke item pesanan.
     */
    public function test_it_ord_009_alokasi_unit_fisik_sukses_ke_item_pesanan(): void
    {
        $order = $this->createOrder(['status' => 'siap_kirim']);
        $orderItem = $order->orderItems()->first();

        $unit = EquipmentUnit::create([
            'client_id'  => $this->clientA->id,
            'product_id' => $this->product->id,
            'unit_code'  => 'BMPCC-UNIT-01',
            'condition'  => 'baik',
            'status'     => 'tersedia',
        ]);

        $response = $this->actingAs($this->adminA)->post(route('admin.orders.allocate', $order), [
            'order_item_id'     => $orderItem->id,
            'equipment_unit_id' => $unit->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Record order_item_units terbentuk
        $this->assertDatabaseHas('order_item_units', [
            'order_item_id'     => $orderItem->id,
            'equipment_unit_id' => $unit->id,
        ]);
    }

    /**
     * IT-ORD-010: Alur Refund Manual: Upload bukti transfer pengembalian dana berhasil menyelesaikan refund.
     */
    public function test_it_ord_010_alur_refund_manual_unggah_bukti_transfer_selesaikan_refund(): void
    {
        Storage::fake('public');

        $order = $this->createOrder([
            'status'        => 'dibatalkan',
            'refund_status' => 'menunggu_refund',
        ]);

        $fakeFile = UploadedFile::fake()->create('bukti_transfer_refund.jpg', 300, 'image/jpeg');

        $response = $this->actingAs($this->adminA)->post(route('admin.orders.refund', $order), [
            'refund_proof' => $fakeFile,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertSame('refund_selesai', $order->refund_status);
        $this->assertNotNull($order->refund_proof);
    }
}
