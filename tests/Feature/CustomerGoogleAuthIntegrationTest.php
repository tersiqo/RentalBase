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
        $socialiteUser = new SocialiteUser();
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

        $activeSocialiteUser = new SocialiteUser();
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
}
