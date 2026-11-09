<?php

namespace Tests\Feature\Customer;

use App\Models\Category;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ReturnModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * MOD-10 — Pengembalian, Kondisi dan Damage Case
 * Fitur: Form Pengembalian Barang & Denda Sewa Customer
 *
 * TINGKAT PENGUJIAN: INTEGRATION TESTING (Pengujian Integrasi Komponen)
 * Runner: Laravel HTTP Testing ($this->get(), $this->post(), $this->actingAs())
 *
 * Menguji:
 * 1. Halaman Form Retur: Customer pemilik pesanan sukses mengakses halaman form (HTTP 200).
 * 2. Otorisasi Ketat: Customer lain dilarang mengakses form pengembalian orang lain (HTTP 403).
 * 3. Alur Retur Kurir: Berhasil input kurir & resi valid, status order berubah ke 'dikembalikan', notifikasi admin terkirim.
 * 4. Alur Retur Antar Toko: Otomatis menullkan nama kurir & nomor resi jika memilih opsi antar langsung ke toko.
 * 5. Validasi Denda Keterlambatan: Mewajibkan unggah bukti pembayaran denda jika pesanan terlambat.
 * 6. Validasi Input Kurir: Menolak nomor resi yang hanya berisi spasi kosong atau nama kurir tidak terdaftar.
 *
 * Total: 6 Test Case Ringkas (IT-RET-011 s/d IT-RET-016)
 */
class CustomerReturnFormTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;
    private User $admin;
    private User $customer;
    private User $otherCustomer;
    private Product $product;
    private Order $order;

    /**
     * Menyiapkan data awal sebelum setiap test dijalankan.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        // Bekukan waktu sistem untuk pengujian kalkulasi sewa & keterlambatan yang deterministik
        $this->travelTo(Carbon::parse('2026-11-10 10:00:00'));

        $this->client = Client::create([
            'business_name' => 'Rental Outdoor Adventure',
            'subdomain'     => 'outdoor-adv',
            'status'        => 'aktif',
        ]);

        $this->admin = User::factory()->create([
            'client_id' => $this->client->id,
            'name'      => 'Admin Toko Rental',
            'role'      => 'admin_rental',
            'status'    => 'aktif',
        ]);

        $this->customer = User::factory()->create([
            'client_id' => $this->client->id,
            'name'      => 'Customer Pemilik Order',
            'role'      => 'customer',
            'status'    => 'aktif',
        ]);

        $this->otherCustomer = User::factory()->create([
            'client_id' => $this->client->id,
            'name'      => 'Customer Lain',
            'role'      => 'customer',
            'status'    => 'aktif',
        ]);

        $category = Category::create([
            'client_id' => $this->client->id,
            'name'      => 'Tenda Camping',
            'slug'      => 'tenda-camping',
        ]);

        $this->product = Product::create([
            'client_id'            => $this->client->id,
            'category_id'          => $category->id,
            'name'                 => 'Tenda Dome Kapasitas 4 Orang',
            'slug'                 => 'tenda-dome-4p',
            'rental_price_per_day' => 100000,
            'late_fee_per_hour'    => 10000,
            'status'               => 'aktif',
        ]);

        // Pesanan aktif berstatus sedang_disewa (tepat waktu, jatuh tempo besok)
        $this->order = Order::create([
            'client_id'         => $this->client->id,
            'user_id'           => $this->customer->id,
            'order_number'      => 'ORD-RET-001',
            'rental_start_date' => now()->subDay(),
            'rental_end_date'   => now()->addDay(), // Besok (belum terlambat)
            'duration_days'     => 2,
            'total_amount'      => 200000,
            'shipping_address'  => 'Jl. Rinjani No. 12, Malang',
            'shipping_method'   => 'kurir',
            'status'            => 'sedang_disewa',
        ]);

        OrderItem::create([
            'order_id'      => $this->order->id,
            'product_id'    => $this->product->id,
            'quantity'      => 1,
            'price_per_day' => 100000,
            'subtotal'      => 200000,
        ]);
    }

    // =====================================================================
    // 6 SKENARIO INTEGRATION TESTING (LARAVEL HTTP)
    // =====================================================================

    /**
     * IT-RET-011: Customer pemilik pesanan sukses mengakses halaman form pengembalian (HTTP 200).
     */
    public function test_it_ret_011_customer_pemilik_sukses_akses_halaman_form_retur(): void
    {
        $response = $this->actingAs($this->customer)
            ->get(route('customer.orders.return.form', $this->order->id));

        $response->assertStatus(200);
        $response->assertViewIs('customer.orders.return');
        $response->assertSee('Form Pengembalian Barang');
        $response->assertSee($this->order->order_number);
        $response->assertSee($this->product->name);
        $response->assertViewHas('lateFeeData');

        $lateFeeData = $response->viewData('lateFeeData');
        $this->assertFalse($lateFeeData['is_late'], 'Pesanan belum jatuh tempo sehingga is_late harus false.');
        $this->assertEquals(0, $lateFeeData['total_late_fee'], 'Nominal denda pesanan tepat waktu harus Rp 0.');
    }

    /**
     * IT-RET-012: Isolasi Otorisasi: Customer lain dilarang mengakses atau mengirim form retur milik orang lain (HTTP 403).
     */
    public function test_it_ret_012_customer_lain_dilarang_akses_form_retur_orang_lain(): void
    {
        // 1. Percobaan GET halaman form retur milik customer lain
        $responseGet = $this->actingAs($this->otherCustomer)
            ->get(route('customer.orders.return.form', $this->order->id));

        $responseGet->assertStatus(403);

        // 2. Percobaan POST pengajuan retur ke order milik customer lain
        $responsePost = $this->actingAs($this->otherCustomer)
            ->post(route('customer.orders.return', $this->order->id), [
                'return_method' => 'ambil_toko',
            ]);

        $responsePost->assertStatus(403);
    }

    /**
     * IT-RET-013: Customer sukses mengajukan pengembalian via kurir tepat waktu (status berpindah ke dikembalikan).
     */
    public function test_it_ret_013_customer_sukses_ajukan_retur_via_kurir_tepat_waktu(): void
    {
        $payload = [
            'return_method'   => 'kurir',
            'courier_name'    => 'JNE',
            'tracking_number' => 'JNE1234567890',
            'notes'           => 'Tenda dan pasak lengkap di dalam tas carrier asli.',
        ];

        $response = $this->actingAs($this->customer)
            ->post(route('customer.orders.return', $this->order->id), $payload);

        $response->assertRedirect(route('customer.orders.show', $this->order->id));
        $response->assertSessionHas('success');

        // 1. Verifikasi status order berubah menjadi 'dikembalikan'
        $this->assertDatabaseHas('orders', [
            'id'     => $this->order->id,
            'status' => 'dikembalikan',
        ]);

        // 2. Verifikasi record pada tabel returns tersimpan dengan status 'diajukan' dan tanpa denda
        $this->assertDatabaseHas('returns', [
            'order_id'            => $this->order->id,
            'return_method'       => 'kurir',
            'courier_name'        => 'JNE',
            'tracking_number'     => 'JNE1234567890',
            'return_status'       => 'diajukan',
            'late_hours'          => 0,
            'calculated_late_fee' => 0,
            'late_fee_status'     => 'tidak_ada',
            'notes'               => 'Tenda dan pasak lengkap di dalam tas carrier asli.',
        ]);

        // 3. Verifikasi notifikasi terkirim ke admin toko
        $this->assertDatabaseHas('notifications', [
            'client_id' => $this->client->id,
            'user_id'   => $this->admin->id,
            'type'      => 'return',
        ]);

        // 4. Verifikasi pencatatan audit log
        $this->assertDatabaseHas('activity_logs', [
            'client_id' => $this->client->id,
            'user_id'   => $this->customer->id,
            'action'    => 'return_requested',
            'module'    => 'returns',
        ]);
    }

    /**
     * IT-RET-014: Customer sukses mengajukan retur via antar langsung ke toko (kurir dan resi dinullkan).
     */
    public function test_it_ret_014_customer_sukses_ajukan_retur_antar_langsung_ke_toko(): void
    {
        // Buat pesanan kedua berstatus 'siap_kembali'
        $orderToko = Order::create([
            'client_id'         => $this->client->id,
            'user_id'           => $this->customer->id,
            'order_number'      => 'ORD-RET-002',
            'rental_start_date' => now()->subDays(2),
            'rental_end_date'   => now()->addDay(),
            'duration_days'     => 3,
            'total_amount'      => 300000,
            'shipping_address'  => 'Ambil di Toko',
            'shipping_method'   => 'ambil_toko',
            'status'            => 'siap_kembali',
        ]);

        OrderItem::create([
            'order_id'      => $orderToko->id,
            'product_id'    => $this->product->id,
            'quantity'      => 1,
            'price_per_day' => 100000,
            'subtotal'      => 300000,
        ]);

        // Kirim payload dengan isian kurir tak sengaja terisi
        $payload = [
            'return_method'   => 'ambil_toko',
            'courier_name'    => 'J&T',
            'tracking_number' => 'RESI-TEST-999',
            'notes'           => 'Barang akan diantar langsung oleh customer pukul 14:00.',
        ];

        $response = $this->actingAs($this->customer)
            ->post(route('customer.orders.return', $orderToko->id), $payload);

        $response->assertRedirect(route('customer.orders.show', $orderToko->id));
        $response->assertSessionHas('success');

        // Pastikan nama kurir dan resi di-nullkan oleh backend
        $this->assertDatabaseHas('returns', [
            'order_id'        => $orderToko->id,
            'return_method'   => 'ambil_toko',
            'courier_name'    => null,
            'tracking_number' => null,
            'return_status'   => 'diajukan',
        ]);
    }

    /**
     * IT-RET-015: Validasi menolak pengajuan retur terlambat jika tidak menyertakan bukti pembayaran denda.
     */
    public function test_it_ret_015_validasi_menolak_retur_terlambat_tanpa_bukti_transfer_denda(): void
    {
        $disk = config('filesystems.default', 'public');
        Storage::fake($disk);

        // Buat order yang sudah melewati batas akhir sewa (terlambat 4 jam)
        $lateOrder = Order::create([
            'client_id'         => $this->client->id,
            'user_id'           => $this->customer->id,
            'order_number'      => 'ORD-RET-LATE',
            'rental_start_date' => now()->subDays(3),
            'rental_end_date'   => now()->subHours(4), // Melewati batas 4 jam
            'duration_days'     => 2,
            'total_amount'      => 200000,
            'shipping_address'  => 'Jl. Dieng No. 8',
            'shipping_method'   => 'ambil_toko',
            'status'            => 'sedang_disewa',
        ]);

        OrderItem::create([
            'order_id'      => $lateOrder->id,
            'product_id'    => $this->product->id, // tarif denda 10.000/jam
            'quantity'      => 1,
            'price_per_day' => 100000,
            'subtotal'      => 200000,
        ]);

        // Kasus 1: Mengajukan tanpa melampirkan file late_fee_proof -> Harus gagal validasi
        $responseFail = $this->actingAs($this->customer)
            ->post(route('customer.orders.return', $lateOrder->id), [
                'return_method' => 'ambil_toko',
            ]);

        $responseFail->assertSessionHasErrors(['late_fee_proof']);
        $this->assertDatabaseMissing('returns', ['order_id' => $lateOrder->id]);

        // Kasus 2: Mengajukan dengan melampirkan file foto bukti transfer denda valid
        $proofFile = UploadedFile::fake()->image('bukti_transfer_denda.jpg', 600, 600);

        $responseSuccess = $this->actingAs($this->customer)
            ->post(route('customer.orders.return', $lateOrder->id), [
                'return_method'  => 'ambil_toko',
                'late_fee_proof' => $proofFile,
                'notes'          => 'Denda terlambat 4 jam sudah ditransfer lunas via BCA.',
            ]);

        $responseSuccess->assertRedirect(route('customer.orders.show', $lateOrder->id));
        $responseSuccess->assertSessionHas('success');

        $returnRecord = ReturnModel::where('order_id', $lateOrder->id)->first();
        $this->assertNotNull($returnRecord);
        $this->assertSame(4, (int) $returnRecord->late_hours);
        $this->assertEquals(40000, (float) $returnRecord->calculated_late_fee); // 4 jam * 10.000
        $this->assertSame('menunggu_pembayaran', $returnRecord->late_fee_status);
        $this->assertNotNull($returnRecord->late_fee_proof);

        Storage::disk($disk)->assertExists($returnRecord->late_fee_proof);
    }

    /**
     * IT-RET-016: Validasi menolak nomor resi kurir yang hanya berisi spasi kosong atau kurir tidak valid.
     */
    public function test_it_ret_016_validasi_menolak_nomor_resi_hanya_spasi_kosong_pada_metode_kurir(): void
    {
        // 1. Resi hanya spasi kosong (whitespace-only)
        $responseSpace = $this->actingAs($this->customer)
            ->post(route('customer.orders.return', $this->order->id), [
                'return_method'   => 'kurir',
                'courier_name'    => 'SiCepat',
                'tracking_number' => '     ',
            ]);

        $responseSpace->assertSessionHasErrors(['tracking_number']);

        // 2. Kurir di luar whitelist yang diizinkan (JNE, J&T, SiCepat, Gojek, Grab)
        $responseCourier = $this->actingAs($this->customer)
            ->post(route('customer.orders.return', $this->order->id), [
                'return_method'   => 'kurir',
                'courier_name'    => 'EkspedisiTidakDikenal',
                'tracking_number' => 'RESI12345',
            ]);

        $responseCourier->assertSessionHasErrors(['courier_name']);
    }
}
