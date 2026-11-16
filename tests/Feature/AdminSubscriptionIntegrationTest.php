<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * ============================================================================
 * INTEGRATION TEST: IT-MOD-04 (PENGATURAN & OPERASIONAL ADMIN TOKO)
 * ============================================================================
 */
class AdminSubscriptionIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        if (config('database.default') === 'sqlite' && config('database.connections.sqlite.database') === ':memory:') {
            if (! Schema::hasTable('users')) {
                $this->artisan('migrate');
            }
        }
    }

    /**
     * Helper untuk mengambil data Admin Toko Rental (budi@babyrental.com) pada Supabase Staging,
     * atau membuat dummy dalam database transaction jika di lingkungan pengujian terisolasi.
     *
     * @return array{0: Client, 1: User}
     */
    private function resolveTenantAndAdmin(): array
    {
        $admin = User::where('email', 'budi@babyrental.com')->first();

        if ($admin && $admin->client) {
            $client = $admin->client;

            // Pastikan admin budi terdaftar sebagai primary admin toko
            if ((int) $client->primary_admin_id !== (int) $admin->id) {
                $client->update(['primary_admin_id' => $admin->id]);
            }

            return [$client, $admin];
        }

        // Fallback jika database lokal belum memiliki data staging Supabase
        $client = Client::firstOrCreate(
            ['subdomain' => 'babyrental'],
            [
                'business_name' => 'Baby Rental Test',
                'status' => 'aktif',
            ]
        );

        $admin = User::firstOrCreate(
            ['email' => 'budi@babyrental.com'],
            [
                'client_id' => $client->id,
                'name' => 'Budi Baby Rental',
                'password' => bcrypt('password123'),
                'role' => 'admin_rental',
                'status' => 'aktif',
            ]
        );

        $client->update(['primary_admin_id' => $admin->id]);

        return [$client, $admin];
    }

    /**
     * Helper untuk membuat staff admin (bukan primary admin) untuk pengujian authorization boundary.
     */
    private function createStaffAdmin(Client $client): User
    {
        return User::create([
            'client_id' => $client->id,
            'name' => 'Staff Operasional Baby Rental',
            'email' => 'staff.' . uniqid() . '@babyrental.com',
            'password' => bcrypt('password123'),
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);
    }

    // =========================================================================
    // IT-MOD-04 - Item 8: Halaman Monitoring Kuota & Langganan Toko (GET /admin/subscriptions)
    // =========================================================================

    public function test_it_04_008_admin_can_access_quota_and_subscription_monitoring_page(): void
    {
        [$client, $admin] = $this->resolveTenantAndAdmin();

        $response = $this->actingAs($admin)->get(route('admin.subscriptions.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.subscriptions.index');
        $response->assertViewHas([
            'client',
            'subscription',
            'history',
            'usedProducts',
            'usedUnits',
            'usedCategories',
            'usedAdmins',
            'packagesConfig',
            'currentPlanKey',
            'currentPackage',
            'daysRemaining',
            'isExpired',
        ]);
        $response->assertSee('Langganan');
    }

    public function test_it_04_008_guest_cannot_access_subscription_monitoring_page(): void
    {
        $response = $this->get(route('admin.subscriptions.index'));

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHas('error', 'Silakan login terlebih dahulu.');
    }

    public function test_it_04_008_customer_cannot_access_admin_subscription_page(): void
    {
        $customer = User::where('role', 'customer')->first();

        if (! $customer) {
            $customer = User::create([
                'name' => 'Customer Test',
                'email' => 'customer.test.' . uniqid() . '@rentalbase.id',
                'password' => bcrypt('password123'),
                'role' => 'customer',
                'status' => 'aktif',
            ]);
        }

        $response = $this->actingAs($customer)->get(route('admin.subscriptions.index'));

        // Middleware admin me-redirect atau menolak akses selain admin_rental
        $this->assertTrue(in_array($response->getStatusCode(), [302, 403]));
        if ($response->getStatusCode() === 302) {
            $response->assertRedirect('/');
        }
    }
}
