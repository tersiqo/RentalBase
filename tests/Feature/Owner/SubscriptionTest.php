<?php

namespace Tests\Feature\Owner;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $adminRental;
    protected Client $clientA;
    protected Client $clientB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        // 1. Akun Super Admin / Owner Platform
        $this->owner = User::create([
            'name'     => 'Super Owner',
            'email'    => 'owner@rentalbase.id',
            'password' => Hash::make('password123'),
            'role'     => 'owner',
            'status'   => 'aktif',
        ]);

        // 2. Tenant Client A (Starter)
        $this->clientA = Client::create([
            'business_name' => 'Rental Bayi Ceria',
            'subdomain'     => 'bayiceria',
            'status'        => 'aktif',
        ]);

        $this->adminRental = User::create([
            'client_id' => $this->clientA->id,
            'name'      => 'Admin Bayi Ceria',
            'email'     => 'admin@bayiceria.com',
            'password'  => Hash::make('password123'),
            'role'      => 'admin_rental',
            'status'    => 'aktif',
        ]);

        // 3. Tenant Client B (Business)
        $this->clientB = Client::create([
            'business_name' => 'Stroller Kids Malang',
            'subdomain'     => 'strollerkids',
            'status'        => 'aktif',
        ]);
    }

    // ==========================================
    // A. OTORISASI & PROTEKSI ROUTE
    // ==========================================

    public function test_unauthenticated_user_cannot_access_subscriptions_page(): void
    {
        $response = $this->get(route('owner.subscriptions.index'));
        $response->assertRedirect(route('owner.login'));
    }

    public function test_owner_can_access_subscriptions_page(): void
    {
        $response = $this->actingAs($this->owner)->get(route('owner.subscriptions.index'));

        $response->assertStatus(200);
        $response->assertSee('Masa Aktif Tenant');
        $response->assertSee('Tenant Aktif');
        $response->assertSee('Jatuh Tempo (H-7)');
        $response->assertSee('Estimasi MRR');
    }

    public function test_admin_rental_cannot_access_subscriptions_page(): void
    {
        $response = $this->actingAs($this->adminRental)->get(route('owner.subscriptions.index'));
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_rental_cannot_renew_subscription_directly(): void
    {
        $sub = Subscription::create([
            'client_id'      => $this->clientA->id,
            'plan_name'      => 'starter',
            'max_products'   => 10,
            'max_users'      => 1,
            'price'          => 0,
            'billing_cycle'  => 'monthly',
            'start_date'     => Carbon::now()->subMonth(),
            'end_date'       => Carbon::now()->addMonth(),
            'status'         => 'aktif',
            'payment_status' => 'lunas',
        ]);

        $response = $this->actingAs($this->adminRental)->post(route('owner.subscriptions.renew', $sub->id), [
            'reference_number' => 'REF-MALICIOUS',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_history_endpoint_is_protected_by_owner_middleware(): void
    {
        $sub = Subscription::create([
            'client_id'      => $this->clientA->id,
            'plan_name'      => 'starter',
            'max_products'   => 10,
            'max_users'      => 1,
            'price'          => 0,
            'billing_cycle'  => 'monthly',
            'start_date'     => Carbon::now()->subMonth(),
            'end_date'       => Carbon::now()->addMonth(),
            'status'         => 'aktif',
            'payment_status' => 'lunas',
        ]);

        // Unauthenticated
        $response = $this->get(route('owner.subscriptions.history', $sub->id));
        $response->assertRedirect(route('owner.login'));

        // Admin rental
        $response = $this->actingAs($this->adminRental)->get(route('owner.subscriptions.history', $sub->id));
        $response->assertRedirect(route('admin.dashboard'));

        // Owner
        $response = $this->actingAs($this->owner)->get(route('owner.subscriptions.history', $sub->id));
        $response->assertStatus(200);
    }

    // ==========================================
    // B. DAFTAR, RINGKASAN METRIK & FILTER
    // ==========================================

    public function test_dashboard_metrics_calculate_correctly(): void
    {
        $now = Carbon::now();

        // Sub 1: Aktif normal (end_date 30 hari lagi, Business: Rp 99.000 / 6 bln = 16.500/bln)
        Subscription::create([
            'client_id'      => $this->clientA->id,
            'plan_name'      => 'business',
            'price'          => 99000,
            'billing_cycle'  => 'monthly',
            'start_date'     => $now->copy()->subMonth(),
            'end_date'       => $now->copy()->addDays(30),
            'status'         => 'aktif',
            'payment_status' => 'lunas',
        ]);

        // Sub 2: Expiring Soon (end_date 3 hari lagi, Professional: Rp 249.000 / 12 bln = 20.750/bln)
        Subscription::create([
            'client_id'      => $this->clientB->id,
            'plan_name'      => 'professional',
            'price'          => 249000,
            'billing_cycle'  => 'monthly',
            'start_date'     => $now->copy()->subMonths(11),
            'end_date'       => $now->copy()->addDays(3),
            'status'         => 'aktif',
            'payment_status' => 'lunas',
        ]);

        // Sub 3: Client C Expired
        $clientC = Client::create([
            'business_name' => 'Rental Expired',
            'subdomain'     => 'expiredstore',
            'status'        => 'aktif',
        ]);
        Subscription::create([
            'client_id'      => $clientC->id,
            'plan_name'      => 'starter',
            'price'          => 0,
            'billing_cycle'  => 'monthly',
            'start_date'     => $now->copy()->subMonths(4),
            'end_date'       => $now->copy()->subDays(5),
            'status'         => 'kadaluarsa',
            'payment_status' => 'lunas',
        ]);

        $response = $this->actingAs($this->owner)->get(route('owner.subscriptions.index'));
        $response->assertStatus(200);

        // Assert data terlihat di tabel
        $response->assertSee('Rental Bayi Ceria');
        $response->assertSee('Stroller Kids Malang');
        $response->assertSee('Rental Expired');
    }

    public function test_filter_by_plan_and_status_works(): void
    {
        $now = Carbon::now();

        Subscription::create([
            'client_id'      => $this->clientA->id,
            'plan_name'      => 'starter',
            'price'          => 0,
            'billing_cycle'  => 'monthly',
            'start_date'     => $now->copy()->subMonth(),
            'end_date'       => $now->copy()->addMonths(2),
            'status'         => 'aktif',
            'payment_status' => 'lunas',
        ]);

        Subscription::create([
            'client_id'      => $this->clientB->id,
            'plan_name'      => 'professional',
            'price'          => 249000,
            'billing_cycle'  => 'monthly',
            'start_date'     => $now->copy()->subMonth(),
            'end_date'       => $now->copy()->addMonths(11),
            'status'         => 'aktif',
            'payment_status' => 'lunas',
        ]);

        // Filter paket Starter
        $responseStarter = $this->actingAs($this->owner)->get(route('owner.subscriptions.index', ['plan' => 'starter']));
        $responseStarter->assertSee('Rental Bayi Ceria');
        $responseStarter->assertDontSee('Stroller Kids Malang');

        // Filter paket Professional
        $responsePro = $this->actingAs($this->owner)->get(route('owner.subscriptions.index', ['plan' => 'professional']));
        $responsePro->assertSee('Stroller Kids Malang');
        $responsePro->assertDontSee('Rental Bayi Ceria');
    }

    public function test_search_by_business_name_and_subdomain_works(): void
    {
        $now = Carbon::now();

        Subscription::create([
            'client_id'      => $this->clientA->id,
            'plan_name'      => 'starter',
            'price'          => 0,
            'billing_cycle'  => 'monthly',
            'start_date'     => $now->copy()->subMonth(),
            'end_date'       => $now->copy()->addMonth(),
            'status'         => 'aktif',
            'payment_status' => 'lunas',
        ]);

        Subscription::create([
            'client_id'      => $this->clientB->id,
            'plan_name'      => 'business',
            'price'          => 99000,
            'billing_cycle'  => 'monthly',
            'start_date'     => $now->copy()->subMonth(),
            'end_date'       => $now->copy()->addMonths(5),
            'status'         => 'aktif',
            'payment_status' => 'lunas',
        ]);

        // Search by business_name
        $responseName = $this->actingAs($this->owner)->get(route('owner.subscriptions.index', ['search' => 'Stroller Kids']));
        $responseName->assertSee('Stroller Kids Malang');
        $responseName->assertDontSee('Rental Bayi Ceria');

        // Search by subdomain
        $responseSubdomain = $this->actingAs($this->owner)->get(route('owner.subscriptions.index', ['search' => 'bayiceria']));
        $responseSubdomain->assertSee('Rental Bayi Ceria');
        $responseSubdomain->assertDontSee('Stroller Kids Malang');
    }

    // ==========================================
    // C. LOGIKA PERPANJANGAN (RENEWAL)
    // ==========================================

    public function test_active_subscription_extends_from_current_end_date(): void
    {
        Storage::fake('public');
        $now = Carbon::now();

        // Subscription Starter (durasi 3 bulan) masih berlaku sampai 10 hari ke depan
        $currentEndDate = $now->copy()->addDays(10);
        $sub = Subscription::create([
            'client_id'      => $this->clientA->id,
            'plan_name'      => 'starter',
            'price'          => 0,
            'billing_cycle'  => 'monthly',
            'start_date'     => $now->copy()->subMonths(2),
            'end_date'       => $currentEndDate,
            'status'         => 'aktif',
            'payment_status' => 'lunas',
        ]);

        $file = UploadedFile::fake()->image('transfer.jpg');

        $response = $this->actingAs($this->owner)->post(route('owner.subscriptions.renew', $sub->id), [
            'reference_number' => 'INV-202610-001',
            'payment_proof'    => $file,
        ]);

        $response->assertRedirect(route('owner.subscriptions.index'));
        $response->assertSessionHas('success');

        $sub->refresh();

        // Tanggal akhir baru = currentEndDate + 3 bulan (durasi paket Starter)
        $expectedEndDate = $currentEndDate->copy()->addMonths(3);
        $this->assertEquals($expectedEndDate->format('Y-m-d H:i'), $sub->end_date->format('Y-m-d H:i'));
        $this->assertEquals('aktif', $sub->status);
        $this->assertEquals('lunas', $sub->payment_status);
        $this->assertNotNull($sub->payment_proof);

        // Verifikasi pencatatan ActivityLog
        $this->assertDatabaseHas('activity_logs', [
            'client_id' => $this->clientA->id,
            'user_id'   => $this->owner->id,
            'action'    => 'subscription_renewed',
            'module'    => 'subscriptions',
        ]);
    }

    public function test_expired_subscription_starts_new_period_from_now(): void
    {
        Storage::fake('public');
        $now = Carbon::now();

        // Subscription Business (durasi 6 bulan) sudah expired 15 hari yang lalu
        $sub = Subscription::create([
            'client_id'      => $this->clientB->id,
            'plan_name'      => 'business',
            'price'          => 99000,
            'billing_cycle'  => 'monthly',
            'start_date'     => $now->copy()->subMonths(7),
            'end_date'       => $now->copy()->subDays(15),
            'status'         => 'kadaluarsa',
            'payment_status' => 'lunas',
        ]);

        $file = UploadedFile::fake()->create('bukti_bayar.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->owner)->post(route('owner.subscriptions.renew', $sub->id), [
            'reference_number' => 'REF-TRANSFER-BCA-99',
            'payment_proof'    => $file,
        ]);

        $response->assertRedirect(route('owner.subscriptions.index'));
        $sub->refresh();

        // Tanggal mulai baru = hari ini (now)
        // Tanggal akhir baru = now + 6 bulan (durasi paket Business)
        $this->assertEquals('aktif', $sub->status);
        $this->assertTrue($sub->end_date->isFuture());
        $this->assertEquals($now->copy()->addMonths(6)->format('Y-m-d'), $sub->end_date->format('Y-m-d'));
    }

    public function test_renewal_requires_valid_reference_and_proof(): void
    {
        $now = Carbon::now();

        $sub = Subscription::create([
            'client_id'      => $this->clientA->id,
            'plan_name'      => 'starter',
            'price'          => 0,
            'billing_cycle'  => 'monthly',
            'start_date'     => $now->copy()->subMonth(),
            'end_date'       => $now->copy()->addMonth(),
            'status'         => 'aktif',
            'payment_status' => 'lunas',
        ]);

        // Submit form kosong
        $response = $this->actingAs($this->owner)->post(route('owner.subscriptions.renew', $sub->id), []);

        $response->assertSessionHasErrors(['reference_number', 'payment_proof']);
    }

    public function test_invalid_subscription_id_returns_404(): void
    {
        $response = $this->actingAs($this->owner)->post('/owner/subscriptions/999999/renew', [
            'reference_number' => 'REF-123',
        ]);

        $response->assertStatus(404);
    }

    public function test_owner_can_retrieve_subscription_history_via_json(): void
    {
        $now = Carbon::now();

        $sub = Subscription::create([
            'client_id'      => $this->clientA->id,
            'plan_name'      => 'starter',
            'price'          => 0,
            'billing_cycle'  => 'monthly',
            'start_date'     => $now->copy()->subMonth(),
            'end_date'       => $now->copy()->addMonth(),
            'status'         => 'aktif',
            'payment_status' => 'lunas',
        ]);

        // Buat log aktivitas
        ActivityLog::create([
            'client_id'   => $this->clientA->id,
            'user_id'     => $this->owner->id,
            'action'      => 'subscription_renewed',
            'module'      => 'subscriptions',
            'description' => 'Perpanjangan paket starter 3 bulan. Ref: REF-HIST-01',
        ]);

        $response = $this->actingAs($this->owner)->json('GET', route('owner.subscriptions.history', $sub->id));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'subscription',
            'logs',
            'history',
        ]);
        $response->assertJsonFragment([
            'action' => 'subscription_renewed',
        ]);
    }

    public function test_renewal_rejects_invalid_file_extension(): void
    {
        Storage::fake('public');
        $now = Carbon::now();

        $sub = Subscription::create([
            'client_id'      => $this->clientA->id,
            'plan_name'      => 'starter',
            'price'          => 0,
            'billing_cycle'  => 'monthly',
            'start_date'     => $now->copy()->subMonth(),
            'end_date'       => $now->copy()->addMonth(),
            'status'         => 'aktif',
            'payment_status' => 'lunas',
        ]);

        $invalidFile = UploadedFile::fake()->create('script.exe', 100, 'application/x-msdownload');

        $response = $this->actingAs($this->owner)->post(route('owner.subscriptions.renew', $sub->id), [
            'reference_number' => 'REF-INVALID-EXT',
            'payment_proof'    => $invalidFile,
        ]);

        $response->assertSessionHasErrors(['payment_proof']);
    }

    public function test_owner_can_access_history_page_directly(): void
    {
        $now = Carbon::now();

        $sub = Subscription::create([
            'client_id'      => $this->clientA->id,
            'plan_name'      => 'starter',
            'price'          => 0,
            'billing_cycle'  => 'monthly',
            'start_date'     => $now->copy()->subMonth(),
            'end_date'       => $now->copy()->addMonth(),
            'status'         => 'aktif',
            'payment_status' => 'lunas',
        ]);

        $response = $this->actingAs($this->owner)->get(route('owner.subscriptions.history', $sub->id));
        $response->assertStatus(200);
        $response->assertSee('Riwayat &amp; Log Perpanjangan', false);
    }
}
