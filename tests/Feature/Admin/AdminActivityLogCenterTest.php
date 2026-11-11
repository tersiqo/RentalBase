<?php

namespace Tests\Feature\Admin;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * MOD-04 — Modul Pengelolaan Toko, Metode Pembayaran dan Dashboard (Admin Rental)
 * Fitur: Activity Log Admin / Jejak Audit Toko (ID: 9.2-A1)
 *
 * TINGKAT PENGUJIAN: INTEGRATION TESTING (Pengujian Integrasi Komponen)
 * Runner: Laravel HTTP Testing ($this->get(), $this->post(), $this->actingAs())
 *
 * Menguji:
 * 1. Halaman Index: Admin rental aktif sukses mengakses jejak audit toko (HTTP 200).
 * 2. Keamanan Otorisasi: Tamu dan customer dicegat oleh middleware admin.
 * 3. Isolasi Multi-Tenant: Admin Toko A tidak dapat melihat rekaman log Toko B.
 * 4. Filter Komprehensif: Filter berdasarkan staf (user_id) dan modul menyaring log secara presisi.
 * 5. Detail Teknis JSON: Endpoint show mengembalikan metadata lengkap untuk modal Alpine.js.
 * 6. Proteksi Akses Detail: Admin ditolak HTTP 403 Forbidden jika mencoba mengakses detail log toko lain.
 *
 * Total: 6 Test Case Ringkas (IT-04-010 s/d IT-04-015)
 */
class AdminActivityLogCenterTest extends TestCase
{
    use RefreshDatabase;

    private Client $storeA;
    private Client $storeB;
    private User $adminA;
    private User $adminB;
    private User $customer;

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

        $this->adminA = User::factory()->create([
            'client_id' => $this->storeA->id,
            'name'      => 'Admin Toko Alpha',
            'role'      => 'admin_rental',
            'status'    => 'aktif',
        ]);

        $this->adminB = User::factory()->create([
            'client_id' => $this->storeB->id,
            'name'      => 'Admin Toko Beta',
            'role'      => 'admin_rental',
            'status'    => 'aktif',
        ]);

        $this->customer = User::factory()->create([
            'client_id' => $this->storeA->id,
            'name'      => 'Pelanggan Biasa',
            'role'      => 'customer',
            'status'    => 'aktif',
        ]);
    }

    // =====================================================================
    // 6 SKENARIO INTEGRATION TESTING (LARAVEL HTTP)
    // =====================================================================

    /**
     * IT-04-010: Admin rental aktif sukses mengakses halaman jejak audit toko (HTTP 200).
     */
    public function test_it_04_010_admin_aktif_sukses_akses_halaman_jejak_audit_toko(): void
    {
        ActivityLog::create([
            'client_id'   => $this->storeA->id,
            'user_id'     => $this->adminA->id,
            'action'      => 'order_verified',
            'module'      => 'orders',
            'description' => 'Pesanan #ORD-101 berhasil diverifikasi staf admin.',
        ]);

        $response = $this->actingAs($this->adminA)
            ->get(route('admin.activity-logs.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.activity-logs.index');
        $response->assertSee('Log Aktivitas Toko');
        $response->assertSee('Pesanan #ORD-101 berhasil diverifikasi staf admin.');
        $response->assertViewHas('totalLogs', 1);
    }

    /**
     * IT-04-011: Keamanan Otorisasi: Pengguna tamu dan customer dicegat oleh middleware admin.
     */
    public function test_it_04_011_keamanan_otorisasi_tamu_dan_customer_dicegat_middleware(): void
    {
        // 1. Pengguna tamu (guest) mencoba akses rute log admin
        $responseGuest = $this->get('/admin/activity-logs');
        $responseGuest->assertRedirect('/');

        // 2. User role customer mencoba akses rute log admin
        $responseCustomer = $this->actingAs($this->customer)->get('/admin/activity-logs');
        $responseCustomer->assertRedirect('/');
    }

    /**
     * IT-04-012: Isolasi Multi-Tenant: Admin Toko A tidak melihat rekaman log aktivitas milik Toko B.
     */
    public function test_it_04_012_isolasi_multi_tenant_admin_toko_a_tidak_melihat_log_toko_b(): void
    {
        ActivityLog::create([
            'client_id'   => $this->storeA->id,
            'user_id'     => $this->adminA->id,
            'action'      => 'stock_updated',
            'module'      => 'products',
            'description' => 'Stok unit kamera Toko A diperbarui.',
        ]);

        ActivityLog::create([
            'client_id'   => $this->storeB->id,
            'user_id'     => $this->adminB->id,
            'action'      => 'stock_updated',
            'module'      => 'products',
            'description' => 'Stok tenda kemping Toko B diperbarui.',
        ]);

        $response = $this->actingAs($this->adminA)
            ->get(route('admin.activity-logs.index'));

        $response->assertStatus(200);
        $response->assertSee('Stok unit kamera Toko A diperbarui.');
        $response->assertDontSee('Stok tenda kemping Toko B diperbarui.');
    }

    /**
     * IT-04-013: Filter Komprehensif: Filter berdasarkan staf dan modul menyaring log toko dengan akurat.
     */
    public function test_it_04_013_filter_komprehensif_menyaring_log_berdasarkan_staf_dan_modul(): void
    {
        // Staf kedua di Toko A
        $staff2 = User::factory()->create([
            'client_id' => $this->storeA->id,
            'name'      => 'Staf Gudang Toko A',
            'role'      => 'admin_rental',
            'status'    => 'aktif',
        ]);

        ActivityLog::create([
            'client_id'   => $this->storeA->id,
            'user_id'     => $this->adminA->id,
            'action'      => 'payment_verified',
            'module'      => 'orders',
            'description' => 'Bukti bayar order #ORD-202 diverifikasi.',
        ]);

        ActivityLog::create([
            'client_id'   => $this->storeA->id,
            'user_id'     => $staff2->id,
            'action'      => 'shipment_dispatched',
            'module'      => 'shipments',
            'description' => 'Unit pesanan #ORD-303 diserahkan ke kurir JNE.',
        ]);

        // Filter aktivitas yang dilakukan staf2
        $responseFilter = $this->actingAs($this->adminA)
            ->get(route('admin.activity-logs.index', ['user_id' => $staff2->id, 'module' => 'shipments']));

        $responseFilter->assertStatus(200);
        $responseFilter->assertSee('Unit pesanan #ORD-303 diserahkan ke kurir JNE.');
        $responseFilter->assertDontSee('Bukti bayar order #ORD-202 diverifikasi.');
    }

    /**
     * IT-04-014: Detail Teknis JSON: Endpoint show mengembalikan data metadata lengkap untuk modal Alpine.js.
     */
    public function test_it_04_014_detail_teknis_log_modal_mengembalikan_json_yang_lengkap(): void
    {
        $log = ActivityLog::create([
            'client_id'   => $this->storeA->id,
            'user_id'     => $this->adminA->id,
            'action'      => 'unit_inspected',
            'module'      => 'condition_checks',
            'description' => 'Inspeksi fisik kamera Sony A7IV selesai dicatat.',
            'ip_address'  => '127.0.0.1',
            'user_agent'  => 'RentalBase Test Client/1.0',
        ]);

        $response = $this->actingAs($this->adminA)
            ->getJson(route('admin.activity-logs.show', $log->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data'    => [
                'id'          => $log->id,
                'action'      => 'unit_inspected',
                'module'      => 'condition_checks',
                'description' => 'Inspeksi fisik kamera Sony A7IV selesai dicatat.',
                'ip_address'  => '127.0.0.1',
                'actor_name'  => 'Admin Toko Alpha',
            ],
        ]);
    }

    /**
     * IT-04-015: Proteksi Multi-Tenant Detail: Admin ditolak HTTP 403 Forbidden jika mencoba mengakses detail log toko lain.
     */
    public function test_it_04_015_proteksi_multi_tenant_detail_menolak_akses_log_toko_lain(): void
    {
        $logB = ActivityLog::create([
            'client_id'   => $this->storeB->id,
            'user_id'     => $this->adminB->id,
            'action'      => 'secret_action',
            'module'      => 'orders',
            'description' => 'Catatan operasional rahasia Toko B.',
        ]);

        $response = $this->actingAs($this->adminA)
            ->getJson(route('admin.activity-logs.show', $logB->id));

        $response->assertStatus(403);
    }
}
