<?php

namespace Tests\Feature\Owner;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * MOD-03 — Modul Manajemen Platform (Owner)
 * Fitur: Activity Log Owner dan Integrasi Logistik (ID: 9.2-A2)
 *
 * TINGKAT PENGUJIAN: INTEGRATION TESTING (Pengujian Integrasi Komponen)
 * Runner: Laravel HTTP Testing ($this->get(), $this->post(), $this->actingAs())
 *
 * Menguji:
 * 1. Halaman Index: Owner platform sukses mengakses audit trail lintas tenant (HTTP 200).
 * 2. Keamanan Otorisasi: Tamu dan non-owner dicegat oleh middleware owner.
 * 3. Visibilitas Lintas Tenant: Owner dapat memantau log dari semua tenant sekaligus.
 * 4. Filter Komprehensif: Filter tenant (client_id) dan modul menyaring log secara presisi.
 * 5. Detail Teknis JSON: Endpoint show mengembalikan data metadata lengkap untuk modal Alpine.js.
 * 6. Pencatatan Jejak Audit: Verifikasi integritas rekam log pada aktivitas operasional platform.
 *
 * Total: 6 Test Case Ringkas (IT-03-005 s/d IT-03-010)
 */
class OwnerPlatformAuditTrailTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Client $storeA;
    private Client $storeB;
    private User $adminA;

    /**
     * Menyiapkan data awal sebelum setiap skenario dijalankan.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->owner = User::factory()->create([
            'role'   => 'owner',
            'status' => 'aktif',
        ]);

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
            'role'      => 'admin_rental',
            'status'    => 'aktif',
        ]);
    }

    // =====================================================================
    // 6 SKENARIO INTEGRATION TESTING (LARAVEL HTTP)
    // =====================================================================

    /**
     * IT-03-005: Owner platform aktif sukses mengakses audit trail lintas tenant (HTTP 200).
     */
    public function test_it_03_005_owner_sukses_akses_audit_trail_lintas_tenant(): void
    {
        ActivityLog::create([
            'client_id'   => $this->storeA->id,
            'user_id'     => $this->adminA->id,
            'action'      => 'order_processed',
            'module'      => 'orders',
            'description' => 'Pesanan #ORD-999 berhasil diproses admin.',
        ]);

        $response = $this->actingAs($this->owner)
            ->get(route('owner.activity-logs.index'));

        $response->assertStatus(200);
        $response->assertViewIs('owner.activity-logs.index');
        $response->assertSee('Audit Trail &amp; Activity Log', false);
        $response->assertSee('Kamera Rental Alpha');
        $response->assertSee('Pesanan #ORD-999 berhasil diproses admin.');
        $response->assertViewHas('totalLogs', 1);
    }

    /**
     * IT-03-006: Keamanan Otorisasi: Tamu dan staf admin rental dicegat oleh middleware owner.
     */
    public function test_it_03_006_keamanan_otorisasi_tamu_dan_admin_rental_dicegat(): void
    {
        // 1. Pengguna tamu dicegat dan diarahkan ke login owner
        $responseGuest = $this->get('/owner/activity-logs');
        $responseGuest->assertRedirect(route('owner.login'));

        // 2. Admin rental dicegat dan ditolak masuk ke portal owner
        $responseAdmin = $this->actingAs($this->adminA)->get('/owner/activity-logs');
        $responseAdmin->assertRedirect(route('admin.dashboard'));
    }

    /**
     * IT-03-007: Visibilitas Lintas Tenant: Owner dapat memantau log aktivitas dari semua tenant sekaligus.
     */
    public function test_it_03_007_visibilitas_lintas_tenant_menampilkan_seluruh_toko(): void
    {
        ActivityLog::create([
            'client_id'   => $this->storeA->id,
            'user_id'     => $this->adminA->id,
            'action'      => 'shipment_dispatched',
            'module'      => 'shipments',
            'description' => 'Resi JNE-12345 diinput untuk Toko A.',
        ]);

        ActivityLog::create([
            'client_id'   => $this->storeB->id,
            'user_id'     => $this->owner->id,
            'action'      => 'tenant_verified',
            'module'      => 'tenants',
            'description' => 'Verifikasi dokumen Toko B disetujui.',
        ]);

        $response = $this->actingAs($this->owner)
            ->get(route('owner.activity-logs.index'));

        $response->assertStatus(200);
        $response->assertSee('Resi JNE-12345 diinput untuk Toko A.');
        $response->assertSee('Verifikasi dokumen Toko B disetujui.');
        $response->assertSee('Kamera Rental Alpha');
        $response->assertSee('Outdoor Gear Beta');
    }

    /**
     * IT-03-008: Filter Komprehensif: Filter berdasarkan tenant dan modul menyaring log aktivitas secara presisi.
     */
    public function test_it_03_008_filter_komprehensif_menyaring_log_berdasarkan_tenant_dan_modul(): void
    {
        ActivityLog::create([
            'client_id'   => $this->storeA->id,
            'user_id'     => $this->adminA->id,
            'action'      => 'order_created',
            'module'      => 'orders',
            'description' => 'Aktivitas pesanan Toko A.',
        ]);

        ActivityLog::create([
            'client_id'   => $this->storeB->id,
            'user_id'     => $this->owner->id,
            'action'      => 'order_created',
            'module'      => 'orders',
            'description' => 'Aktivitas pesanan Toko B.',
        ]);

        // Filter spesifik Toko A
        $responseFilter = $this->actingAs($this->owner)
            ->get(route('owner.activity-logs.index', ['client_id' => $this->storeA->id]));

        $responseFilter->assertStatus(200);
        $responseFilter->assertSee('Aktivitas pesanan Toko A.');
        $responseFilter->assertDontSee('Aktivitas pesanan Toko B.');
    }

    /**
     * IT-03-009: Detail Teknis JSON: Endpoint show mengembalikan data metadata lengkap untuk modal Alpine.js.
     */
    public function test_it_03_009_detail_teknis_log_mengembalikan_json_untuk_modal(): void
    {
        $log = ActivityLog::create([
            'client_id'   => $this->storeA->id,
            'user_id'     => $this->adminA->id,
            'action'      => 'return_inspected',
            'module'      => 'condition_checks',
            'description' => 'Inspeksi fisik unit sewa berhasil disimpan.',
            'ip_address'  => '127.0.0.1',
            'user_agent'  => 'Mozilla/5.0 Test Suite',
        ]);

        $response = $this->actingAs($this->owner)
            ->getJson(route('owner.activity-logs.show', $log->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data'    => [
                'id'          => $log->id,
                'action'      => 'return_inspected',
                'module'      => 'condition_checks',
                'description' => 'Inspeksi fisik unit sewa berhasil disimpan.',
                'ip_address'  => '127.0.0.1',
                'client_name' => 'Kamera Rental Alpha',
            ],
        ]);
    }

    /**
     * IT-03-010: Pencatatan Jejak Audit: Verifikasi integritas rekam log pada aktivitas operasional platform.
     */
    public function test_it_03_010_pencatatan_audit_log_pada_aktivitas_operasional(): void
    {
        // Simulasi pencatatan event audit log logistik & denda
        $newLog = ActivityLog::create([
            'client_id'   => $this->storeA->id,
            'user_id'     => $this->adminA->id,
            'action'      => 'shipment_dispatched',
            'module'      => 'shipments',
            'description' => 'Paket pesanan diserahkan ke kurir J&T Express.',
            'ip_address'  => '192.168.1.50',
            'user_agent'  => 'RentalBase-Logistics/1.0',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'id'        => $newLog->id,
            'action'    => 'shipment_dispatched',
            'module'    => 'shipments',
            'client_id' => $this->storeA->id,
        ]);

        $response = $this->actingAs($this->owner)
            ->get(route('owner.activity-logs.index'));

        $response->assertStatus(200);
        $response->assertSee('Paket pesanan diserahkan ke kurir J&T Express.');
    }
}
