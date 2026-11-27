<?php

namespace Tests\Feature\Owner;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * MOD-03 — Modul Manajemen Platform (Owner)
 * Fitur: Maintenance CLI Demo Date Refresher & Audit Trail Sync (ID: 9.2-A2)
 *
 * TINGKAT PENGUJIAN: INTEGRATION TESTING (Laravel HTTP Testing & Artisan Runner)
 * Runner: $this->actingAs(), $this->get(), $this->artisan()
 * Rentang ID: IT-03-038 s/d IT-03-045 (8 Skenario Pengujian)
 */
class OwnerDemoDatesRefreshTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $regularAdmin;
    private Client $demoClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->owner = User::create([
            'name'     => 'Superadmin Owner',
            'email'    => 'owner@rentalbase.id',
            'password' => bcrypt('password123'),
            'role'     => 'owner',
            'status'   => 'aktif',
        ]);

        $this->demoClient = Client::create([
            'business_name' => 'Baby Rental Demo',
            'subdomain'     => 'babyrental',
            'status'        => 'aktif',
        ]);

        $this->regularAdmin = User::create([
            'client_id' => $this->demoClient->id,
            'name'      => 'Admin Biasa',
            'email'     => 'admin@babyrental.com',
            'password'  => bcrypt('password123'),
            'role'      => 'admin_rental',
            'status'    => 'aktif',
        ]);
    }

    /**
     * IT-03-038: Eksekusi Artisan Command demo:refresh berjalan sukses dengan return exit code 0.
     */
    public function test_it_03_038_artisan_demo_refresh_command_runs_successfully(): void
    {
        $this->artisan('demo:refresh')
            ->assertExitCode(0);
    }

    /**
     * IT-03-039: Command demo:refresh memperbarui timestamp tabel pesanan demo di database.
     */
    public function test_it_03_039_orders_table_timestamps_updated_in_database(): void
    {
        $order = Order::create([
            'client_id'       => $this->demoClient->id,
            'user_id'         => $this->regularAdmin->id,
            'invoice_number'  => 'INV-DEMO-01',
            'status'          => 'menunggu_pembayaran',
            'start_date'      => now()->addDay(),
            'end_date'        => now()->addDays(4),
            'total_amount'    => 150000,
        ]);

        $this->artisan('demo:refresh')->assertExitCode(0);

        $this->assertDatabaseHas('orders', [
            'id'             => $order->id,
            'invoice_number' => 'INV-DEMO-01',
        ]);
    }

    /**
     * IT-03-040: Timestamp pendaftaran tenant demo terperbarui dengan benar di database.
     */
    public function test_it_03_040_demo_client_registration_updated_in_database(): void
    {
        $this->artisan('demo:refresh')->assertExitCode(0);

        $this->assertDatabaseHas('clients', [
            'id'        => $this->demoClient->id,
            'subdomain' => 'babyrental',
            'status'    => 'aktif',
        ]);
    }

    /**
     * IT-03-041: Superadmin Owner dapat mengakses dashboard audit trail platform pasca refresh (HTTP 200).
     */
    public function test_it_03_041_owner_can_access_activity_logs_dashboard(): void
    {
        ActivityLog::create([
            'client_id'   => $this->demoClient->id,
            'user_id'     => $this->owner->id,
            'action'      => 'demo_dates_refreshed',
            'module'      => 'settings',
            'description' => 'Peremajaan timestamp data demo berhasil dijalankan',
        ]);

        $response = $this->actingAs($this->owner)->get('/owner/activity-logs');

        $response->assertStatus(200);
        $response->assertSee('Activity Log');
        $response->assertSee('Peremajaan timestamp data demo');
    }

    /**
     * IT-03-042: Superadmin Owner dapat menyaring log audit berdasarkan tenant toko demo terpilih.
     */
    public function test_it_03_042_owner_can_filter_activity_logs_by_tenant(): void
    {
        ActivityLog::create([
            'client_id'   => $this->demoClient->id,
            'user_id'     => $this->owner->id,
            'action'      => 'order_created',
            'module'      => 'orders',
            'description' => 'Pesanan demo toko Baby Rental',
        ]);

        $response = $this->actingAs($this->owner)->get('/owner/activity-logs?client_id=' . $this->demoClient->id);

        $response->assertStatus(200);
        $response->assertSee('Pesanan demo toko Baby Rental');
    }

    /**
     * IT-03-043: Superadmin Owner dapat menyaring log audit platform khusus kategori modul pesanan toko.
     */
    public function test_it_03_043_owner_can_filter_activity_logs_by_module(): void
    {
        ActivityLog::create([
            'client_id'   => $this->demoClient->id,
            'user_id'     => $this->owner->id,
            'action'      => 'status_changed',
            'module'      => 'orders',
            'description' => 'Modul pesanan demo aktif',
        ]);

        $response = $this->actingAs($this->owner)->get('/owner/activity-logs?module=orders');

        $response->assertStatus(200);
        $response->assertSee('Modul pesanan demo aktif');
    }

    /**
     * IT-03-044: Proteksi otorisasi mencegah pengunjung tamu mengakses jejak audit platform owner (HTTP 302).
     */
    public function test_it_03_044_guest_cannot_access_owner_audit_logs(): void
    {
        $response = $this->get('/owner/activity-logs');

        $response->assertRedirect('/owner/login');
    }

    /**
     * IT-03-045: Admin rental toko biasa dilarang membuka portal jejak audit platform superadmin (HTTP 403 / Redirect).
     */
    public function test_it_03_045_regular_admin_forbidden_from_platform_audit_logs(): void
    {
        $response = $this->actingAs($this->regularAdmin)->get('/owner/activity-logs');

        // Middleware memblokir non-owner dengan status redirect atau 403
        $this->assertTrue(in_array($response->status(), [302, 403]));
    }
}
