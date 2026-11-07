<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_ut_01_010_password_reset_workflow_and_token_security(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
        $response->assertSee('Lupa Password?');
        $response->assertSee('Kirim Link Reset');
        $response->assertSee('Kembali ke Login');
    }

    public function test_requesting_reset_link_validates_required_email(): void
    {
        $response = $this->post('/forgot-password', [
            'email' => '',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_password_reset_link_is_dispatched_for_registered_user(): void
    {
        $user = User::factory()->create([
            'email' => 'customer_reset@example.com',
            'role' => 'customer',
        ]);

        $response = $this->post('/forgot-password', [
            'email' => 'customer_reset@example.com',
        ]);

        $response->assertSessionHas('success');
    }

    public function test_reset_password_page_renders_with_token_and_email(): void
    {
        $response = $this->get('/reset-password/sample-security-token?email=user@example.com');

        $response->assertStatus(200);
        $response->assertSee('Reset Password Baru');
        $response->assertSee('sample-security-token');
        $response->assertSee('user@example.com');
        $response->assertSee('Password Baru');
        $response->assertSee('Konfirmasi Password Baru');
    }

    public function test_user_can_reset_password_with_valid_token_and_confirmation(): void
    {
        $user = User::factory()->create([
            'email' => 'reset_success@example.com',
            'password' => bcrypt('oldpassword123'),
            'role' => 'customer',
        ]);

        $token = Password::createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => 'reset_success@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    public function test_password_reset_fails_with_invalid_token_or_unconfirmed_password(): void
    {
        $user = User::factory()->create([
            'email' => 'failed_reset@example.com',
            'password' => bcrypt('original123'),
            'role' => 'customer',
        ]);

        // 1. Password confirmation tidak cocok
        $responseMismatch = $this->post('/reset-password', [
            'token' => 'dummy-token',
            'email' => 'failed_reset@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'mismatched123',
        ]);
        $responseMismatch->assertSessionHasErrors('password');

        // 2. Token tidak valid
        $responseInvalidToken = $this->post('/reset-password', [
            'token' => 'invalid-token-12345',
            'email' => 'failed_reset@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);
        $responseInvalidToken->assertSessionHasErrors('email');
    }
}

