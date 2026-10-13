<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientRegistration;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TenantRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_form_is_accessible(): void
    {
        $response = $this->get('/register-tenant');

        $response->assertStatus(200);
        $response->assertSee('Pendaftaran admin rental');
    }

    public function test_setup_tenant_saves_data_and_redirects_to_theme_setup_on_register_tenant(): void
    {
        $this->withoutVite();

        $user = User::create([
            'name' => 'johndoe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin_rental',
            'status' => 'pending_setup',
        ]);

        $response = $this->actingAs($user)->post('/register-tenant', [
            'business_name' => 'Rental Maju Jaya',
            'subdomain' => 'majujaya',
            'description' => 'Toko sewa alat pesta',
            'owner_name' => 'John Doe',
            'phone' => '081234567890',
            'address' => 'Jl. Merdeka No. 10 Jakarta',
            'plan_name' => 'starter',
            'terms' => '1',
        ]);

        // Must redirect to /register-tenant (staying on register-tenant URL)
        $response->assertRedirect('/register-tenant');

        // User must still be authenticated with status pending_setup
        $this->assertAuthenticatedAs($user);
        $user->refresh();
        $this->assertEquals('pending_setup', $user->status);

        // Client created with status menunggu_verifikasi
        $this->assertDatabaseHas('clients', [
            'business_name' => 'Rental Maju Jaya',
            'subdomain' => 'majujaya',
            'status' => 'menunggu_verifikasi',
        ]);

        // Visiting /register-tenant now shows setup-theme
        $themePage = $this->actingAs($user)->get('/register-tenant');
        $themePage->assertStatus(200);
        $themePage->assertSee('Personalisasi toko Anda');

        // Now submit theme setup
        $themeResponse = $this->actingAs($user)->post('/register-tenant', [
            'theme_color' => '#FF9F43',
        ]);

        // Must redirect to /register-tenant (proceeds to payment step)
        $themeResponse->assertRedirect('/register-tenant');

        // User is still authenticated with status pending_setup
        $this->assertAuthenticatedAs($user);
        $user->refresh();
        $this->assertEquals('pending_setup', $user->status);

        // Client theme_color is updated
        $this->assertDatabaseHas('clients', [
            'id' => $user->client_id,
            'theme_color' => '#FF9F43',
        ]);

        // Visiting /register-tenant now shows payment page
        $paymentPage = $this->actingAs($user)->get('/register-tenant');
        $paymentPage->assertStatus(200);
        $paymentPage->assertSee('Konfirmasi Pembayaran');
        $paymentPage->assertSee('Unggah Bukti Pembayaran');

        // Now submit payment proof
        $proofFile = \Illuminate\Http\UploadedFile::fake()->image('bukti_transfer.jpg');
        $paymentResponse = $this->actingAs($user)->post('/register-tenant', [
            'action_step' => 'payment',
            'payment_proof' => $proofFile,
        ]);

        // Must redirect to /register-tenant
        $paymentResponse->assertRedirect('/register-tenant');

        // User is now logged out and status updated to menunggu_verifikasi
        $this->assertGuest();
        $user->refresh();
        $this->assertEquals('menunggu_verifikasi', $user->status);

        // Subscription and ClientRegistration have payment_proof recorded
        $subscription = Subscription::where('client_id', $user->client_id)->first();
        $this->assertNotNull($subscription);
        $this->assertNotNull($subscription->payment_proof);
        $this->assertEquals('menunggu', $subscription->payment_status);
        $this->assertEquals('menunggu_verifikasi', $subscription->status);

        $clientReg = ClientRegistration::where('admin_email', $user->email)->first();
        $this->assertNotNull($clientReg);
        $this->assertNotNull($clientReg->payment_proof);
        $this->assertEquals('menunggu', $clientReg->payment_status);
        $this->assertEquals('menunggu_verifikasi', $clientReg->status);

        // Visiting /register-tenant now shows waiting page
        $waitingPage = $this->get('/register-tenant');
        $waitingPage->assertStatus(200);
        $waitingPage->assertSee('Menunggu Verifikasi');

        // Visiting home page clears waiting state
        $homePage = $this->get('/');
        $homePage->assertStatus(200);

        // Visiting /register-tenant again after home shows the initial registration form
        $freshRegisterPage = $this->get('/register-tenant');
        $freshRegisterPage->assertStatus(200);
        $freshRegisterPage->assertSee('Pendaftaran admin rental');
    }

    public function test_direct_full_registration_redirects_to_login_with_flash_message(): void
    {
        $response = $this->post('/register-tenant', [
            'username' => 'budiutomo',
            'email' => 'budi@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'business_name' => 'Kamera Budi Rental',
            'subdomain' => 'budikamera',
            'description' => 'Sewa kamera profesional',
            'phone' => '081298765432',
            'address' => 'Bandung',
            'plan_name' => 'business',
            'terms' => '1',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('success', 'Pendaftaran berhasil diajukan. Silakan tunggu verifikasi dari Owner. Informasi persetujuan akan dikirimkan melalui email Anda.');

        $this->assertGuest();
        $this->assertDatabaseHas('client_registrations', [
            'subdomain' => 'budikamera',
            'status' => 'menunggu_verifikasi',
        ]);
    }

    public function test_failed_validation_does_not_redirect_to_login_and_preserves_input(): void
    {
        $user = User::create([
            'name' => 'johndoe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin_rental',
            'status' => 'pending_setup',
        ]);

        $response = $this->actingAs($user)->post('/register-tenant', [
            'business_name' => '', // missing required
            'subdomain' => 'majujaya',
            'description' => 'Toko sewa alat pesta',
            'owner_name' => 'John Doe',
            'phone' => '081234567890',
            'address' => 'Jl. Merdeka No. 10 Jakarta',
            'plan_name' => 'starter',
            'terms' => '1',
        ]);

        $response->assertSessionHasErrors(['business_name']);
        $this->assertDatabaseMissing('clients', [
            'subdomain' => 'majujaya',
        ]);
    }

    public function test_login_view_displays_success_flash_message(): void
    {
        $response = $this->withSession([
            'success' => 'Pendaftaran berhasil diajukan. Silakan tunggu verifikasi dari Owner. Informasi persetujuan akan dikirimkan melalui email Anda.',
        ])->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Pendaftaran berhasil diajukan. Silakan tunggu verifikasi dari Owner. Informasi persetujuan akan dikirimkan melalui email Anda.');
    }

    public function test_unverified_user_attempting_login_stays_at_login_with_status_message(): void
    {
        User::create([
            'name' => 'johndoe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin_rental',
            'status' => 'menunggu_verifikasi',
        ]);

        $response = $this->post('/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('info');
        $this->assertGuest();
    }

    public function test_waiting_verification_route_redirects_to_login(): void
    {
        $response = $this->get('/waiting-verification');

        $response->assertRedirect('/login');
    }

    public function test_setup_tenant_saves_logo_to_both_client_and_user_avatar(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $user = User::create([
            'name' => 'johndoe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin_rental',
            'status' => 'pending_setup',
        ]);

        $file = \Illuminate\Http\UploadedFile::fake()->image('logo.png');

        $response = $this->actingAs($user)->post('/register-tenant', [
            'business_name' => 'Rental Kamera Cepat',
            'subdomain' => 'kameracepat',
            'description' => 'Sewa kamera kilat',
            'logo' => $file,
            'owner_name' => 'John Doe',
            'phone' => '081234567890',
            'address' => 'Jl. Sudirman No. 1',
            'plan_name' => 'starter',
            'terms' => '1',
        ]);

        $response->assertRedirect('/register-tenant');

        $client = Client::where('subdomain', 'kameracepat')->first();
        $this->assertNotNull($client);
        $this->assertNotNull($client->logo);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($client->logo);

        $user->refresh();
        $this->assertEquals($client->logo, $user->avatar);
    }

    public function test_direct_registration_saves_logo_to_both_client_and_user_avatar(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $file = \Illuminate\Http\UploadedFile::fake()->image('logo.png');

        $response = $this->post('/register-tenant', [
            'username' => 'suryaadmin',
            'email' => 'surya@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'business_name' => 'Surya Rental',
            'subdomain' => 'suryarental',
            'description' => 'Sewa alat outdoor',
            'logo' => $file,
            'phone' => '081234567891',
            'address' => 'Jl. Pahlawan No. 2',
            'plan_name' => 'starter',
            'terms' => '1',
        ]);

        $response->assertRedirect('/login');

        $client = Client::where('subdomain', 'suryarental')->first();
        $this->assertNotNull($client);
        $this->assertNotNull($client->logo);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($client->logo);

        $user = User::where('email', 'surya@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals($client->logo, $user->avatar);
    }
}
