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

    // =========================================================================
    // IT-MOD-04 - Item 9: Admin Toko Request Perpanjang + Upload Bukti Bayar (POST /admin/subscriptions/renew)
    // =========================================================================

    public function test_it_04_009_primary_admin_can_request_subscription_renewal_with_payment_proof(): void
    {
        Storage::fake(config('filesystems.default', 'public'));
        [$client, $admin] = $this->resolveTenantAndAdmin();

        // Hindari throttling 60 detik duplikasi dengan meng-age out created_at jika ada pending sebelumnya
        Subscription::where('client_id', $client->id)
            ->where('status', 'menunggu_konfirmasi')
            ->where('created_at', '>=', now()->subSeconds(60))
            ->update(['created_at' => now()->subMinutes(5)]);

        $file = UploadedFile::fake()->create('bukti_transfer_renew.jpg', 800, 'image/jpeg');

        $payload = [
            'payment_proof' => $file,
            'notes' => 'Perpanjangan paket toko untuk periode berikutnya via Transfer Bank BCA.',
        ];

        $response = $this->actingAs($admin)->post(route('admin.subscriptions.renew'), $payload);

        $response->assertRedirect(route('admin.subscriptions.index'));
        $response->assertSessionHas('success');

        // Verifikasi record subscription baru tersimpan di database
        $this->assertDatabaseHas('subscriptions', [
            'client_id' => $client->id,
            'status' => 'menunggu_konfirmasi',
            'payment_status' => 'menunggu',
            'notes' => 'Perpanjangan paket toko untuk periode berikutnya via Transfer Bank BCA.',
        ]);

        // Verifikasi pencatatan Activity Log audit
        $this->assertDatabaseHas('activity_logs', [
            'client_id' => $client->id,
            'user_id' => $admin->id,
            'action' => 'subscription_renewal_requested',
            'module' => 'subscriptions',
        ]);
    }

    public function test_it_04_009_request_renew_validation_fails_without_payment_proof(): void
    {
        [$client, $admin] = $this->resolveTenantAndAdmin();

        $response = $this->actingAs($admin)
            ->from(route('admin.subscriptions.index'))
            ->post(route('admin.subscriptions.renew'), [
                'notes' => 'Mencoba perpanjang tanpa bukti bayar',
            ]);

        $response->assertRedirect(route('admin.subscriptions.index'));
        $response->assertSessionHasErrors(['payment_proof']);
    }

    public function test_it_04_009_request_renew_validation_fails_with_invalid_file_extension(): void
    {
        Storage::fake(config('filesystems.default', 'public'));
        [$client, $admin] = $this->resolveTenantAndAdmin();

        $invalidFile = UploadedFile::fake()->create('script_malicious.exe', 500, 'application/octet-stream');

        $response = $this->actingAs($admin)
            ->from(route('admin.subscriptions.index'))
            ->post(route('admin.subscriptions.renew'), [
                'payment_proof' => $invalidFile,
            ]);

        $response->assertRedirect(route('admin.subscriptions.index'));
        $response->assertSessionHasErrors(['payment_proof']);
    }

    public function test_it_04_009_staff_admin_cannot_request_subscription_renewal(): void
    {
        [$client] = $this->resolveTenantAndAdmin();
        $staffAdmin = $this->createStaffAdmin($client);

        $file = UploadedFile::fake()->create('bukti_transfer.jpg', 500, 'image/jpeg');

        $response = $this->actingAs($staffAdmin)->post(route('admin.subscriptions.renew'), [
            'payment_proof' => $file,
        ]);

        $response->assertStatus(403);
    }

    // =========================================================================
    // IT-MOD-04 - Item 10: Admin Toko Request Upgrade Paket (POST /admin/subscriptions/upgrade)
    // =========================================================================

    public function test_it_04_010_primary_admin_can_request_upgrade_to_business(): void
    {
        Storage::fake(config('filesystems.default', 'public'));
        [$client, $admin] = $this->resolveTenantAndAdmin();

        Subscription::where('client_id', $client->id)
            ->where('status', 'menunggu_konfirmasi')
            ->where('created_at', '>=', now()->subSeconds(60))
            ->update(['created_at' => now()->subMinutes(5)]);

        $file = UploadedFile::fake()->create('bukti_upgrade_business.png', 1024, 'image/png');

        $payload = [
            'plan_name' => 'business',
            'payment_proof' => $file,
            'notes' => 'Request upgrade toko ke paket Business untuk penambahan kuota produk.',
        ];

        $response = $this->actingAs($admin)->post(route('admin.subscriptions.upgrade'), $payload);

        $response->assertRedirect(route('admin.subscriptions.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('subscriptions', [
            'client_id' => $client->id,
            'plan_name' => 'Business',
            'status' => 'menunggu_konfirmasi',
            'payment_status' => 'menunggu',
            'notes' => 'Request upgrade toko ke paket Business untuk penambahan kuota produk.',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'client_id' => $client->id,
            'user_id' => $admin->id,
            'action' => 'subscription_upgrade_requested',
            'module' => 'subscriptions',
        ]);
    }

    public function test_it_04_010_primary_admin_can_request_upgrade_to_professional(): void
    {
        Storage::fake(config('filesystems.default', 'public'));
        [$client, $admin] = $this->resolveTenantAndAdmin();

        Subscription::where('client_id', $client->id)
            ->where('status', 'menunggu_konfirmasi')
            ->where('created_at', '>=', now()->subSeconds(60))
            ->update(['created_at' => now()->subMinutes(5)]);

        $file = UploadedFile::fake()->create('bukti_upgrade_pro.pdf', 1200, 'application/pdf');

        $payload = [
            'plan_name' => 'professional',
            'payment_proof' => $file,
            'notes' => 'Request upgrade toko ke paket Professional.',
        ];

        $response = $this->actingAs($admin)->post(route('admin.subscriptions.upgrade'), $payload);

        $response->assertRedirect(route('admin.subscriptions.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('subscriptions', [
            'client_id' => $client->id,
            'plan_name' => 'Professional',
            'status' => 'menunggu_konfirmasi',
            'payment_status' => 'menunggu',
        ]);
    }

    public function test_it_04_010_request_upgrade_validation_fails_with_invalid_plan_name(): void
    {
        Storage::fake(config('filesystems.default', 'public'));
        [$client, $admin] = $this->resolveTenantAndAdmin();

        $file = UploadedFile::fake()->create('bukti.jpg', 500, 'image/jpeg');

        $response = $this->actingAs($admin)
            ->from(route('admin.subscriptions.index'))
            ->post(route('admin.subscriptions.upgrade'), [
                'plan_name' => 'enterprise_invalid',
                'payment_proof' => $file,
            ]);

        $response->assertRedirect(route('admin.subscriptions.index'));
        $response->assertSessionHasErrors(['plan_name']);
    }

    public function test_it_04_010_request_upgrade_validation_fails_without_payment_proof(): void
    {
        [$client, $admin] = $this->resolveTenantAndAdmin();

        $response = $this->actingAs($admin)
            ->from(route('admin.subscriptions.index'))
            ->post(route('admin.subscriptions.upgrade'), [
                'plan_name' => 'business',
            ]);

        $response->assertRedirect(route('admin.subscriptions.index'));
        $response->assertSessionHasErrors(['payment_proof']);
    }

    public function test_it_04_010_staff_admin_cannot_request_subscription_upgrade(): void
    {
        [$client] = $this->resolveTenantAndAdmin();
        $staffAdmin = $this->createStaffAdmin($client);

        $file = UploadedFile::fake()->create('bukti.jpg', 500, 'image/jpeg');

        $response = $this->actingAs($staffAdmin)->post(route('admin.subscriptions.upgrade'), [
            'plan_name' => 'business',
            'payment_proof' => $file,
        ]);

        $response->assertStatus(403);
    }
}
