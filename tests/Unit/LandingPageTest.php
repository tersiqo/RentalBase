<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_ut_02_001_landing_page_and_public_layout(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewHas('packages');
        $response->assertViewHas('tenants');
    }

    public function test_public_navbar_displays_brand_navigation_and_cta_login(): void
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

    public function test_features_section_displays_four_rental_management_capabilities(): void
    {
        $response = $this->get('/');

        $response->assertSee('Fitur Unggulan');
        $response->assertSee('Kalender Booking');
        $response->assertSee('Inventaris Cerdas');
        $response->assertSee('Faktur &amp; Denda', false);
        $response->assertSee('Laporan Analitik');
    }

    public function test_pricing_section_displays_three_packages_and_quota_limits(): void
    {
        $response = $this->get('/');

        $response->assertSee('Starter');
        $response->assertSee('Business');
        $response->assertSee('Professional');
        $response->assertSee('3 Bulan');
        $response->assertSee('6 Bulan');
        $response->assertSee('12 Bulan');
        $response->assertSee('Maks. 10 jenis produk');
        $response->assertSee('Maks. 50 unit peralatan total');
        $response->assertSee('Pilih Paket Starter');
        $response->assertSee('Pilih Paket Business');
        $response->assertSee('Pilih Paket Professional');
    }

    public function test_public_footer_displays_legal_links_and_copyright(): void
    {
        $response = $this->get('/');

        $response->assertSee('Syarat &amp; Ketentuan', false);
        $response->assertSee('Kebijakan Privasi');
    }

    public function test_public_layout_supports_responsive_mobile_viewport_and_grid(): void
    {
        $response = $this->get('/');

        $response->assertSee('name="viewport"', false);
        $response->assertSee('width=device-width, initial-scale=1.0', false);
        $response->assertSee('grid-cols-1 md:grid-cols-2 lg:grid-cols-4', false);
    }
}
