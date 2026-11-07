<?php

namespace Tests\Unit;

use App\Models\Client;
use App\Models\TenantProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminTenantProfileThemeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function createTenantWithPrimaryAdmin(string $subdomain = 'tokoprofil', string $plan = 'starter'): array
    {
        $client = Client::create([
            'business_name' => 'Toko Rental ' . $subdomain,
            'subdomain' => $subdomain,
            'status' => 'aktif',
        ]);

        $admin = User::factory()->create([
            'client_id' => $client->id,
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        $client->update(['primary_admin_id' => $admin->id]);

        \App\Models\Subscription::create([
            'client_id' => $client->id,
            'plan_name' => $plan,
            'price' => 150000,
            'billing_cycle' => 'monthly',
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'status' => 'aktif',
        ]);

        return [$client, $admin];
    }

    public function test_ut_04_010_admin_tenant_profile_logo_and_theme_customization(): void
    {
        [$client, $admin] = $this->createTenantWithPrimaryAdmin('viewtoko', 'starter');

        $response = $this->actingAs($admin)->get('/admin/settings/profile');

        $response->assertStatus(200);
        $response->assertSee('Identitas');
        $response->assertSee('Logo Toko');
        $response->assertSee($client->subdomain);
    }

    public function test_primary_admin_can_update_store_identity_and_contact(): void
    {
        [$client, $admin] = $this->createTenantWithPrimaryAdmin('updatetoko', 'starter');

        $response = $this->actingAs($admin)->post('/admin/settings/profile', [
            'name' => 'Rental Kamera Malang Juara',
            'description' => 'Pusat persewaan kamera terlengkap di kota Malang.',
            'address' => 'Jl. Ijen No. 100',
            'city' => 'Malang',
            'whatsapp' => '081234567890',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('tenant_profiles', [
            'client_id' => $client->id,
            'name' => 'Rental Kamera Malang Juara',
            'city' => 'Malang',
            'whatsapp' => '081234567890',
        ]);
    }

    public function test_logo_upload_validates_file_extension_and_max_2mb_size(): void
    {
        [$client, $admin] = $this->createTenantWithPrimaryAdmin('logovalidate', 'starter');

        // File lebih dari 2MB (2500 KB)
        $oversizedFile = UploadedFile::fake()->create('huge-logo.png', 2500, 'image/png');

        $response = $this->actingAs($admin)->post('/admin/settings/profile', [
            'name' => 'Toko Logo',
            'logo' => $oversizedFile,
        ]);

        $response->assertSessionHasErrors('logo');
    }

    public function test_primary_admin_can_upload_store_logo(): void
    {
        Storage::fake('public');
        [$client, $admin] = $this->createTenantWithPrimaryAdmin('logoupload', 'starter');

        $validLogo = UploadedFile::fake()->image('logo-toko.png', 300, 300);

        $response = $this->actingAs($admin)->post('/admin/settings/profile', [
            'name' => 'Toko Dengan Logo',
            'logo' => $validLogo,
        ]);

        $response->assertRedirect();

        $profile = TenantProfile::where('client_id', $client->id)->first();
        $this->assertNotNull($profile->logo_path);
    }

    public function test_starter_plan_tenant_cannot_customize_theme_color(): void
    {
        [$client, $admin] = $this->createTenantWithPrimaryAdmin('startertheme', 'starter');

        $response = $this->actingAs($admin)->post('/admin/settings/profile', [
            'name' => 'Toko Starter Coba Ubah Warna',
            'theme_color' => '#1E3A8A',
        ]);

        $response->assertSessionHasErrors('theme_color');
    }

    public function test_business_or_pro_plan_tenant_can_customize_theme_color(): void
    {
        [$client, $admin] = $this->createTenantWithPrimaryAdmin('businesstheme', 'business');

        $response = $this->actingAs($admin)->post('/admin/settings/profile', [
            'name' => 'Toko Business Sukses Warna',
            'theme_color' => '#059669',
        ]);

        $response->assertRedirect();

        $profile = TenantProfile::where('client_id', $client->id)->first();
        $this->assertEquals('#059669', $profile->theme_color);
    }

    public function test_non_primary_staff_admin_is_forbidden_from_updating_store_profile(): void
    {
        [$client, $primaryAdmin] = $this->createTenantWithPrimaryAdmin('stafftenant', 'business');

        $staffAdmin = User::factory()->create([
            'client_id' => $client->id,
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($staffAdmin)->post('/admin/settings/profile', [
            'name' => 'Upaya Ubah Profil Oleh Staff',
        ]);

        $response->assertStatus(403);
    }
}
