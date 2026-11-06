<?php

namespace Tests\Unit;

use App\Models\Client;
use App\Models\ClientRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantVerificationGateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_pending_tenant_registration_login_is_held_with_review_notification(): void
    {
        $client = Client::create([
            'business_name' => 'Toko Rental Pending',
            'subdomain' => 'pendingtoko',
            'status' => 'menunggu_verifikasi',
        ]);

        User::factory()->create([
            'client_id' => $client->id,
            'role' => 'admin_rental',
            'status' => 'menunggu_verifikasi',
            'email' => 'pending@rental.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'pending@rental.com',
            'password' => 'password123',
            'login_type' => 'admin',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('info');
        $this->assertGuest();
    }

    public function test_admin_middleware_blocks_pending_tenant_from_dashboard(): void
    {
        $client = Client::create([
            'business_name' => 'Toko Rental Pending',
            'subdomain' => 'pendingtoko',
            'status' => 'menunggu_verifikasi',
        ]);

        $userPending = User::factory()->create([
            'client_id' => $client->id,
            'role' => 'admin_rental',
            'status' => 'menunggu_verifikasi',
            'email' => 'pending@rental.com',
        ]);

        $response = $this->actingAs($userPending)->get('/admin/dashboard');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_rejected_tenant_registration_redirects_to_register_with_rejection_reason(): void
    {
        $client = Client::create([
            'business_name' => 'Toko Rental Ditolak',
            'subdomain' => 'ditolaktoko',
            'status' => 'ditolak',
        ]);

        User::factory()->create([
            'client_id' => $client->id,
            'role' => 'admin_rental',
            'status' => 'ditolak',
            'email' => 'ditolak@rental.com',
            'password' => bcrypt('password123'),
        ]);

        ClientRegistration::create([
            'business_name' => 'Toko Rental Ditolak',
            'subdomain' => 'ditolaktoko',
            'admin_name' => 'Admin Ditolak',
            'admin_email' => 'ditolak@rental.com',
            'admin_phone' => '081234567890',
            'admin_password' => bcrypt('password123'),
            'plan_name' => 'starter',
            'rejection_reason' => 'Identitas KTP PIC tidak terbaca jelas.',
        ]);

        $response = $this->post('/login', [
            'email' => 'ditolak@rental.com',
            'password' => 'password123',
            'login_type' => 'admin',
        ]);

        $response->assertRedirect(route('tenant.register'));
        $response->assertSessionHas('rejection_reason', 'Identitas KTP PIC tidak terbaca jelas.');
    }

    public function test_approved_active_tenant_can_login_and_access_admin_dashboard(): void
    {
        $client = Client::create([
            'business_name' => 'Toko Rental Sukses',
            'subdomain' => 'suksestoko',
            'status' => 'aktif',
        ]);

        $userActive = User::factory()->create([
            'client_id' => $client->id,
            'role' => 'admin_rental',
            'status' => 'aktif',
            'email' => 'admin@suksestoko.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@suksestoko.com',
            'password' => 'password123',
            'login_type' => 'admin',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($userActive);
    }

    public function test_waiting_verification_and_setup_tenant_routes_are_protected(): void
    {
        $responseWaiting = $this->get('/waiting-verification');
        $responseWaiting->assertRedirect(route('login'));

        $responseSetupGuest = $this->get('/setup-tenant');
        $responseSetupGuest->assertRedirect('/');
    }
}
