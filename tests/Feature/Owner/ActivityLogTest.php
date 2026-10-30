<?php

namespace Tests\Feature\Owner;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_guest_cannot_access_owner_activity_logs(): void
    {
        $response = $this->get('/owner/activity-logs');

        $response->assertRedirect('/owner/login');
    }

    public function test_admin_cannot_access_owner_activity_logs(): void
    {
        $client = Client::create([
            'business_name' => 'Toko Bayi Ceria',
            'subdomain' => 'tokobayiceria',
            'status' => 'aktif',
        ]);

        $admin = User::factory()->create([
            'client_id' => $client->id,
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($admin)->get('/owner/activity-logs');

        $response->assertRedirect('/admin/dashboard');
    }

    public function test_owner_can_view_activity_logs_page(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'status' => 'aktif',
        ]);

        $client = Client::create([
            'business_name' => 'Toko Bayi Ceria',
            'subdomain' => 'tokobayiceria',
            'status' => 'aktif',
        ]);

        ActivityLog::create([
            'client_id'   => $client->id,
            'user_id'     => $owner->id,
            'action'      => 'tenant_approved',
            'module'      => 'tenants',
            'description' => 'Owner menyetujui pendaftaran tenant Toko Bayi Ceria.',
            'ip_address'  => '127.0.0.1',
            'user_agent'  => 'Mozilla/5.0',
        ]);

        $response = $this->actingAs($owner)->get('/owner/activity-logs');

        $response->assertStatus(200);
        $response->assertSee('Audit Trail &amp; Activity Log', false);
        $response->assertSee('Toko Bayi Ceria');
        $response->assertSee('tenant_approved');
    }

    public function test_owner_can_filter_logs_by_tenant(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'status' => 'aktif',
        ]);

        $clientA = Client::create([
            'business_name' => 'Rental A',
            'subdomain' => 'rentala',
            'status' => 'aktif',
        ]);

        $clientB = Client::create([
            'business_name' => 'Rental B',
            'subdomain' => 'rentalb',
            'status' => 'aktif',
        ]);

        ActivityLog::create([
            'client_id'   => $clientA->id,
            'user_id'     => $owner->id,
            'action'      => 'action_a',
            'module'      => 'orders',
            'description' => 'Aktivitas khusus Rental A',
        ]);

        ActivityLog::create([
            'client_id'   => $clientB->id,
            'user_id'     => $owner->id,
            'action'      => 'action_b',
            'module'      => 'orders',
            'description' => 'Aktivitas khusus Rental B',
        ]);

        $response = $this->actingAs($owner)->get('/owner/activity-logs?client_id=' . $clientA->id);

        $response->assertStatus(200);
        $response->assertSee('Aktivitas khusus Rental A');
        $response->assertDontSee('Aktivitas khusus Rental B');
    }

    public function test_owner_can_get_log_detail_via_json(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'status' => 'aktif',
        ]);

        $log = ActivityLog::create([
            'user_id'     => $owner->id,
            'action'      => 'platform_backup',
            'module'      => 'system',
            'description' => 'Backup rutin database selesai dieksekusi.',
            'ip_address'  => '192.168.1.10',
            'user_agent'  => 'PostmanRuntime/7.28.4',
        ]);

        $response = $this->actingAs($owner)->getJson('/owner/activity-logs/' . $log->id);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'id' => $log->id,
                'action' => 'platform_backup',
                'module' => 'system',
                'description' => 'Backup rutin database selesai dieksekusi.',
                'ip_address' => '192.168.1.10',
            ],
        ]);
    }
}
