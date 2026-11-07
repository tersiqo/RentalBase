<?php

namespace Tests\Feature;

use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_it_02_001_landing_page_and_public_layout_integration(): void
    {
        Client::create([
            'business_name' => 'Kamera Malang Pro',
            'subdomain' => 'kameramalang',
            'status' => 'aktif',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewHas('packages');
        $response->assertViewHas('tenants');
        $response->assertSee('Kamera Malang Pro');
    }

    public function test_base_public_layout_renders_navbar_brand_and_navigation_links(): void
    {
        $response = $this->get('/');

        $response->assertSee('RentalBase');
        $response->assertSee('Fitur');
        $response->assertSee('Harga');
        $response->assertSee('Kontak');
        $response->assertSee('Login');
        $response->assertSee('Daftar Sekarang');
    }

    public function test_hero_section_displays_saas_headline_and_tenant_registration_link(): void
    {
        $response = $this->get('/');

        $response->assertSee('Kelola Bisnis Rental');
        $response->assertSee('dalam Satu Sistem');
        $response->assertSee('Platform Manajemen Rental');
        $response->assertSee('/register-tenant');
        $response->assertSee('Buka Toko Rental (Tenant)');
    }

    public function test_features_section_renders_four_operational_rental_capabilities(): void
    {
        $response = $this->get('/');

        $response->assertSee('Fitur Unggulan');
        $response->assertSee('Kalender Booking');
        $response->assertSee('Inventaris Cerdas');
        $response->assertSee('Faktur &amp; Denda', false);
        $response->assertSee('Laporan Analitik');
    }

    public function test_pricing_section_integrates_three_subscription_packages_and_limits(): void
    {
        $response = $this->get('/');

        $response->assertSee('Starter');
        $response->assertSee('Business');
        $response->assertSee('Professional');
        $response->assertSee('Maks. 10 jenis produk');
        $response->assertSee('Maks. 50 unit peralatan total');
        $response->assertSee('Pilih Paket Starter');
        $response->assertSee('Pilih Paket Business');
        $response->assertSee('Pilih Paket Professional');
    }

    public function test_public_footer_renders_legal_links_and_contact(): void
    {
        $response = $this->get('/');

        $response->assertSee('Syarat &amp; Ketentuan', false);
        $response->assertSee('Kebijakan Privasi');
    }

    public function test_public_layout_is_responsive_with_mobile_viewport_and_grid_breakpoints(): void
    {
        $response = $this->get('/');

        $response->assertSee('name="viewport"', false);
        $response->assertSee('width=device-width, initial-scale=1.0', false);
        $response->assertSee('grid-cols-1 md:grid-cols-2 lg:grid-cols-4', false);
    }
}
