<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Client;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantSuspendedInterceptorIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_it_02_003_suspended_tenant_catalog_interceptor_and_isolation(): void
    {
        $suspendedTenant = Client::create([
            'business_name' => 'Toko Rental Suspended',
            'subdomain' => 'tokosuspend',
            'status' => 'suspended',
        ]);

        $response = $this->get("/client/{$suspendedTenant->subdomain}");

        $response->assertStatus(503);
        $response->assertViewIs('errors.tenant-suspended');
        $response->assertSee('Toko Sewa Sedang Ditangguhkan');
        $response->assertSee('Toko Rental Suspended');
    }

    public function test_suspended_tenant_product_detail_and_checkout_are_completely_blocked(): void
    {
        $suspendedTenant = Client::create([
            'business_name' => 'Toko Rental Freeze',
            'subdomain' => 'tokofreeze',
            'status' => 'suspended',
        ]);

        $category = Category::create([
            'client_id' => $suspendedTenant->id,
            'name' => 'Kamera',
            'slug' => 'kamera',
        ]);

        $product = Product::create([
            'client_id' => $suspendedTenant->id,
            'category_id' => $category->id,
            'name' => 'Kamera Freeze',
            'slug' => 'kamera-freeze',
            'rental_price_per_day' => 100000,
            'status' => 'aktif',
        ]);

        $customer = User::factory()->create(['role' => 'customer']);

        // 1. Blokir rincian produk toko suspended
        $responseProduct = $this->actingAs($customer)->get("/client/{$suspendedTenant->subdomain}/products/{$product->id}");
        $responseProduct->assertStatus(503);
        $responseProduct->assertViewIs('errors.tenant-suspended');

        // 2. Blokir checkout toko suspended
        $responseCheckout = $this->actingAs($customer)->get("/client/{$suspendedTenant->subdomain}/checkout");
        $responseCheckout->assertStatus(503);
    }

    public function test_suspended_page_displays_locked_storefront_headline_and_safe_home_cta(): void
    {
        $suspendedTenant = Client::create([
            'business_name' => 'Rental Bahagia Suspended',
            'subdomain' => 'bahagiasuspend',
            'status' => 'suspended',
        ]);

        $response = $this->get("/client/{$suspendedTenant->subdomain}");

        $response->assertStatus(503);
        $response->assertSee('Toko Sewa Sedang Ditangguhkan');
        $response->assertSee('Kembali ke Beranda RentalBase');
        $response->assertSee('Hubungi Bantuan');
        $response->assertSee('href="/"', false);
        $response->assertSee('mailto:support@rentalbase.id', false);
    }

    public function test_multi_tenant_isolation_keeps_active_tenants_fully_accessible_when_another_is_suspended(): void
    {
        $tenantSuspended = Client::create([
            'business_name' => 'Toko A Suspended',
            'subdomain' => 'tokoa-suspended',
            'status' => 'suspended',
        ]);

        $tenantActive = Client::create([
            'business_name' => 'Toko B Aktif Normal',
            'subdomain' => 'tokob-aktif',
            'status' => 'aktif',
        ]);

        // Toko A suspended terblokir 503
        $responseA = $this->get("/client/{$tenantSuspended->subdomain}");
        $responseA->assertStatus(503);

        // Toko B aktif tetap dapat diakses normal 200 OK
        $responseB = $this->get("/client/{$tenantActive->subdomain}");
        $responseB->assertStatus(200);
        $responseB->assertSee('Toko B Aktif Normal');
    }

    public function test_saas_landing_page_remains_operational_regardless_of_tenant_suspension(): void
    {
        Client::create([
            'business_name' => 'Toko X Suspended',
            'subdomain' => 'tokox-suspended',
            'status' => 'suspended',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('RentalBase');
    }

    public function test_non_active_status_tenants_are_consistently_blocked_from_public_catalog(): void
    {
        $tenantNonaktif = Client::create([
            'business_name' => 'Toko Nonaktif',
            'subdomain' => 'tokononaktif',
            'status' => 'nonaktif',
        ]);

        $response = $this->get("/client/{$tenantNonaktif->subdomain}");

        $response->assertStatus(503);
        $response->assertViewIs('errors.tenant-suspended');
    }
}

