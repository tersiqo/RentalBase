<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantThemeSetupIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_it_02_005_tenant_onboarding_theme_color_setup_flow(): void
    {
        $client = Client::create([
            'business_name' => 'Kamera Rent Studio',
            'subdomain' => 'kamerarent',
            'status' => 'pending_setup',
            'theme_color' => '#F97316',
        ]);

        $admin = User::factory()->create([
            'client_id' => $client->id,
            'role' => 'admin_rental',
            'status' => 'pending_setup',
        ]);

        $response = $this->actingAs($admin)->post('/setup-theme', [
            'theme_color' => '#3B82F6',
        ]);

        $response->assertRedirect(route('tenant.register'));
        $response->assertSessionHas('registration_theme_selected', true);

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'theme_color' => '#3B82F6',
        ]);
    }

    public function test_tenant_theme_setup_get_route_redirects_to_login(): void
    {
        $response = $this->get('/setup-theme');

        $response->assertStatus(302);
    }

    public function test_tenant_theme_setup_validates_hex_color_format(): void
    {
        $client = Client::create([
            'business_name' => 'Drone Rent',
            'subdomain' => 'dronerent',
            'status' => 'pending_setup',
        ]);

        $admin = User::factory()->create([
            'client_id' => $client->id,
            'role' => 'admin_rental',
            'status' => 'pending_setup',
        ]);

        $response = $this->actingAs($admin)->post('/setup-theme', [
            'theme_color' => 'invalid-hex-code',
        ]);

        $response->assertSessionHasErrors('theme_color');
    }

    public function test_guest_cannot_submit_theme_setup(): void
    {
        $response = $this->post('/setup-theme', [
            'theme_color' => '#10B981',
        ]);

        $response->assertStatus(302);
        $this->assertGuest();
    }
}
