<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

/**
 * ============================================================================
 * INTEGRATION TEST: IT-MOD-01 (AUTENTIKASI & OTORISASI - LOGOUT MULTI-ROLE)
 * ============================================================================
 */
class MultiRoleLogoutIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Mencegah kegagalan pengujian jika asset Vite belum dikompilasi
        $this->withoutVite();
    }

    /**
     * Helper untuk mengambil data Admin Toko Rental (budi@babyrental.com).
     * Fallback aman ke in-memory instance jika tabel database belum tersedia.
     */
    protected function getAdminRentalUser(): User
    {
        try {
            $user = User::where('email', 'budi@babyrental.com')->first()
                ?? User::where('role', 'admin_rental')->where('status', 'aktif')->first();

            if ($user) {
                return $user;
            }
        } catch (\Throwable $e) {
            // Fallback aman in-memory jika database SQLite testing belum termigrasi
        }

        $user = new User([
            'name' => 'Admin Budi Babyrental',
            'email' => 'budi@babyrental.com',
            'role' => 'admin_rental',
            'status' => 'aktif',
            'client_id' => 1,
        ]);
        $user->id = 9991;
        $user->exists = true;

        return $user;
    }

    /**
     * Helper untuk mengambil data Customer Universal (customer@rentalbase.id).
     * Fallback aman ke in-memory instance jika tabel database belum tersedia.
     */
    protected function getCustomerUser(): User
    {
        try {
            $user = User::where('email', 'customer@rentalbase.id')->first()
                ?? User::where('role', 'customer')->where('status', 'aktif')->first();

            if ($user) {
                return $user;
            }
        } catch (\Throwable $e) {
            // Fallback aman in-memory jika database SQLite testing belum termigrasi
        }

        $user = new User([
            'name' => 'Customer Universal',
            'email' => 'customer@rentalbase.id',
            'role' => 'customer',
            'status' => 'aktif',
            'client_id' => null,
        ]);
        $user->id = 9992;
        $user->exists = true;

        return $user;
    }

    /**
     * Helper untuk mengambil data Owner SaaS (owner@rentalbase.id).
     * Fallback aman ke in-memory instance jika tabel database belum tersedia.
     */
    protected function getOwnerUser(): User
    {
        try {
            $user = User::where('email', 'owner@rentalbase.id')->first()
                ?? User::where('role', 'owner')->where('status', 'aktif')->first();

            if ($user) {
                return $user;
            }
        } catch (\Throwable $e) {
            // Fallback aman in-memory jika database SQLite testing belum termigrasi
        }

        $user = new User([
            'name' => 'Owner SaaS Platform',
            'email' => 'owner@rentalbase.id',
            'role' => 'owner',
            'status' => 'aktif',
            'client_id' => null,
        ]);
        $user->id = 9993;
        $user->exists = true;

        return $user;
    }

    /**
     * IT-MOD-01: Customer Logout dari Katalog Toko diarahkan kembali ke URL Katalog Toko.
     * Perilaku: Saat referer berasal dari katalog (/client/babyrental), user diarahkan kembali ke katalog.
     */
    public function test_it_mod_01_customer_logout_from_catalog_redirects_to_catalog(): void
    {
        $customer = $this->getCustomerUser();
        $catalogUrl = '/client/babyrental';

        $response = $this->actingAs($customer, 'web')
            ->from($catalogUrl)
            ->post(route('logout'));

        $response->assertRedirect($catalogUrl);
        $this->assertGuest('web');
    }

    /**
     * IT-MOD-01: Customer Logout tanpa referer katalog diarahkan ke halaman login umum.
     * Perilaku: Invalidate session, regenerate CSRF token, redirect route('login') dengan flash 'success'.
     */
    public function test_it_mod_01_customer_logout_default_redirects_to_login(): void
    {
        $customer = $this->getCustomerUser();

        $response = $this->actingAs($customer, 'web')
            ->post(route('logout'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success', 'Anda telah berhasil keluar.');
        $this->assertGuest('web');
    }

    /**
     * IT-MOD-01: Admin Toko Rental Logout membersihkan session dan redirect ke homepage ('/').
     */
    public function test_it_mod_01_admin_rental_logout_clears_session_and_redirects_to_home(): void
    {
        $admin = $this->getAdminRentalUser();

        $response = $this->actingAs($admin, 'web')
            ->post(route('admin.logout'));

        $response->assertRedirect('/');
        $this->assertGuest('web');
    }

    /**
     * IT-MOD-01: Admin Toko Rental tidak dapat mengakses dashboard admin setelah logout.
     * Perilaku: Akses ke GET /admin/dashboard setelah logout diblokir oleh AdminMiddleware (302 ke '/').
     */
    public function test_it_mod_01_admin_rental_cannot_access_dashboard_after_logout(): void
    {
        $admin = $this->getAdminRentalUser();

        // 1. Admin melakukan logout
        $logoutResponse = $this->actingAs($admin, 'web')
            ->post(route('admin.logout'));
        $logoutResponse->assertRedirect('/');
        $this->assertGuest('web');

        // 2. Akses kembali ke dashboard admin setelah session invalid
        $dashboardResponse = $this->get(route('admin.dashboard'));

        $dashboardResponse->assertStatus(302);
        $dashboardResponse->assertRedirect('/');
        $dashboardResponse->assertSessionHas('error', 'Silakan login terlebih dahulu.');
    }

    /**
     * IT-MOD-01: Owner SaaS Logout membersihkan session dan redirect ke login owner ('owner.login').
     */
    public function test_it_mod_01_owner_logout_clears_session_and_redirects_to_owner_login(): void
    {
        $owner = $this->getOwnerUser();

        $response = $this->actingAs($owner, 'web')
            ->post(route('owner.logout'));

        $response->assertRedirect(route('owner.login'));
        $response->assertSessionHas('success', 'Anda telah berhasil keluar dari Portal Owner.');
        $this->assertGuest('web');
    }

    /**
     * IT-MOD-01: Owner SaaS tidak dapat mengakses dashboard owner setelah logout.
     * Perilaku: Akses ke GET /owner/dashboard setelah logout diblokir oleh OwnerMiddleware (302 ke 'owner.login').
     */
    public function test_it_mod_01_owner_cannot_access_dashboard_after_logout(): void
    {
        $owner = $this->getOwnerUser();

        // 1. Owner melakukan logout
        $logoutResponse = $this->actingAs($owner, 'web')
            ->post(route('owner.logout'));
        $logoutResponse->assertRedirect(route('owner.login'));
        $this->assertGuest('web');

        // 2. Akses kembali ke dashboard owner setelah session invalid
        $dashboardResponse = $this->get(route('owner.dashboard'));

        $dashboardResponse->assertStatus(302);
        $dashboardResponse->assertRedirect(route('owner.login'));
        $dashboardResponse->assertSessionHas('error', 'Silakan masuk terlebih dahulu untuk mengakses Portal Owner.');
    }

    /**
     * IT-MOD-01: Guest / Unauthenticated user tidak dapat memicu logout Admin atau Owner secara langsung.
     * Perilaku:
     *   - POST /admin/logout diblokir oleh AdminMiddleware (302 ke '/')
     *   - POST /owner/logout diblokir oleh OwnerMiddleware (302 ke 'owner.login')
     */
    public function test_it_mod_01_guest_cannot_trigger_admin_or_owner_logout(): void
    {
        // 1. Guest mencoba logout admin
        $adminLogoutResponse = $this->post(route('admin.logout'));
        $adminLogoutResponse->assertStatus(302);
        $adminLogoutResponse->assertRedirect('/');
        $adminLogoutResponse->assertSessionHas('error', 'Silakan login terlebih dahulu.');

        // 2. Guest mencoba logout owner
        $ownerLogoutResponse = $this->post(route('owner.logout'));
        $ownerLogoutResponse->assertStatus(302);
        $ownerLogoutResponse->assertRedirect(route('owner.login'));
        $ownerLogoutResponse->assertSessionHas('error', 'Silakan masuk terlebih dahulu untuk mengakses Portal Owner.');

        // 3. Pastikan status auth tetap guest
        $this->assertGuest('web');
    }
}
