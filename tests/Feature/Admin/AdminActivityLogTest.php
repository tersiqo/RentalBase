<?php

namespace Tests\Feature\Admin;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminActivityLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_guest_cannot_access_admin_activity_logs(): void
    {
        $response = $this->get('/admin/activity-logs');

        $response->assertRedirect('/');
    }

    public function test_customer_cannot_access_admin_activity_logs(): void
    {
        $client = Client::create([
            'business_name' => 'Toko Bayi Ceria',
            'subdomain' => 'tokobayiceria',
            'status' => 'aktif',
        ]);

        $customer = User::factory()->create([
            'client_id' => $client->id,
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($customer)->get('/admin/activity-logs');

        $response->assertRedirect('/');
    }

    public function test_admin_can_view_own_store_activity_logs(): void
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

        ActivityLog::create([
            'client_id'   => $client->id,
            'user_id'     => $admin->id,
            'action'      => 'order_shipped',
            'module'      => 'shipments',
            'description' => 'Pesanan #ORD-999 dikirim via JNE (Resi: JNE999).',
        ]);

        $response = $this->actingAs($admin)->get('/admin/activity-logs');

        $response->assertStatus(200);
        $response->assertSee('Log Aktivitas Toko');
        $response->assertSee('Pesanan #ORD-999 dikirim via JNE');
    }

    public function test_admin_cannot_see_other_store_activity_logs(): void
    {
        $storeA = Client::create([
            'business_name' => 'Toko A',
            'subdomain' => 'tokoa',
            'status' => 'aktif',
        ]);

        $storeB = Client::create([
            'business_name' => 'Toko B',
            'subdomain' => 'tokob',
            'status' => 'aktif',
        ]);

        $adminA = User::factory()->create([
            'client_id' => $storeA->id,
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        $adminB = User::factory()->create([
            'client_id' => $storeB->id,
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        ActivityLog::create([
            'client_id'   => $storeA->id,
            'user_id'     => $adminA->id,
            'action'      => 'action_store_a',
            'module'      => 'orders',
            'description' => 'Aktivitas rahasia Toko A',
        ]);

        ActivityLog::create([
            'client_id'   => $storeB->id,
            'user_id'     => $adminB->id,
            'action'      => 'action_store_b',
            'module'      => 'orders',
            'description' => 'Aktivitas rahasia Toko B',
        ]);

        $response = $this->actingAs($adminA)->get('/admin/activity-logs');

        $response->assertStatus(200);
        $response->assertSee('Aktivitas rahasia Toko A');
        $response->assertDontSee('Aktivitas rahasia Toko B');
    }

    public function test_admin_cannot_access_other_store_log_detail(): void
    {
        $storeA = Client::create([
            'business_name' => 'Toko A',
            'subdomain' => 'tokoa',
            'status' => 'aktif',
        ]);

        $storeB = Client::create([
            'business_name' => 'Toko B',
            'subdomain' => 'tokob',
            'status' => 'aktif',
        ]);

        $adminA = User::factory()->create([
            'client_id' => $storeA->id,
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        $adminB = User::factory()->create([
            'client_id' => $storeB->id,
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        $logB = ActivityLog::create([
            'client_id'   => $storeB->id,
            'user_id'     => $adminB->id,
            'action'      => 'action_b',
            'module'      => 'orders',
            'description' => 'Log milik Toko B',
        ]);

        $response = $this->actingAs($adminA)->getJson('/admin/activity-logs/' . $logB->id);

        $response->assertStatus(403);
    }
}
