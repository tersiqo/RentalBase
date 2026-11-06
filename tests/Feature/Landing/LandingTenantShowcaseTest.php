<?php

namespace Tests\Feature\Landing;

use App\Models\Category;
use App\Models\Client;
use App\Models\Product;
use App\Models\TenantProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingTenantShowcaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_displays_active_tenants_and_product_counts(): void
    {
        // Active Tenant 1 (Baby Rental)
        $clientA = Client::create([
            'business_name' => 'Baby Rental Studio',
            'subdomain' => 'babyrental',
            'status' => 'aktif',
            'description' => 'Sewa stroller dan box bayi murah di Surabaya.',
        ]);

        TenantProfile::create([
            'client_id' => $clientA->id,
            'name' => 'Baby Rental Studio',
            'subdomain' => 'babyrental',
            'description' => 'Sewa stroller dan box bayi murah di Surabaya.',
            'city' => 'Surabaya',
            'theme_color' => '#F97316',
        ]);

        $categoryA = Category::create([
            'client_id' => $clientA->id,
            'name' => 'Stroller',
            'slug' => 'stroller',
        ]);

        Product::create([
            'client_id' => $clientA->id,
            'category_id' => $categoryA->id,
            'name' => 'Stroller Cocolatte',
            'slug' => 'stroller-cocolatte',
            'rental_price_per_day' => 45000,
            'status' => 'aktif',
        ]);

        Product::create([
            'client_id' => $clientA->id,
            'category_id' => $categoryA->id,
            'name' => 'Stroller Joie',
            'slug' => 'stroller-joie',
            'rental_price_per_day' => 60000,
            'status' => 'aktif',
        ]);

        // Inactive product on Tenant 1 (should not be counted in products_count)
        Product::create([
            'client_id' => $clientA->id,
            'category_id' => $categoryA->id,
            'name' => 'Stroller Rusak',
            'slug' => 'stroller-rusak',
            'rental_price_per_day' => 20000,
            'status' => 'nonaktif',
        ]);

        // Active Tenant 2 (Kamera Malang)
        $clientB = Client::create([
            'business_name' => 'Kamera Malang Rental',
            'subdomain' => 'kamerarental',
            'status' => 'aktif',
            'description' => 'Sewa kamera DSLR dan mirrorless Malang.',
        ]);

        TenantProfile::create([
            'client_id' => $clientB->id,
            'name' => 'Kamera Malang Rental',
            'subdomain' => 'kamerarental',
            'city' => 'Malang',
            'theme_color' => '#3B82F6',
        ]);

        // Inactive / Suspended Tenant 3 (Must not be shown)
        Client::create([
            'business_name' => 'Toko Nonaktif Rental',
            'subdomain' => 'tokononaktif',
            'status' => 'nonaktif',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);

        // Navbar & Section
        $response->assertSee('Mitra Rental');
        $response->assertSee('Jelajahi Toko Mitra yang Telah Bergabung');
        $response->assertSee('Ekosistem RentalBase');

        // Active Tenant 1 assertions
        $response->assertSee('Baby Rental Studio');
        $response->assertSee('babyrental.rentalbase.id');
        $response->assertSee('Surabaya');
        $response->assertSee('2'); // 2 active products count
        $response->assertSee(route('customer.home', ['subdomain' => 'babyrental']));

        // Active Tenant 2 assertions
        $response->assertSee('Kamera Malang Rental');
        $response->assertSee('kamerarental.rentalbase.id');
        $response->assertSee('Malang');
        $response->assertSee(route('customer.home', ['subdomain' => 'kamerarental']));

        // Inactive Tenant assertions (Must NOT appear)
        $response->assertDontSee('Toko Nonaktif Rental');
        $response->assertDontSee('tokononaktif.rentalbase.id');

        // Search & Filter UI elements
        $response->assertSee('x-model="search"', false);
        $response->assertSee('Cari nama rental, kota, atau jenis alat');
        $response->assertSee('Filter Cepat:');
        $response->assertSee('Toko Tidak Ditemukan');
        $response->assertSee('Reset Pencarian');
    }

    public function test_landing_page_shows_empty_state_when_no_active_tenants(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Belum Ada Toko Terdaftar');
        $response->assertSee('Daftar Jadi Tenant Sekarang');
    }
}
