<?php

namespace Tests\Feature\Customer;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerAuthRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_catalog_navbar_register_button_points_to_customer_register()
    {
        $client = Client::create([
            'business_name' => 'Kamera Malang Rental',
            'description' => 'Sewa kamera & lensa profesional termurah di Malang.',
            'subdomain' => 'kamerarental',
            'status' => 'aktif',
        ]);

        $response = $this->get('/client/kamerarental');

        $response->assertStatus(200);
        // Pastikan tidak lagi mengarah ke /register-tenant
        $response->assertDontSee('/register-tenant');
        // Pastikan tombol Daftar mengarah ke tab register
        $response->assertSee('tab=register');
    }

    public function test_register_route_redirects_to_login_tab_register_with_query_params()
    {
        $response = $this->get('/register?redirect=http://localhost/client/kamerarental');

        $response->assertRedirect('/login?tab=register&redirect=http%3A%2F%2Flocalhost%2Fclient%2Fkamerarental');
    }

    public function test_customer_can_register_and_is_redirected_to_provided_redirect_url()
    {
        $client = Client::create([
            'business_name' => 'Baby Rental',
            'description' => 'Sewa perlengkapan bayi.',
            'subdomain' => 'babyrental',
            'status' => 'aktif',
        ]);

        $response = $this->post('/register', [
            'name' => 'Budi Pelanggan',
            'email' => 'budi@gmail.com',
            'phone' => '081234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'redirect' => 'http://localhost/client/babyrental',
        ]);

        $response->assertRedirect('http://localhost/client/babyrental');
        $this->assertAuthenticated();

        $user = User::where('email', 'budi@gmail.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('customer', $user->role);
        $this->assertEquals('aktif', $user->status);
    }

    public function test_landing_page_renders_role_modal_with_customer_and_tenant_options()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('showRoleModal');
        $response->assertSee('Pilih Jenis Pendaftaran');
        $response->assertSee(route('login', ['tab' => 'register']));
        $response->assertSee(route('tenant.register'));
    }

    public function test_landing_page_pricing_buttons_point_to_tenant_register()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee(route('tenant.register', ['plan' => 'starter']));
        $response->assertSee(route('tenant.register', ['plan' => 'business']));
        $response->assertSee(route('tenant.register', ['plan' => 'professional']));
    }

    public function test_tenant_registration_stores_selected_plan_in_session()
    {
        $response = $this->post('/register-tenant', [
            'username' => 'newadmin',
            'email' => 'newadmin@rental.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'plan' => 'business',
        ]);

        $response->assertRedirect('/register-tenant');
        $this->assertEquals('business', session('registration_plan'));
    }

    public function test_user_abandoning_tenant_step_1_can_open_customer_register_form_without_being_redirected()
    {
        // 1. User melakukan pendaftaran tenant tahap 1 (Pendekatan 1: simpan di session, belum ke DB)
        $this->post('/register-tenant', [
            'username' => 'calontenant',
            'email' => 'calon@rental.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // User tetap guest, data belum masuk database users
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'calon@rental.com']);
        $this->assertTrue(session()->has('tenant_registration.account'));

        // 2. User kembali ke landing page dan memilih daftar sebagai customer (/login?tab=register)
        $response = $this->get('/login?tab=register');

        // Pastikan form registrasi customer dapat dibuka normal tanpa ter-redirect/mental
        $response->assertStatus(200);
        $response->assertSee('Daftar Akun Baru');
        $response->assertSee('Buat akun universal untuk bertransaksi di semua toko');
        $this->assertGuest();

        // 3. User mendaftar sebagai customer (bahkan bisa memakai email yang sama karena belum tersimpan di DB)
        $registerCustomerResponse = $this->post('/register', [
            'name' => 'Calon Tenant Jadi Customer',
            'email' => 'calon@rental.com',
            'phone' => '089876543210',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $registerCustomerResponse->assertRedirect('/client/babyrental');
        $this->assertAuthenticated();
        $this->assertEquals('customer', auth()->user()->role);
        $this->assertEquals('aktif', auth()->user()->status);
        $this->assertEquals('calon@rental.com', auth()->user()->email);
    }

    public function test_tenant_step_1_followed_by_step_2_creates_user_and_client_in_db()
    {
        $this->withoutVite();

        // 1. Tahap 1: Simpan di session
        $this->post('/register-tenant', [
            'username' => 'petualang',
            'email' => 'petualang@outdoor.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'plan' => 'business',
        ])->assertRedirect('/register-tenant');

        $this->assertGuest();
        $this->assertTrue(session()->has('tenant_registration.account'));

        // 2. Tahap 2: Kirim Info Toko
        $response = $this->post('/register-tenant', [
            'business_name' => 'Outdoor Adventure',
            'subdomain' => 'outdooradv',
            'description' => 'Sewa alat gunung dan camping terlengkap',
            'owner_name' => 'Budi Petualang',
            'phone' => '081234567899',
            'address' => 'Jl. Rinjani No. 5',
            'plan_name' => 'business',
            'terms' => '1',
        ]);

        $response->assertRedirect('/register-tenant');
        $this->assertAuthenticated();

        // Verifikasi User dibuat di database
        $this->assertDatabaseHas('users', [
            'email' => 'petualang@outdoor.com',
            'name' => 'Budi Petualang',
            'status' => 'pending_setup',
        ]);

        // Verifikasi Client dibuat di database
        $this->assertDatabaseHas('clients', [
            'business_name' => 'Outdoor Adventure',
            'subdomain' => 'outdooradv',
            'status' => 'menunggu_verifikasi',
        ]);

        // Verifikasi Subscription dibuat
        $this->assertDatabaseHas('subscriptions', [
            'plan_name' => 'business',
            'status' => 'menunggu_verifikasi',
        ]);
    }
}

