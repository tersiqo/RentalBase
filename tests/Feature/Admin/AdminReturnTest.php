<?php

namespace Tests\Feature\Admin;

use App\Events\FineIssued;
use App\Events\ReturnAccepted;
use App\Models\Category;
use App\Models\Client;
use App\Models\EquipmentUnit;
use App\Models\IdentityGuarantee;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemUnit;
use App\Models\Product;
use App\Models\ReturnModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * MOD-10 — Pengembalian, Kondisi dan Damage Case
 * Fitur: Halaman Retur Admin (/admin/returns)
 *
 * TINGKAT PENGUJIAN: INTEGRATION TESTING (Pengujian Integrasi Komponen)
 *
 * File ini menguji keterhubungan antara:
 * Route web.php <-> AdminMiddleware <-> ReturnController <-> ReturnProcessRequest <-> Database SQLite
 *
 * Database: SQLite in-memory (RefreshDatabase) -> AMAN, Supabase tidak tersentuh.
 *
 * Total: 10 Test Case (IT-RET-001 s/d IT-RET-010)
 */
class AdminReturnTest extends TestCase
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
     * setUp() dijalankan otomatis sebelum setiap test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        // Bekukan waktu di 10 November 2026 pukul 10:00:00 WIB
        $this->travelTo(Carbon::parse('2026-11-10 10:00:00'));

        $this->clientA = Client::create([
            'business_name' => 'Toko Rental Kamera A',
            'subdomain'     => 'toko-kamera-a',
            'status'        => 'aktif',
        ]);

        $this->clientB = Client::create([
            'business_name' => 'Toko Rental Outdoor B',
            'subdomain'     => 'toko-outdoor-b',
            'status'        => 'aktif',
        ]);

        $this->adminA = User::factory()->create([
            'client_id' => $this->clientA->id,
            'name'      => 'Admin Toko A',
            'role'      => 'admin_rental',
            'status'    => 'aktif',
        ]);

        $this->customer = User::factory()->create([
            'client_id' => $this->clientA->id,
            'name'      => 'Rian Pelanggan',
            'role'      => 'customer',
            'status'    => 'aktif',
        ]);

        $this->category = Category::create([
            'client_id' => $this->clientA->id,
            'name'      => 'Kamera DSLR',
            'slug'      => 'kamera-dslr',
        ]);

        $this->product = Product::create([
            'client_id'            => $this->clientA->id,
            'category_id'          => $this->category->id,
            'name'                 => 'Canon EOS R6',
            'slug'                 => 'canon-eos-r6',
            'rental_price_per_day' => 200000,
            'late_fee_per_hour'    => 20000,
            'status'               => 'aktif',
        ]);
    }

    /**
     * Helper: Membuat Order sewa lengkap dengan unit fisik.
     */
    private function createRentalOrder(array $overrides = [], Client $client = null): Order
    {
        $client ??= $this->clientA;
        $this->seq++;

        $order = Order::create(array_merge([
            'client_id'         => $client->id,
            'user_id'           => $this->customer->id,
            'order_number'      => sprintf('ORD-RET-%03d', $this->seq),
            'rental_start_date' => now()->subDays(3)->setTime(10, 0),
            'rental_end_date'   => now()->subHours(2), // Lewat 2 jam dari jadwal jatuh tempo
            'duration_days'     => 3,
            'total_amount'      => 600000,
            'shipping_address'  => 'Jl. Pahlawan No. 12, Surabaya',
            'shipping_method'   => 'ambil_toko',
            'status'            => 'sedang_disewa',
        ], $overrides));

        $item = OrderItem::create([
            'order_id'      => $order->id,
            'product_id'    => $this->product->id,
            'quantity'      => 1,
            'price_per_day' => 200000,
            'subtotal'      => 600000,
        ]);

        $unit = EquipmentUnit::create([
            'client_id'  => $client->id,
            'product_id' => $this->product->id,
            'unit_code'  => sprintf('CANON-%03d', $this->seq),
            'condition'  => 'baik',
            'status'     => 'disewa',
        ]);

        OrderItemUnit::create([
            'order_item_id'     => $item->id,
            'equipment_unit_id' => $unit->id,
        ]);

        return $order;
    }

    /**
     * Helper: Menambahkan jaminan identitas KTP berstatus 'dititipkan'.
     */
    private function attachKtpGuarantee(Order $order): IdentityGuarantee
    {
        return IdentityGuarantee::create([
            'order_id'                => $order->id,
            'customer_id'             => $order->user_id,
            'full_name'               => 'Rian Pelanggan',
            'identity_number'         => '3578010101010001',
            'identity_document_image' => 'identity/ktp.jpg',
            'face_image'              => 'identity/face.jpg',
            'identity_address'        => 'Jl. Pahlawan No. 12',
            'status'                  => 'dititipkan',
            'deposited_at'            => now()->subDays(3),
        ]);
    }

    // =====================================================================
    // 10 SKENARIO INTEGRATION TESTING
    // =====================================================================

    /**
     * IT-RET-001: Admin aktif sukses mengakses halaman retur dengan respon HTTP 200.
     */
    public function test_it_ret_001_admin_aktif_sukses_akses_halaman_retur(): void
    {
        $response = $this->actingAs($this->adminA)->get(route('admin.returns.index'));

        $response->assertOk();
        $response->assertViewIs('admin.returns.index');
        $response->assertViewHas('orders');
    }

    /**
     * IT-RET-002: Pesanan berstatus 'sedang_disewa' dan 'dikembalikan' tampil pada tabel retur.
     */
    public function test_it_ret_002_pesanan_sedang_disewa_dan_dikembalikan_tampil_di_tabel(): void
    {
        $orderDisewa = $this->createRentalOrder(['status' => 'sedang_disewa', 'order_number' => 'ORD-SEWA-777']);
        $orderRetur  = $this->createRentalOrder(['status' => 'dikembalikan', 'order_number' => 'ORD-AJU-777']);

        $response = $this->actingAs($this->adminA)->get(route('admin.returns.index'));

        $response->assertOk();
        $response->assertSee('ORD-SEWA-777');
        $response->assertSee('ORD-AJU-777');
    }

    /**
     * IT-RET-003: Pesanan dengan status selain retur (selesai / siap_kirim) tidak ditampilkan.
     */
    public function test_it_ret_003_pesanan_selesai_dan_siap_kirim_tidak_tampil(): void
    {
        $orderSelesai = $this->createRentalOrder(['status' => 'selesai', 'order_number' => 'ORD-SELESAI-888']);
        $orderSiap    = $this->createRentalOrder(['status' => 'siap_kirim', 'order_number' => 'ORD-SIAP-888']);

        $response = $this->actingAs($this->adminA)->get(route('admin.returns.index'));

        $response->assertDontSee('ORD-SELESAI-888');
        $response->assertDontSee('ORD-SIAP-888');
    }

    /**
     * IT-RET-004: Proses retur normal berhasil: order selesai, unit kembali tersedia, KTP dikembalikan.
     */
    public function test_it_ret_004_proses_retur_normal_selesaikan_order_dan_kembalikan_unit_ktp(): void
    {
        Event::fake([ReturnAccepted::class]);
        $order = $this->createRentalOrder();
        $ktp = $this->attachKtpGuarantee($order);

        $response = $this->actingAs($this->adminA)->post(route('admin.returns.process', $order), [
            'action_type'     => 'normal',
            'late_fee_amount' => 0,
        ]);

        $response->assertRedirect(route('admin.returns.index'));
        $response->assertSessionHas('success');

        // Status pesanan menjadi selesai
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'selesai']);

        // Record returns berstatus selesai
        $this->assertDatabaseHas('returns', [
            'order_id'      => $order->id,
            'return_status' => 'selesai',
        ]);

        // Unit fisik kembali tersedia
        $unitId = $order->orderItems->first()->orderItemUnits->first()->equipment_unit_id;
        $this->assertDatabaseHas('equipment_units', ['id' => $unitId, 'status' => 'tersedia']);

        // Jaminan KTP dikembalikan dan mencatat returned_at
        $this->assertDatabaseHas('identity_guarantees', [
            'id'     => $ktp->id,
            'status' => 'dikembalikan',
        ]);
        $this->assertNotNull($ktp->fresh()->returned_at);

        // Event ReturnAccepted terpicu
        Event::assertDispatched(ReturnAccepted::class);
    }

    /**
     * IT-RET-005: Proses penerbitan denda berhasil: order menunggu denda, KTP tetap dititipkan.
     */
    public function test_it_ret_005_proses_terbitkan_denda_ubah_status_dan_tahan_ktp(): void
    {
        Event::fake([FineIssued::class]);
        $order = $this->createRentalOrder();
        $ktp = $this->attachKtpGuarantee($order);

        $response = $this->actingAs($this->adminA)->post(route('admin.returns.process', $order), [
            'action_type'     => 'denda',
            'late_fee_amount' => 40000,
        ]);

        $response->assertRedirect(route('admin.returns.index'));
        $response->assertSessionHas('success');

        // Order berubah menjadi menunggu_pembayaran_denda
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'menunggu_pembayaran_denda']);

        // Record returns mencatat denda
        $this->assertDatabaseHas('returns', [
            'order_id'        => $order->id,
            'late_fee_amount' => 40000,
            'late_fee_status' => 'menunggu_pembayaran',
            'return_status'   => 'diterima',
        ]);

        // KTP fisik tetap berstatus dititipkan (ditahan sampai lunas)
        $this->assertDatabaseHas('identity_guarantees', [
            'id'     => $ktp->id,
            'status' => 'dititipkan',
        ]);

        // Event FineIssued terpicu
        Event::assertDispatched(FineIssued::class);
    }

    /**
     * IT-RET-006: Admin dapat melakukan override nominal denda (mengubah nilai denda otomatis).
     */
    public function test_it_ret_006_admin_dapat_override_nominal_denda(): void
    {
        $order = $this->createRentalOrder();

        // Denda otomatis sistem untuk 2 jam terlambat = 2 x 20.000 = 40.000
        // Admin melakukan override menjadi 25.000
        $this->actingAs($this->adminA)->post(route('admin.returns.process', $order), [
            'action_type'     => 'denda',
            'late_fee_amount' => 25000,
        ]);

        $this->assertDatabaseHas('returns', [
            'order_id'            => $order->id,
            'late_hours'          => 2,
            'calculated_late_fee' => 40000,
            'late_fee_amount'     => 25000, // Hasil override admin
        ]);
    }

    /**
     * IT-RET-007: Validasi menolak proses jika nominal denda bernilai negatif (min:0).
     */
    public function test_it_ret_007_validasi_menolak_nominal_denda_negatif(): void
    {
        $order = $this->createRentalOrder();

        $response = $this->actingAs($this->adminA)
            ->from(route('admin.returns.index'))
            ->post(route('admin.returns.process', $order), [
                'action_type'     => 'denda',
                'late_fee_amount' => -15000,
            ]);

        $response->assertRedirect(route('admin.returns.index'));
        $response->assertSessionHasErrors('late_fee_amount');
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'sedang_disewa']);
    }

    /**
     * IT-RET-008: Validasi menolak jika action_type tidak sesuai pilihan yang diizinkan.
     */
    public function test_it_ret_008_validasi_menolak_action_type_tidak_valid(): void
    {
        $order = $this->createRentalOrder();

        $response = $this->actingAs($this->adminA)
            ->from(route('admin.returns.index'))
            ->post(route('admin.returns.process', $order), [
                'action_type'     => 'tipe_ngawur',
                'late_fee_amount' => 0,
            ]);

        $response->assertRedirect(route('admin.returns.index'));
        $response->assertSessionHasErrors('action_type');
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'sedang_disewa']);
    }

    /**
     * IT-RET-009: Multi-Tenant: Admin Toko A dilarang memproses retur pesanan milik Toko B.
     */
    public function test_it_ret_009_admin_toko_lain_dilarang_proses_retur(): void
    {
        $orderTokoB = $this->createRentalOrder([], $this->clientB);

        $response = $this->actingAs($this->adminA)->post(route('admin.returns.process', $orderTokoB), [
            'action_type'     => 'normal',
            'late_fee_amount' => 0,
        ]);

        $response->assertForbidden(); // 403 Forbidden
        $this->assertDatabaseHas('orders', ['id' => $orderTokoB->id, 'status' => 'sedang_disewa']);
    }

    /**
     * IT-RET-010: Pencegahan duplikasi data: proses ulang pesanan yang sama mengupdate data retur yang ada.
     */
    public function test_it_ret_010_first_or_new_mencegah_duplikasi_record_retur(): void
    {
        $order = $this->createRentalOrder();

        // Proses pertama: denda 30.000
        $this->actingAs($this->adminA)->post(route('admin.returns.process', $order), [
            'action_type'     => 'denda',
            'late_fee_amount' => 30000,
        ]);

        // Proses kedua: update denda jadi 45.000
        $this->actingAs($this->adminA)->post(route('admin.returns.process', $order), [
            'action_type'     => 'denda',
            'late_fee_amount' => 45000,
        ]);

        // Jumlah baris di tabel returns untuk order ini harus tepat 1
        $this->assertSame(1, ReturnModel::where('order_id', $order->id)->count());
        $this->assertDatabaseHas('returns', [
            'order_id'        => $order->id,
            'late_fee_amount' => 45000,
        ]);
    }
}
