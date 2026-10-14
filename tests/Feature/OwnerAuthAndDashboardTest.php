<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientRegistration;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OwnerAuthAndDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Masuk ke Akun Anda');
    }

    public function test_owner_can_login_and_is_redirected_to_owner_dashboard(): void
    {
        $owner = User::create([
            'name' => 'Owner Platform',
            'email' => 'owner@rentalbase.id',
            'password' => Hash::make('password123'),
            'role' => 'owner',
            'status' => 'aktif',
        ]);

        $response = $this->post('/login', [
            'email' => 'owner@rentalbase.id',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/owner/dashboard');
        $this->assertAuthenticatedAs($owner);
    }

    public function test_admin_rental_can_login_and_is_redirected_to_admin_dashboard(): void
    {
        $client = Client::create([
            'business_name' => 'Kamera Jaya Rental',
            'subdomain' => 'kamerajaya',
            'status' => 'aktif',
        ]);

        $admin = User::create([
            'name' => 'Admin Kamera',
            'email' => 'admin@kamerajaya.com',
            'password' => Hash::make('password123'),
            'role' => 'admin_rental',
            'status' => 'aktif',
            'client_id' => $client->id,
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@kamerajaya.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => Hash::make('correctpassword'),
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'admin@test.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::create([
            'name' => 'Nonaktif User',
            'email' => 'inactive@test.com',
            'password' => Hash::make('password123'),
            'role' => 'admin_rental',
            'status' => 'nonaktif',
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'inactive@test.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_authenticated_owner_visiting_login_redirects_to_owner_dashboard(): void
    {
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@rentalbase.id',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($owner)->get('/login');
        $response->assertRedirect('/owner/dashboard');
    }

    public function test_guest_cannot_access_owner_dashboard(): void
    {
        $response = $this->get('/owner/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_admin_rental_cannot_access_owner_dashboard(): void
    {
        $client = Client::create([
            'business_name' => 'Rental Toko',
            'subdomain' => 'rentaltoko',
            'status' => 'aktif',
        ]);

        $admin = User::create([
            'name' => 'Admin Rental',
            'email' => 'admin@rental.com',
            'password' => Hash::make('password'),
            'role' => 'admin_rental',
            'status' => 'aktif',
            'client_id' => $client->id,
        ]);

        $response = $this->actingAs($admin)->get('/owner/dashboard');

        // Should be redirected to admin dashboard with error
        $response->assertRedirect('/admin/dashboard');
        $response->assertSessionHas('error');
    }

    public function test_owner_cannot_access_admin_rental_dashboard(): void
    {
        $owner = User::create([
            'name' => 'Owner Pusat',
            'email' => 'owner@rentalbase.id',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($owner)->get('/admin/dashboard');

        // Should be redirected to owner dashboard with error
        $response->assertRedirect('/owner/dashboard');
        $response->assertSessionHas('error');
    }

    public function test_owner_can_access_owner_dashboard_with_metrics_and_tenant_list(): void
    {
        $this->withoutVite();

        $owner = User::create([
            'name' => 'Owner Platform',
            'email' => 'owner@rentalbase.id',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'status' => 'aktif',
        ]);

        $client1 = Client::create([
            'business_name' => 'Alpha Rental',
            'subdomain' => 'alpha',
            'status' => 'aktif',
        ]);

        Subscription::create([
            'client_id' => $client1->id,
            'plan_name' => 'business',
            'price' => 99000,
            'billing_cycle' => 'monthly',
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($owner)->get('/owner/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard Owner Pusat');
        $response->assertSee('Alpha Rental');
        $response->assertSee('alpha.rentalbase.id');
        $response->assertSee('business');
        $response->assertSee('Aktif');
    }

    public function test_user_can_logout(): void
    {
        $owner = User::create([
            'name' => 'Owner Platform',
            'email' => 'owner@rentalbase.id',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($owner)->post('/owner/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
