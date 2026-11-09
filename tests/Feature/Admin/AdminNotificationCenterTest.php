<?php

namespace Tests\Feature\Admin;

use App\Models\Client;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * MOD-04 — Modul Pengelolaan Toko, Metode Pembayaran dan Dashboard (Admin Rental)
 * Fitur: Notifikasi Admin / Pusat Pemberitahuan Toko (ID: 8.2-A2)
 *
 * TINGKAT PENGUJIAN: INTEGRATION TESTING (Pengujian Integrasi Komponen)
 * Runner: Laravel HTTP Testing ($this->get(), $this->post(), $this->actingAs())
 *
 * Menguji:
 * 1. Halaman Index: Admin rental sukses mengakses pusat notifikasi operasional toko (HTTP 200).
 * 2. Otorisasi Keamanan: Tamu (guest) dan customer dilarang mengakses notifikasi admin.
 * 3. Isolasi Multi-Tenant: Admin Toko A dilarang melihat alert operasional toko B.
 * 4. Filter Kategori: Tab filter (orders, returns, unread) menyaring data notifikasi dengan akurat.
 * 5. Tandai Dibaca Satuan: Sukses menandai satu notifikasi dan menolak manipulasi notifikasi toko lain (HTTP 403).
 * 6. Tandai Semua Dibaca: Batch update mengubah semua notifikasi toko aktif menjadi dibaca tanpa menyentuh toko lain.
 *
 * Total: 6 Test Case Ringkas (IT-04-004 s/d IT-04-009)
 */
class AdminNotificationCenterTest extends TestCase
{
    use RefreshDatabase;

    private Client $storeA;
    private Client $storeB;
    private User $adminA;
    private User $adminB;
    private User $customer;

    /**
     * Menyiapkan data awal sebelum setiap test dijalankan.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->storeA = Client::create([
            'business_name' => 'Rental Outdoor Adventure A',
            'subdomain'     => 'outdoor-a',
            'status'        => 'aktif',
        ]);

        $this->storeB = Client::create([
            'business_name' => 'Rental Studio Kamera B',
            'subdomain'     => 'kamera-b',
            'status'        => 'aktif',
        ]);

        $this->adminA = User::factory()->create([
            'client_id' => $this->storeA->id,
            'name'      => 'Admin Toko A',
            'role'      => 'admin_rental',
            'status'    => 'aktif',
        ]);

        $this->adminB = User::factory()->create([
            'client_id' => $this->storeB->id,
            'name'      => 'Admin Toko B',
            'role'      => 'admin_rental',
            'status'    => 'aktif',
        ]);

        $this->customer = User::factory()->create([
            'client_id' => $this->storeA->id,
            'name'      => 'Customer Biasa',
            'role'      => 'customer',
            'status'    => 'aktif',
        ]);
    }

    // =====================================================================
    // 6 SKENARIO INTEGRATION TESTING (LARAVEL HTTP)
    // =====================================================================

    /**
     * IT-04-004: Admin rental aktif sukses mengakses halaman pusat pemberitahuan toko (HTTP 200).
     */
    public function test_it_04_004_admin_aktif_sukses_akses_halaman_notifikasi_toko(): void
    {
        Notification::create([
            'client_id' => $this->storeA->id,
            'user_id'   => $this->adminA->id,
            'title'     => 'Pesanan Baru #ORD-101 Masuk',
            'message'   => 'Customer telah membuat booking baru menunggu konfirmasi.',
            'type'      => 'order',
            'is_read'   => false,
        ]);

        $response = $this->actingAs($this->adminA)->get(route('admin.notifications.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.notifications.index');
        $response->assertSee('Pusat Pemberitahuan Toko');
        $response->assertSee('Pesanan Baru #ORD-101 Masuk');
        $response->assertViewHas('countUnread', 1);
    }

    /**
     * IT-04-005: Keamanan Otorisasi: Tamu dan customer dilarang mengakses pusat notifikasi admin (Dicegat Middleware).
     */
    public function test_it_04_005_keamanan_otorisasi_tamu_dan_customer_dilarang_akses_notifikasi_admin(): void
    {
        // 1. Pengguna tamu (guest) mencoba akses rute notifikasi admin
        $responseGuest = $this->get('/admin/notifications');
        $responseGuest->assertRedirect('/');

        // 2. User role customer mencoba akses rute notifikasi admin
        $responseCustomer = $this->actingAs($this->customer)->get('/admin/notifications');
        $responseCustomer->assertRedirect('/');
    }

    /**
     * IT-04-006: Isolasi Multi-Tenant: Admin Toko A tidak melihat alert notifikasi milik Toko B.
     */
    public function test_it_04_006_isolasi_multi_tenant_admin_toko_a_tidak_melihat_alert_toko_b(): void
    {
        Notification::create([
            'client_id' => $this->storeA->id,
            'user_id'   => $this->adminA->id,
            'title'     => 'Alert Eksklusif Toko A',
            'message'   => 'Pesan operasional rahasia Toko A.',
            'type'      => 'order',
            'is_read'   => false,
        ]);

        Notification::create([
            'client_id' => $this->storeB->id,
            'user_id'   => $this->adminB->id,
            'title'     => 'Alert Eksklusif Toko B',
            'message'   => 'Pesan operasional rahasia Toko B.',
            'type'      => 'order',
            'is_read'   => false,
        ]);

        $response = $this->actingAs($this->adminA)->get(route('admin.notifications.index'));

        $response->assertStatus(200);
        $response->assertSee('Alert Eksklusif Toko A');
        $response->assertDontSee('Alert Eksklusif Toko B');
    }

    /**
     * IT-04-007: Tab filter kategori operasional menyaring data notifikasi dengan akurat.
     */
    public function test_it_04_007_tab_filter_kategori_operasional_menyaring_data_notifikasi(): void
    {
        // Notif tipe 'order'
        Notification::create([
            'client_id' => $this->storeA->id,
            'user_id'   => $this->adminA->id,
            'title'     => 'Booking Masuk #ORD-77',
            'message'   => 'Detail pesanan sewa baru.',
            'type'      => 'order',
            'is_read'   => false,
        ]);

        // Notif tipe 'return'
        Notification::create([
            'client_id' => $this->storeA->id,
            'user_id'   => $this->adminA->id,
            'title'     => 'Barang Retur Diterima #RET-88',
            'message'   => 'Pengembalian barang sewa siap diperiksa.',
            'type'      => 'return',
            'is_read'   => false,
        ]);

        // 1. Filter tab 'orders' -> hanya menampilkan booking
        $responseOrders = $this->actingAs($this->adminA)->get(route('admin.notifications.index', ['tab' => 'orders']));
        $responseOrders->assertStatus(200);
        $responseOrders->assertSee('Booking Masuk #ORD-77');
        $responseOrders->assertDontSee('Barang Retur Diterima #RET-88');

        // 2. Filter tab 'returns' -> hanya menampilkan retur
        $responseReturns = $this->actingAs($this->adminA)->get(route('admin.notifications.index', ['tab' => 'returns']));
        $responseReturns->assertStatus(200);
        $responseReturns->assertSee('Barang Retur Diterima #RET-88');
        $responseReturns->assertDontSee('Booking Masuk #ORD-77');
    }

    /**
     * IT-04-008: Admin sukses menandai satu notifikasi sebagai dibaca dan menolak manipulasi notifikasi toko lain (HTTP 403).
     */
    public function test_it_04_008_admin_sukses_tandai_satu_notifikasi_dibaca_dan_larang_toko_lain(): void
    {
        $notifA = Notification::create([
            'client_id' => $this->storeA->id,
            'user_id'   => $this->adminA->id,
            'title'     => 'Notifikasi Belum Dibaca A',
            'message'   => 'KTP pelanggan baru telah diunggah.',
            'type'      => 'identity',
            'is_read'   => false,
        ]);

        $notifB = Notification::create([
            'client_id' => $this->storeB->id,
            'user_id'   => $this->adminB->id,
            'title'     => 'Notifikasi Toko B',
            'message'   => 'Operasional toko tetangga.',
            'type'      => 'identity',
            'is_read'   => false,
        ]);

        // 1. Sukses tandai notifikasi milik toko sendiri
        $responseSuccess = $this->actingAs($this->adminA)
            ->post(route('admin.notifications.read', $notifA->id));

        $responseSuccess->assertRedirect();
        $this->assertDatabaseHas('notifications', [
            'id'      => $notifA->id,
            'is_read' => true,
        ]);

        // 2. Ditolak HTTP 403 jika mencoba menandai notifikasi milik toko lain
        $responseForbidden = $this->actingAs($this->adminA)
            ->post(route('admin.notifications.read', $notifB->id));

        $responseForbidden->assertStatus(403);
        $this->assertDatabaseHas('notifications', [
            'id'      => $notifB->id,
            'is_read' => false,
        ]);
    }

    /**
     * IT-04-009: Admin sukses menandai semua notifikasi toko sebagai dibaca tanpa mengubah data toko lain.
     */
    public function test_it_04_009_admin_sukses_tandai_semua_notifikasi_toko_sebagai_dibaca(): void
    {
        Notification::create([
            'client_id' => $this->storeA->id,
            'user_id'   => $this->adminA->id,
            'title'     => 'Notif 1 Toko A',
            'message'   => 'Pesan 1',
            'type'      => 'order',
            'is_read'   => false,
        ]);

        Notification::create([
            'client_id' => $this->storeA->id,
            'user_id'   => $this->adminA->id,
            'title'     => 'Notif 2 Toko A',
            'message'   => 'Pesan 2',
            'type'      => 'payment',
            'is_read'   => false,
        ]);

        Notification::create([
            'client_id' => $this->storeB->id,
            'user_id'   => $this->adminB->id,
            'title'     => 'Notif Toko B Tetap Unread',
            'message'   => 'Jangan diubah',
            'type'      => 'order',
            'is_read'   => false,
        ]);

        $response = $this->actingAs($this->adminA)
            ->post(route('admin.notifications.mark-all-read'));

        $response->assertRedirect();

        // Seluruh notifikasi Toko A menjadi dibaca
        $unreadA = Notification::where('client_id', $this->storeA->id)->where('is_read', false)->count();
        $this->assertSame(0, $unreadA);

        // Notifikasi Toko B tetap belum dibaca (tidak tersentuh)
        $unreadB = Notification::where('client_id', $this->storeB->id)->where('is_read', false)->count();
        $this->assertSame(1, $unreadB);
    }
}
