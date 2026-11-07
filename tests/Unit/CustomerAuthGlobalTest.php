<?php

namespace Tests\Unit;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerAuthGlobalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_login_page_renders_with_login_and_register_tabs(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Masuk');
        $response->assertSee('Ingat Saya');
        $response->assertSee('Lupa password?');
        $response->assertSee('Customer universal');
    }

    public function test_global_customer_registration_creates_account_with_null_client_id(): void
    {
        $response = $this->post('/register', [
            'name' => 'Ahmad Customer',
            'email' => 'ahmad@example.com',
            'phone' => '081234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'ahmad@example.com',
            'name' => 'Ahmad Customer',
            'role' => 'customer',
            'status' => 'aktif',
            'client_id' => null,
        ]);

        $user = User::where('email', 'ahmad@example.com')->first();
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_registration_form_ignores_client_side_role_or_client_id_tampering(): void
    {
        $client = Client::create([
            'business_name' => 'Toko Mitra',
            'subdomain' => 'tokomitra',
            'status' => 'aktif',
        ]);

        $this->post('/register', [
            'name' => 'Hacker Tampering',
            'email' => 'tamper@example.com',
            'phone' => '08999999999',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'owner',
            'status' => 'super_admin',
            'client_id' => $client->id,
        ]);

        $tamperedUser = User::where('email', 'tamper@example.com')->first();
        $this->assertNotNull($tamperedUser);
        $this->assertEquals('customer', $tamperedUser->role);
        $this->assertEquals('aktif', $tamperedUser->status);
        $this->assertNull($tamperedUser->client_id);
    }

    public function test_customer_registration_validates_required_fields_and_unique_email(): void
    {
        User::factory()->create([
            'email' => 'duplicate@example.com',
            'role' => 'customer',
        ]);

        $response = $this->post('/register', [
            'name' => '',
            'email' => 'duplicate@example.com',
            'phone' => '',
            'password' => 'short',
            'password_confirmation' => 'different',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'phone', 'password']);
    }

    public function test_customer_can_login_with_valid_credentials_and_support_remember_me(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'email' => 'login_customer@example.com',
            'password' => bcrypt('secretpassword'),
            'status' => 'aktif',
            'client_id' => null,
        ]);

        $response = $this->post('/login', [
            'email' => 'login_customer@example.com',
            'password' => 'secretpassword',
            'login_type' => 'customer',
            'remember' => true,
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($customer);
    }

    public function test_customer_login_supports_intended_redirect_to_previous_page(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'email' => 'intended@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Simulasi intended redirect ke halaman cart
        $response = $this->withSession(['url.intended' => 'http://localhost/client/babyrent/cart'])
            ->post('/login', [
                'email' => 'intended@example.com',
                'password' => 'password123',
                'login_type' => 'customer',
            ]);

        $response->assertRedirect('http://localhost/client/babyrent/cart');
    }

    public function test_admin_credentials_are_rejected_when_logging_in_via_customer_tab(): void
    {
        $client = Client::create([
            'business_name' => 'Rental Maju',
            'subdomain' => 'rentalmaju',
            'status' => 'aktif',
        ]);

        User::factory()->create([
            'role' => 'admin_rental',
            'client_id' => $client->id,
            'email' => 'admin@rentalmaju.com',
            'password' => bcrypt('adminsecret'),
            'status' => 'aktif',
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@rentalmaju.com',
            'password' => 'adminsecret',
            'login_type' => 'customer',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}

