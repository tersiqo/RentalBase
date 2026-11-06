<?php

namespace Tests\Feature\Owner;

use App\Models\Client;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OwnerTenantTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $adminRental;
    private User $customer;
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        // 1. Akun Super Admin / Owner Platform
        $this->owner = User::create([
            'name' => 'Owner Platform',
            'email' => 'owner@rentalbase.id',
            'password' => Hash::make('password123'),
            'role' => 'owner',
            'status' => 'aktif',
        ]);

        // 2. Akun Tenant & Admin Rental
        $this->client = Client::create([
            'business_name' => 'Bintang Kamera Rental',
            'subdomain' => 'bintangkamera',
            'description' => 'Sewa perlengkapan foto dan video',
            'status' => 'aktif',
            'theme_color' => '#F97316',
        ]);

        $this->adminRental = User::create([
            'client_id' => $this->client->id,
            'name' => 'Bintang Admin',
            'email' => 'admin@bintangkamera.test',
            'password' => Hash::make('password123'),
            'role' => 'admin_rental',
            'status' => 'aktif',
            'phone' => '081234567890',
            'address' => 'Jl. Merdeka No. 12 Jakarta',
        ]);

        Subscription::create([
            'client_id' => $this->client->id,
            'plan_name' => 'starter',
            'price' => 0,
            'billing_cycle' => 'monthly',
            'start_date' => Carbon::now()->subDays(10),
            'end_date' => Carbon::now()->addDays(20),
            'status' => 'aktif',
        ]);

        // 3. Akun Customer
        $this->customer = User::create([
            'name' => 'Customer Budi',
            'email' => 'budi@gmail.test',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'status' => 'aktif',
        ]);
    }

    // ==========================================
    // A. OTORISASI & PROTEKSI ROLE (401 / 403 / REDIRECT)
    // ==========================================

    public function test_unauthenticated_user_cannot_access_owner_tenants_page(): void
    {
        $response = $this->get(route('owner.tenants.index'));

        $response->assertRedirect(route('owner.login'));
    }

    public function test_admin_rental_cannot_access_owner_tenants_page(): void
    {
        $response = $this->actingAs($this->adminRental)->get(route('owner.tenants.index'));

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_customer_cannot_access_owner_tenants_page(): void
    {
        $response = $this->actingAs($this->customer)->get(route('owner.tenants.index'));

        $response->assertRedirect(route('owner.login'));
        $response->assertSessionHas('error');
    }

    // ==========================================
    // B. SKENARIO POSITIF (INDEX, FILTER, SEARCH)
    // ==========================================

    public function test_owner_can_view_tenants_list_with_filters_and_search(): void
    {
        // Tenant kedua untuk pengujian filter & pencarian
        $client2 = Client::create([
            'business_name' => 'Outdoor Camp Malang',
            'subdomain' => 'campmalang',
            'status' => 'suspended',
        ]);

        Subscription::create([
            'client_id' => $client2->id,
            'plan_name' => 'business',
            'price' => 99000,
            'billing_cycle' => 'monthly',
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->addMonth(),
            'status' => 'aktif',
        ]);

        // 1. Tampilkan semua dengan status aktif
        $response = $this->actingAs($this->owner)->get(route('owner.tenants.index', ['status' => 'aktif']));
        $response->assertStatus(200);
        $response->assertViewIs('owner.tenants.index');
        $response->assertSee('Bintang Kamera Rental');
        $response->assertDontSee('Outdoor Camp Malang');

        // 2. Filter status suspended
        $responseSuspended = $this->actingAs($this->owner)->get(route('owner.tenants.index', ['status' => 'suspended']));
        $responseSuspended->assertStatus(200);
        $responseSuspended->assertSee('Outdoor Camp Malang');
        $responseSuspended->assertDontSee('Bintang Kamera Rental');

        // 3. Pencarian nama usaha
        $responseSearch = $this->actingAs($this->owner)->get(route('owner.tenants.index', [
            'status' => 'all',
            'search' => 'Bintang',
        ]));
        $responseSearch->assertStatus(200);
        $responseSearch->assertSee('Bintang Kamera Rental');
        $responseSearch->assertDontSee('Outdoor Camp Malang');
    }

    // ==========================================
    // C. UPDATE PROFIL TENANT & VALIDASI GAGAL
    // ==========================================

    public function test_owner_can_update_tenant_profile_information(): void
    {
        $payload = [
            'business_name' => 'Bintang Kamera Rental Perkasa',
            'subdomain' => 'bintangkameraperkasa',
            'description' => 'Rental kamera terlengkap di ibukota',
            'theme_color' => '#10B981',
            'admin_name' => 'Bintang Setiawan',
            'admin_email' => 'bintang.setiawan@bintangkamera.test',
            'admin_phone' => '081299998888',
            'admin_address' => 'Jl. Sudirman Kav 20, Jakarta',
        ];

        $response = $this->actingAs($this->owner)->put(route('owner.tenants.update', $this->client->id), $payload);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('clients', [
            'id' => $this->client->id,
            'business_name' => 'Bintang Kamera Rental Perkasa',
            'subdomain' => 'bintangkameraperkasa',
            'theme_color' => '#10B981',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->adminRental->id,
            'name' => 'Bintang Setiawan',
            'email' => 'bintang.setiawan@bintangkamera.test',
            'phone' => '081299998888',
        ]);
    }

    public function test_owner_update_tenant_fails_with_duplicate_subdomain(): void
    {
        Client::create([
            'business_name' => 'Existing Shop',
            'subdomain' => 'existingshop',
            'status' => 'aktif',
        ]);

        $payload = [
            'business_name' => 'Bintang Kamera Rental',
            'subdomain' => 'existingshop', // Duplicate!
            'admin_name' => 'Bintang Admin',
            'admin_email' => 'admin@bintangkamera.test',
            'admin_phone' => '081234567890',
        ];

        $response = $this->actingAs($this->owner)->put(route('owner.tenants.update', $this->client->id), $payload);

        $response->assertSessionHasErrors(['subdomain']);
        $this->client->refresh();
        $this->assertEquals('bintangkamera', $this->client->subdomain);
    }

    public function test_owner_update_tenant_fails_with_reserved_subdomain(): void
    {
        $payload = [
            'business_name' => 'Bintang Kamera Rental',
            'subdomain' => 'admin', // Reserved!
            'admin_name' => 'Bintang Admin',
            'admin_email' => 'admin@bintangkamera.test',
            'admin_phone' => '081234567890',
        ];

        $response = $this->actingAs($this->owner)->put(route('owner.tenants.update', $this->client->id), $payload);

        $response->assertSessionHasErrors(['subdomain']);
    }

    // ==========================================
    // D. UPDATE STATUS TENANT & SINKRONISASI USER
    // ==========================================

    public function test_owner_can_update_tenant_status_and_sync_admin_status(): void
    {
        // 1. Ubah ke suspended
        $response = $this->actingAs($this->owner)->post(route('owner.tenants.status', $this->client->id), [
            'status' => 'suspended',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->client->refresh();
        $this->adminRental->refresh();
        $this->assertEquals('suspended', $this->client->status);
        $this->assertEquals('suspended', $this->adminRental->status);

        // 2. Ubah kembali ke aktif
        $response2 = $this->actingAs($this->owner)->post(route('owner.tenants.status', $this->client->id), [
            'status' => 'aktif',
        ]);

        $response2->assertRedirect();
        $this->client->refresh();
        $this->adminRental->refresh();
        $this->assertEquals('aktif', $this->client->status);
        $this->assertEquals('aktif', $this->adminRental->status);
    }

    public function test_owner_update_status_fails_with_invalid_status_value(): void
    {
        $response = $this->actingAs($this->owner)->post(route('owner.tenants.status', $this->client->id), [
            'status' => 'invalid_status_123',
        ]);

        $response->assertSessionHasErrors(['status']);
    }

    // ==========================================
    // E. UPDATE SUBSCRIPTION TENANT
    // ==========================================

    public function test_owner_can_update_tenant_subscription(): void
    {
        $newEndDate = Carbon::now()->addMonths(6)->format('Y-m-d');

        $response = $this->actingAs($this->owner)->put(route('owner.tenants.subscription', $this->client->id), [
            'plan_name' => 'business',
            'end_date' => $newEndDate,
            'billing_cycle' => 'monthly',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('subscriptions', [
            'client_id' => $this->client->id,
            'plan_name' => 'business',
            'status' => 'aktif',
            'payment_status' => 'diverifikasi',
        ]);
    }

    public function test_owner_update_subscription_fails_with_invalid_plan(): void
    {
        $response = $this->actingAs($this->owner)->put(route('owner.tenants.subscription', $this->client->id), [
            'plan_name' => 'paket_khayalan',
            'end_date' => Carbon::now()->addMonth()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors(['plan_name']);
    }
}

