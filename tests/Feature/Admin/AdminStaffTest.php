<?php

namespace Tests\Feature\Admin;

use App\Models\Client;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminStaffTest extends TestCase
{
    use RefreshDatabase;

    private Client $clientA;
    private Client $clientB;
    private User $primaryAdminA;
    private User $staffAdminA;
    private User $primaryAdminB;
    private User $customer;
    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        // 1. Client A dengan paket Starter (Maksimal 1 Admin Aktif)
        $this->clientA = Client::create([
            'business_name' => 'Kamera Jaya Rental',
            'subdomain' => 'kamerajaya',
            'status' => 'aktif',
        ]);

        $this->primaryAdminA = User::create([
            'client_id' => $this->clientA->id,
            'name' => 'Fiza Admin Utama',
            'email' => 'fiza.primary@kamerajaya.test',
            'password' => Hash::make('password123'),
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        $this->clientA->update(['primary_admin_id' => $this->primaryAdminA->id]);

        Subscription::create([
            'client_id' => $this->clientA->id,
            'plan_name' => 'starter',
            'max_products' => 10,
            'max_users' => 1,
            'price' => 0,
            'billing_cycle' => 'monthly',
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->addDays(25),
            'status' => 'aktif',
        ]);

        // Akun staf admin (non-primary) di Toko A
        $this->staffAdminA = User::create([
            'client_id' => $this->clientA->id,
            'name' => 'Staff Toko A',
            'email' => 'staff.a@kamerajaya.test',
            'password' => Hash::make('password123'),
            'role' => 'admin_rental',
            'status' => 'nonaktif',
        ]);

        // 2. Client B untuk uji isolasi tenant
        $this->clientB = Client::create([
            'business_name' => 'Outdoor Gear Toko B',
            'subdomain' => 'outdoortokob',
            'status' => 'aktif',
        ]);

        $this->primaryAdminB = User::create([
            'client_id' => $this->clientB->id,
            'name' => 'Admin Utama Toko B',
            'email' => 'admin.b@outdoortokob.test',
            'password' => Hash::make('password123'),
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        $this->clientB->update(['primary_admin_id' => $this->primaryAdminB->id]);

        // 3. Customer & Owner
        $this->customer = User::create([
            'name' => 'Customer Budi',
            'email' => 'budi@gmail.test',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        $this->owner = User::create([
            'name' => 'Owner Platform',
            'email' => 'owner@rentalbase.id',
            'password' => Hash::make('password123'),
            'role' => 'owner',
            'status' => 'aktif',
        ]);
    }

    // ==========================================
    // A. OTORISASI & PROTEKSI ROLE (401 / 403 / REDIRECT)
    // ==========================================

    public function test_unauthenticated_user_cannot_access_admin_staff_page(): void
    {
        $response = $this->get(route('admin.admins.index'));

        $response->assertRedirect('/');
        $response->assertSessionHas('error', 'Silakan login terlebih dahulu.');
    }

    public function test_customer_cannot_access_admin_staff_page(): void
    {
        $response = $this->actingAs($this->customer)->get(route('admin.admins.index'));

        $response->assertRedirect('/');
        $response->assertSessionHas('error');
    }

    public function test_owner_cannot_access_admin_staff_page(): void
    {
        $response = $this->actingAs($this->owner)->get(route('admin.admins.index'));

        $response->assertRedirect(route('owner.dashboard'));
    }

    public function test_staff_admin_cannot_access_admin_staff_management(): void
    {
        // Staf admin (bukan Primary Admin) harus ditolak dengan 403
        $response = $this->actingAs($this->staffAdminA)->get(route('admin.admins.index'));

        $response->assertStatus(403);
    }

    // ==========================================
    // B. SKENARIO POSITIF & MONITORING KUOTA
    // ==========================================

    public function test_primary_admin_can_view_staff_list_and_quota_indicators(): void
    {
        $response = $this->actingAs($this->primaryAdminA)->get(route('admin.admins.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.admins.index');
        $response->assertSee('Fiza Admin Utama');
        $response->assertSee('Staff Toko A');
        $response->assertViewHas('maxAdmins', 1);
        $response->assertViewHas('activeAdminsCount', 1);
        $response->assertViewHas('isQuotaFull', true);
    }

    public function test_primary_admin_can_create_new_staff_admin_when_quota_allows(): void
    {
        // Upgrade paket Toko A ke Business (Maksimal 3 Admin Aktif)
        Subscription::where('client_id', $this->clientA->id)->update([
            'plan_name' => 'business',
            'max_users' => 3,
        ]);

        $payload = [
            'name' => 'Staff Baru Kasir',
            'email' => 'kasir@kamerajaya.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->actingAs($this->primaryAdminA)->post(route('admin.admins.store'), $payload);

        $response->assertRedirect(route('admin.admins.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'client_id' => $this->clientA->id,
            'name' => 'Staff Baru Kasir',
            'email' => 'kasir@kamerajaya.test',
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);
    }

    // ==========================================
    // C. VALIDASI GAGAL & BATASAN KUOTA
    // ==========================================

    public function test_store_staff_fails_with_validation_errors(): void
    {
        // Form kosong dan password tidak cocok
        $response = $this->actingAs($this->primaryAdminA)->post(route('admin.admins.store'), [
            'name' => '',
            'email' => 'bukan-email',
            'password' => 'pass',
            'password_confirmation' => 'beda',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
    }

    public function test_store_staff_fails_with_duplicate_email(): void
    {
        $response = $this->actingAs($this->primaryAdminA)->post(route('admin.admins.store'), [
            'name' => 'Duplikat Email',
            'email' => $this->primaryAdminA->email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_store_staff_fails_when_plan_quota_is_exceeded(): void
    {
        // Paket Starter Toko A kuotanya 1 admin dan sudah terpakai oleh primaryAdminA
        $response = $this->actingAs($this->primaryAdminA)->post(route('admin.admins.store'), [
            'name' => 'Admin Kelebihan Kuota',
            'email' => 'kelebihan@kamerajaya.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('users', [
            'email' => 'kelebihan@kamerajaya.test',
        ]);
    }

    // ==========================================
    // D. TOGGLE STATUS & ATURAN KEAMANAN (BUSINESS RULES)
    // ==========================================

    public function test_primary_admin_can_toggle_staff_status_to_inactive(): void
    {
        // Buat staf aktif
        $activeStaff = User::create([
            'client_id' => $this->clientA->id,
            'name' => 'Staf Aktif Sementara',
            'email' => 'aktif.sementara@kamerajaya.test',
            'password' => Hash::make('password123'),
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->primaryAdminA)->post(route('admin.admins.toggle-status', $activeStaff->id));

        $response->assertRedirect(route('admin.admins.index'));
        $response->assertSessionHas('success');

        $activeStaff->refresh();
        $this->assertEquals('nonaktif', $activeStaff->status);
    }

    public function test_cannot_deactivate_primary_admin(): void
    {
        $response = $this->actingAs($this->primaryAdminA)->post(route('admin.admins.toggle-status', $this->primaryAdminA->id));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Akun Admin Utama tidak dapat dinonaktifkan.');

        $this->primaryAdminA->refresh();
        $this->assertEquals('aktif', $this->primaryAdminA->status);
    }

    public function test_cannot_manage_staff_belonging_to_another_tenant(): void
    {
        // Admin Toko A mencoba mengubah status Admin Toko B (Cross-tenant attack)
        $response = $this->actingAs($this->primaryAdminA)->post(route('admin.admins.toggle-status', $this->primaryAdminB->id));

        $response->assertStatus(403);
    }
}

