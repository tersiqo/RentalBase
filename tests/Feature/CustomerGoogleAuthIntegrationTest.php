<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class CustomerGoogleAuthIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_01_028_google_oauth_customer_login_and_redirect_flow(): void
    {
        $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $provider->shouldReceive('redirect')
            ->once()
            ->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));

        Socialite::shouldReceive('driver')
            ->with('google')
            ->once()
            ->andReturn($provider);

        $response = $this->get(route('auth.google'));

        $response->assertRedirect('https://accounts.google.com/o/oauth2/auth');
    }

    public function test_it_01_029_google_oauth_callback_creates_user_with_pending_setup_and_active_admin(): void
    {
        // 1. Kasus Pengguna Baru (Pending Setup)
        $socialiteUser = new SocialiteUser;
        $socialiteUser->id = 'google-id-12345';
        $socialiteUser->name = 'Budi Santoso';
        $socialiteUser->email = 'budi.santoso@example.com';

        $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $provider->shouldReceive('user')
            ->once()
            ->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')
            ->with('google')
            ->once()
            ->andReturn($provider);

        $response = $this->get('/auth/google/callback');

        $this->assertDatabaseHas('users', [
            'email' => 'budi.santoso@example.com',
            'status' => 'pending_setup',
            'role' => 'admin_rental',
        ]);

        $response->assertRedirect(route('tenant.register'));
        $this->assertAuthenticated();

        // 2. Kasus Pengguna Aktif (Login Langsung ke Dashboard)
        $activeUser = User::factory()->create([
            'email' => 'active.admin@rentalbase.com',
            'status' => 'aktif',
            'role' => 'admin_rental',
        ]);

        $activeSocialiteUser = new SocialiteUser;
        $activeSocialiteUser->id = 'google-id-999';
        $activeSocialiteUser->name = 'Active Admin';
        $activeSocialiteUser->email = 'active.admin@rentalbase.com';

        $activeProvider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $activeProvider->shouldReceive('user')
            ->once()
            ->andReturn($activeSocialiteUser);

        Socialite::shouldReceive('driver')
            ->with('google')
            ->once()
            ->andReturn($activeProvider);

        $activeResponse = $this->get('/auth/google/callback');

        $activeResponse->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($activeUser);
    }

    public function test_google_oauth_callback_handles_failure_gracefully(): void
    {
        $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $provider->shouldReceive('user')
            ->once()
            ->andThrow(new \Exception('Google OAuth Failed'));

        Socialite::shouldReceive('driver')
            ->with('google')
            ->once()
            ->andReturn($provider);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_google_oauth_customer_registration_creates_active_customer_and_redirects_not_to_setup_tenant(): void
    {
        $socialiteUser = new SocialiteUser;
        $socialiteUser->id = 'google-cust-001';
        $socialiteUser->name = 'Customer Baru';
        $socialiteUser->email = 'customer.baru@example.com';

        $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $provider->shouldReceive('user')
            ->once()
            ->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')
            ->with('google')
            ->once()
            ->andReturn($provider);

        // Simulasi customer yang mengklik Daftar/Masuk dengan Google dari tab customer
        $response = $this->withSession(['google_auth_role' => 'customer'])
            ->get('/auth/google/callback');

        $this->assertDatabaseHas('users', [
            'email' => 'customer.baru@example.com',
            'role' => 'customer',
            'status' => 'aktif',
            'client_id' => null,
        ]);

        // Customer TIDAK boleh diarahkan ke tenant.register (Setup Toko)
        $response->assertRedirect('/#mitra-rental');
        $this->assertAuthenticated();

        $user = User::where('email', 'customer.baru@example.com')->first();
        $this->assertEquals('customer', $user->role);
        $this->assertEquals('aktif', $user->status);
    }

    public function test_google_oauth_existing_customer_login_redirects_to_store_or_redirect_url(): void
    {
        $customer = User::factory()->create([
            'email' => 'pelanggan.lama@example.com',
            'name' => 'Pelanggan Setia',
            'role' => 'customer',
            'status' => 'aktif',
            'client_id' => null,
        ]);

        $socialiteUser = new SocialiteUser;
        $socialiteUser->id = 'google-cust-002';
        $socialiteUser->name = 'Pelanggan Setia';
        $socialiteUser->email = 'pelanggan.lama@example.com';

        $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $provider->shouldReceive('user')
            ->once()
            ->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')
            ->with('google')
            ->once()
            ->andReturn($provider);

        $targetUrl = 'http://localhost/client/rental-kamera/cart';

        $response = $this->withSession([
            'google_auth_role' => 'customer',
            'google_auth_redirect' => $targetUrl,
        ])->get('/auth/google/callback');

        // Harus diarahkan ke halaman redirect / toko, BUKAN setup toko
        $response->assertRedirect($targetUrl);
        $this->assertAuthenticatedAs($customer);
    }

    public function test_customer_register_tab_renders_google_register_button(): void
    {
        $response = $this->get('/login?tab=register');

        $response->assertStatus(200);
        $response->assertSee('Daftar dengan Google');
        $response->assertSee(route('auth.google'));
    }

    public function test_admin_register_page_renders_footer_security_badge_and_back_link(): void
    {
        $response = $this->get(route('tenant.register'));

        $response->assertStatus(200);
        $response->assertSee('Kembali ke Beranda');
        $response->assertSee('Enkripsi SSL 256-bit Multi-Tenant');
    }

    public function test_already_logged_in_customer_clicking_google_redirect_clears_old_session_and_initiates_oauth(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'status' => 'aktif',
            'email' => 'customer.logged@example.com',
        ]);

        $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $provider->shouldReceive('redirect')
            ->once()
            ->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));

        Socialite::shouldReceive('driver')
            ->with('google')
            ->once()
            ->andReturn($provider);

        $response = $this->actingAs($customer)
            ->get(route('auth.google'));

        $this->assertGuest();
        $response->assertRedirect('https://accounts.google.com/o/oauth2/auth');
    }
}
