<?php

namespace Tests\Feature\Customer;

use App\Models\Client;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * MOD-09 — Pengiriman dan Status Pesanan (Pelacakan Customer)
 * Fitur: Notifikasi Customer / Pusat Pemberitahuan Customer (ID: 8.2-A1 / 9.1-A1)
 *
 * TINGKAT PENGUJIAN: INTEGRATION TESTING (Pengujian Integrasi Komponen)
 * Runner: Laravel HTTP Testing ($this->get(), $this->post(), $this->actingAs())
 *
 * Menguji:
 * 1. Halaman Index Global: Customer aktif sukses mengakses /notifications (HTTP 200).
 * 2. Halaman Index Tenant: Akses notifikasi dengan rute subdomain toko /client/{subdomain}/notifications.
 * 3. Otorisasi Keamanan: Pengguna tamu (guest) dicegat middleware auth dan diarahkan ke login.
 * 4. Isolasi Akun: Customer A dilarang melihat notifikasi milik Customer B.
 * 5. Tab Filter Kategori: Filter tab (unread, orders, logistics) menyaring notifikasi dengan tepat.
 * 6. Tandai Terbaca: Sukses menandai satu dan semua notifikasi dibaca, menolak manipulasi notifikasi akun lain (HTTP 403).
 *
 * Total: 6 Test Case Ringkas (IT-09-011 s/d IT-09-016)
 */
class CustomerNotificationHubTest extends TestCase
{
    use RefreshDatabase;

    private Client $storeA;
    private Client $storeB;
    private User $customerA;
    private User $customerB;

    /**
     * Menyiapkan data awal sebelum setiap skenario dijalankan.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->storeA = Client::create([
            'business_name' => 'Kamera Rental Alpha',
            'subdomain'     => 'kamera-alpha',
            'status'        => 'aktif',
        ]);

        $this->storeB = Client::create([
            'business_name' => 'Outdoor Gear Beta',
            'subdomain'     => 'outdoor-beta',
            'status'        => 'aktif',
        ]);

        $this->customerA = User::factory()->create([
            'client_id' => $this->storeA->id,
            'name'      => 'Customer Alpha',
            'role'      => 'customer',
            'status'    => 'aktif',
        ]);

        $this->customerB = User::factory()->create([
            'client_id' => $this->storeA->id,
            'name'      => 'Customer Beta',
            'role'      => 'customer',
            'status'    => 'aktif',
        ]);
    }

    // =====================================================================
    // 6 SKENARIO INTEGRATION TESTING (LARAVEL HTTP)
    // =====================================================================

    /**
     * IT-09-011: Customer aktif sukses mengakses halaman pusat notifikasi global (/notifications).
     */
    public function test_it_09_011_customer_aktif_sukses_akses_pusat_notifikasi_global(): void
    {
        Notification::create([
            'client_id' => $this->storeA->id,
            'user_id'   => $this->customerA->id,
            'title'     => 'Pesanan Rental #ORD-501 Terkonfirmasi',
            'message'   => 'Pesanan rental kamera kamu telah disetujui toko.',
            'type'      => 'order',
            'is_read'   => false,
        ]);

        $response = $this->actingAs($this->customerA)
            ->get(route('customer.notifications.index'));

        $response->assertStatus(200);
        $response->assertViewIs('customer.notifications.index');
        $response->assertSee('Pusat Pemberitahuan');
        $response->assertSee('Pesanan Rental #ORD-501 Terkonfirmasi');
        $response->assertViewHas('totalUnread', 1);
        $response->assertViewHas('totalAll', 1);
    }

    /**
     * IT-09-012: Customer sukses mengakses notifikasi berdasarkan subdomain toko (/client/{subdomain}/notifications).
     */
    public function test_it_09_012_customer_sukses_akses_notifikasi_berdasarkan_subdomain_toko(): void
    {
        Notification::create([
            'client_id' => $this->storeA->id,
            'user_id'   => $this->customerA->id,
            'title'     => 'Unit Sedang Dikirim Kurir #RES-99',
            'message'   => 'Paket unit sedang dalam perjalanan menuju lokasi Anda.',
            'type'      => 'shipment',
            'is_read'   => false,
        ]);

        $response = $this->actingAs($this->customerA)
            ->get(route('customer.notifications.tenant', ['subdomain' => $this->storeA->subdomain]));

        $response->assertStatus(200);
        $response->assertViewIs('customer.notifications.index');
        $response->assertSee('Kamera Rental Alpha');
        $response->assertSee('Unit Sedang Dikirim Kurir #RES-99');
    }

    /**
     * IT-09-013: Keamanan Otorisasi: Pengguna tamu (guest belum login) dicegat middleware auth.
     */
    public function test_it_09_013_keamanan_otorisasi_pengguna_tamu_dicegat_middleware_auth(): void
    {
        // 1. Akses rute global /notifications tanpa login
        $responseGlobal = $this->get('/notifications');
        $responseGlobal->assertRedirect();

        // 2. Akses rute tenant /client/{subdomain}/notifications tanpa login
        $responseTenant = $this->get("/client/{$this->storeA->subdomain}/notifications");
        $responseTenant->assertRedirect();
    }

    /**
     * IT-09-014: Isolasi Akun: Customer A hanya melihat notifikasi miliknya dan tidak dapat melihat notifikasi Customer B.
     */
    public function test_it_09_014_isolasi_akun_customer_a_tidak_melihat_notifikasi_customer_b(): void
    {
        Notification::create([
            'client_id' => $this->storeA->id,
            'user_id'   => $this->customerA->id,
            'title'     => 'Notifikasi Privat Customer A',
            'message'   => 'Update status pesanan untuk akun A.',
            'type'      => 'order',
            'is_read'   => false,
        ]);

        Notification::create([
            'client_id' => $this->storeA->id,
            'user_id'   => $this->customerB->id,
            'title'     => 'Notifikasi Privat Customer B',
            'message'   => 'Update status pesanan untuk akun B.',
            'type'      => 'order',
            'is_read'   => false,
        ]);

        $response = $this->actingAs($this->customerA)
            ->get(route('customer.notifications.index'));

        $response->assertStatus(200);
        $response->assertSee('Notifikasi Privat Customer A');
        $response->assertDontSee('Notifikasi Privat Customer B');
    }

    /**
     * IT-09-015: Tab filter kategori menyaring data notifikasi dengan akurat (orders, logistics, unread).
     */
    public function test_it_09_015_tab_filter_kategori_menyaring_notifikasi_dengan_akurat(): void
    {
        // Notifikasi kategori pesanan (type: order)
        Notification::create([
            'client_id' => $this->storeA->id,
            'user_id'   => $this->customerA->id,
            'title'     => 'Booking Berhasil #ORD-111',
            'message'   => 'Pesanan kamu telah masuk ke sistem.',
            'type'      => 'order',
            'is_read'   => false,
        ]);

        // Notifikasi kategori logistik (type: shipment)
        Notification::create([
            'client_id' => $this->storeA->id,
            'user_id'   => $this->customerA->id,
            'title'     => 'Barang Sedang Dikirim #SHP-222',
            'message'   => 'Nomor resi pengiriman telah diterbitkan.',
            'type'      => 'shipment',
            'is_read'   => true, // sudah dibaca
        ]);

        // 1. Filter tab 'orders' -> hanya memuat booking order
        $responseOrders = $this->actingAs($this->customerA)
            ->get(route('customer.notifications.index', ['filter' => 'orders']));
        $responseOrders->assertStatus(200);
        $responseOrders->assertSee('Booking Berhasil #ORD-111');
        $responseOrders->assertDontSee('Barang Sedang Dikirim #SHP-222');

        // 2. Filter tab 'logistics' -> hanya memuat shipment
        $responseLogistics = $this->actingAs($this->customerA)
            ->get(route('customer.notifications.index', ['filter' => 'logistics']));
        $responseLogistics->assertStatus(200);
        $responseLogistics->assertSee('Barang Sedang Dikirim #SHP-222');
        $responseLogistics->assertDontSee('Booking Berhasil #ORD-111');

        // 3. Filter tab 'unread' -> hanya memuat notifikasi belum dibaca
        $responseUnread = $this->actingAs($this->customerA)
            ->get(route('customer.notifications.index', ['filter' => 'unread']));
        $responseUnread->assertStatus(200);
        $responseUnread->assertSee('Booking Berhasil #ORD-111');
        $responseUnread->assertDontSee('Barang Sedang Dikirim #SHP-222');
    }

    /**
     * IT-09-016: Customer sukses menandai notifikasi dibaca dan dilarang memanipulasi notifikasi customer lain (HTTP 403).
     */
    public function test_it_09_016_customer_tandai_notifikasi_dibaca_dan_proteksi_hak_akses(): void
    {
        $notifA = Notification::create([
            'client_id' => $this->storeA->id,
            'user_id'   => $this->customerA->id,
            'title'     => 'Notifikasi A Belum Dibaca',
            'message'   => 'Verifikasi KTP Anda berhasil.',
            'type'      => 'identity',
            'is_read'   => false,
        ]);

        $notifB = Notification::create([
            'client_id' => $this->storeA->id,
            'user_id'   => $this->customerB->id,
            'title'     => 'Notifikasi B Milik Orang Lain',
            'message'   => 'Pesan akun customer lain.',
            'type'      => 'identity',
            'is_read'   => false,
        ]);

        // 1. Sukses menandai satu notifikasi milik sendiri
        $responseSuccess = $this->actingAs($this->customerA)
            ->post(route('customer.notifications.read', $notifA->id));

        $responseSuccess->assertRedirect();
        $this->assertDatabaseHas('notifications', [
            'id'      => $notifA->id,
            'is_read' => true,
        ]);

        // 2. Ditolak HTTP 403 jika mencoba menandai notifikasi milik customer lain
        $responseForbidden = $this->actingAs($this->customerA)
            ->post(route('customer.notifications.read', $notifB->id));

        $responseForbidden->assertStatus(403);
        $this->assertDatabaseHas('notifications', [
            'id'      => $notifB->id,
            'is_read' => false,
        ]);

        // 3. Aksi tandai semua dibaca hanya mengubah notifikasi Customer A
        Notification::create([
            'client_id' => $this->storeA->id,
            'user_id'   => $this->customerA->id,
            'title'     => 'Notifikasi A Kedua',
            'message'   => 'Pembayaran diverifikasi.',
            'type'      => 'payment',
            'is_read'   => false,
        ]);

        $responseMarkAll = $this->actingAs($this->customerA)
            ->post(route('customer.notifications.mark-all-read'));

        $responseMarkAll->assertRedirect();

        // Customer A: semua unread jadi 0
        $unreadA = Notification::where('user_id', $this->customerA->id)->where('is_read', false)->count();
        $this->assertSame(0, $unreadA);

        // Customer B: notifikasi tetap belum dibaca (tidak tersentuh)
        $unreadB = Notification::where('user_id', $this->customerB->id)->where('is_read', false)->count();
        $this->assertSame(1, $unreadB);
    }
}
