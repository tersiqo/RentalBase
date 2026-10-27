<?php

namespace Tests\Feature;

use App\Mail\TenantApproved;
use App\Mail\TenantRejected;
use App\Models\Client;
use App\Models\ClientRegistration;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OwnerRegistrationVerificationTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::create([
            'name' => 'Owner Platform',
            'email' => 'owner@rentalbase.id',
            'password' => Hash::make('password123'),
            'role' => 'owner',
            'status' => 'aktif',
        ]);
    }

    public function test_owner_can_view_registration_queue(): void
    {
        $this->withoutVite();

        ClientRegistration::create([
            'business_name' => 'Kamera Malang Rental',
            'description' => 'Sewa kamera profesional',
            'subdomain' => 'kameramlg',
            'plan_name' => 'business',
            'admin_name' => 'Ahmad PIC',
            'admin_email' => 'ahmad@example.com',
            'admin_phone' => '081234567890',
            'admin_password' => Hash::make('password123'),
            'status' => 'menunggu_verifikasi',
        ]);

        $response = $this->actingAs($this->owner)->get(route('owner.registrations.index'));

        $response->assertStatus(200);
        $response->assertSee('Kamera Malang Rental');
        $response->assertSee('ahmad@example.com');
        $response->assertSee('kameramlg.rentalbase.id');
        $response->assertSee('Menunggu Verifikasi');
    }

    public function test_owner_can_verify_tenant_and_notification_email_is_dispatched(): void
    {
        Mail::fake();

        $client = Client::create([
            'business_name' => 'Outdoor Gear Malang',
            'subdomain' => 'outdoormalang',
            'status' => 'menunggu_verifikasi',
        ]);

        $user = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@outdoorgear.id',
            'password' => Hash::make('secret123'),
            'role' => 'admin_rental',
            'client_id' => $client->id,
            'status' => 'menunggu_verifikasi',
        ]);

        $subscription = Subscription::create([
            'client_id' => $client->id,
            'plan_name' => 'business',
            'price' => 149000,
            'billing_cycle' => 'monthly',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'status' => 'menunggu_verifikasi',
            'payment_status' => 'menunggu',
        ]);

        $registration = ClientRegistration::create([
            'business_name' => 'Outdoor Gear Malang',
            'description' => 'Persewaan tenda dan alat camping',
            'subdomain' => 'outdoormalang',
            'plan_name' => 'business',
            'admin_name' => 'Budi Santoso',
            'admin_email' => 'budi@outdoorgear.id',
            'admin_phone' => '081298765432',
            'admin_password' => $user->password,
            'status' => 'menunggu_verifikasi',
            'payment_status' => 'menunggu',
            'payment_proof' => 'payment_proofs/dummy.jpg',
        ]);

        $response = $this->actingAs($this->owner)->post(route('owner.registrations.verify', $registration->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Database assertions: Status records transitioned to aktif
        $this->assertDatabaseHas('client_registrations', [
            'id' => $registration->id,
            'status' => 'disetujui',
            'payment_status' => 'diverifikasi',
        ]);

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'status' => 'aktif',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => 'aktif',
        ]);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'status' => 'aktif',
            'payment_status' => 'lunas',
        ]);

        // Email assertions: Mail sent to admin_email
        Mail::assertSent(TenantApproved::class, function (TenantApproved $mail) use ($registration) {
            return $mail->hasTo('budi@outdoorgear.id')
                && str_contains($mail->envelope()->subject, 'Outdoor Gear Malang')
                && $mail->businessName === 'Outdoor Gear Malang';
        });
    }

    public function test_repeated_approval_does_not_send_duplicate_email(): void
    {
        Mail::fake();

        $registration = ClientRegistration::create([
            'business_name' => 'Studio Foto Express',
            'subdomain' => 'studiofoto',
            'plan_name' => 'starter',
            'admin_name' => 'Citra Dewi',
            'admin_email' => 'citra@studiofoto.id',
            'admin_password' => Hash::make('password123'),
            'status' => 'disetujui',
            'payment_status' => 'diverifikasi',
        ]);

        $response = $this->actingAs($this->owner)->post(route('owner.registrations.verify', $registration->id));

        $response->assertRedirect();
        $response->assertSessionHas('info', 'Pendaftaran tenant ini sudah pernah diverifikasi sebelumnya.');

        // No mail sent on repeated verification
        Mail::assertNothingQueued();
        Mail::assertNothingSent();
    }

    public function test_email_failure_does_not_rollback_committed_database_transaction(): void
    {
        $client = Client::create([
            'business_name' => 'Drone Pro Rental',
            'subdomain' => 'dronepro',
            'status' => 'menunggu_verifikasi',
        ]);

        $user = User::create([
            'name' => 'Dedi Gunawan',
            'email' => 'dedi@dronepro.id',
            'password' => Hash::make('secret123'),
            'role' => 'admin_rental',
            'client_id' => $client->id,
            'status' => 'menunggu_verifikasi',
        ]);

        $registration = ClientRegistration::create([
            'business_name' => 'Drone Pro Rental',
            'subdomain' => 'dronepro',
            'plan_name' => 'starter',
            'admin_name' => 'Dedi Gunawan',
            'admin_email' => 'dedi@dronepro.id',
            'admin_password' => $user->password,
            'status' => 'menunggu_verifikasi',
            'payment_status' => 'menunggu',
        ]);

        // Simulate mail transport failure
        Mail::shouldReceive('to')
            ->once()
            ->with('dedi@dronepro.id')
            ->andThrow(new \Exception('SMTP connection timeout'));

        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) use ($registration) {
                return str_contains($message, 'Gagal mengirim email persetujuan')
                    && $context['registration_id'] === $registration->id
                    && $context['email'] === 'dedi@dronepro.id';
            });

        $response = $this->actingAs($this->owner)->post(route('owner.registrations.verify', $registration->id));

        $response->assertRedirect();
        $response->assertSessionHas('warning');

        // Database transaction remains committed despite mail failure
        $this->assertDatabaseHas('client_registrations', [
            'id' => $registration->id,
            'status' => 'disetujui',
        ]);

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'status' => 'aktif',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => 'aktif',
        ]);
    }

    public function test_tenant_approved_mailable_contains_required_information_without_plain_password(): void
    {
        $registration = new ClientRegistration([
            'business_name' => 'Bintang Sound System',
            'subdomain' => 'bintangsound',
            'plan_name' => 'business',
            'admin_name' => 'Eko Prasetyo',
            'admin_email' => 'eko@bintangsound.id',
            'admin_phone' => '081122334455',
            'admin_password' => 'secret_hashed_password',
        ]);

        $mailable = new TenantApproved($registration);

        // Subject check
        $this->assertStringContainsString('Bintang Sound System', $mailable->envelope()->subject);
        $this->assertStringContainsString('Disetujui', $mailable->envelope()->subject);

        // Render HTML and assert contents
        $html = $mailable->render();

        $this->assertStringContainsString('Bintang Sound System', $html);
        $this->assertStringContainsString('Eko Prasetyo', $html);
        $this->assertStringContainsString('eko@bintangsound.id', $html);
        $this->assertStringContainsString('bintangsound.rentalbase.id', $html);
        $this->assertStringContainsString('Pendaftaran Toko Disetujui', $html);
        $this->assertStringContainsString('Masuk ke Dashboard Admin', $html);
        $this->assertStringContainsString(route('login'), $html);
        $this->assertStringContainsString(route('customer.home', ['subdomain' => 'bintangsound']), $html);

        // Ensure raw password is NOT included
        $this->assertStringNotContainsString('secret_hashed_password', $html);
    }

    public function test_owner_can_sort_registrations_table_by_various_columns(): void
    {
        $this->withoutVite();

        ClientRegistration::create([
            'business_name' => 'AAA Rental',
            'subdomain' => 'aaa',
            'plan_name' => 'starter',
            'admin_name' => 'Zack Admin',
            'admin_email' => 'zack@example.com',
            'admin_phone' => '081111111111',
            'admin_password' => Hash::make('password123'),
            'status' => 'menunggu_verifikasi',
            'created_at' => now()->subDays(5),
        ]);

        ClientRegistration::create([
            'business_name' => 'ZZZ Rental',
            'subdomain' => 'zzz',
            'plan_name' => 'professional',
            'admin_name' => 'Adam Admin',
            'admin_email' => 'adam@example.com',
            'admin_phone' => '089999999999',
            'admin_password' => Hash::make('password123'),
            'status' => 'disetujui',
            'created_at' => now()->subDay(),
        ]);

        // Sort by business_name asc
        $responseAsc = $this->actingAs($this->owner)->get(route('owner.registrations.index', [
            'sort' => 'business_name',
            'direction' => 'asc',
        ]));
        $responseAsc->assertStatus(200);
        $registrationsAsc = $responseAsc->viewData('registrations');
        $this->assertEquals('AAA Rental', $registrationsAsc->first()->business_name);
        $this->assertEquals('ZZZ Rental', $registrationsAsc->last()->business_name);

        // Sort by business_name desc
        $responseDesc = $this->actingAs($this->owner)->get(route('owner.registrations.index', [
            'sort' => 'business_name',
            'direction' => 'desc',
        ]));
        $responseDesc->assertStatus(200);
        $registrationsDesc = $responseDesc->viewData('registrations');
        $this->assertEquals('ZZZ Rental', $registrationsDesc->first()->business_name);
        $this->assertEquals('AAA Rental', $registrationsDesc->last()->business_name);

        // Sort by admin_name asc
        $responseAdmin = $this->actingAs($this->owner)->get(route('owner.registrations.index', [
            'sort' => 'admin_name',
            'direction' => 'asc',
        ]));
        $responseAdmin->assertStatus(200);
        $registrationsAdmin = $responseAdmin->viewData('registrations');
        $this->assertEquals('Adam Admin', $registrationsAdmin->first()->admin_name);

        // Sort by created_at desc (default)
        $responseCreated = $this->actingAs($this->owner)->get(route('owner.registrations.index', [
            'sort' => 'created_at',
            'direction' => 'desc',
        ]));
        $responseCreated->assertStatus(200);
        $registrationsCreated = $responseCreated->viewData('registrations');
        $this->assertEquals('ZZZ Rental', $registrationsCreated->first()->business_name);
    }

    public function test_owner_can_reject_registration_and_updates_all_associated_records(): void
    {
        Mail::fake();

        $client = Client::create([
            'business_name' => 'Kamera Bali Rental',
            'subdomain' => 'kamerabali',
            'status' => 'menunggu_verifikasi',
        ]);

        $user = User::create([
            'name' => 'Wayan PIC',
            'email' => 'wayan@kamerabali.id',
            'password' => Hash::make('password123'),
            'role' => 'admin_rental',
            'client_id' => $client->id,
            'status' => 'menunggu_verifikasi',
        ]);

        $subscription = Subscription::create([
            'client_id' => $client->id,
            'plan_name' => 'business',
            'price' => 99000,
            'billing_cycle' => 'monthly',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'status' => 'menunggu_verifikasi',
            'payment_status' => 'menunggu',
        ]);

        $reg = ClientRegistration::create([
            'business_name' => 'Kamera Bali Rental',
            'description' => 'Sewa kamera mirrorless dan lensa di Denpasar',
            'subdomain' => 'kamerabali',
            'plan_name' => 'business',
            'admin_name' => 'Wayan PIC',
            'admin_email' => 'wayan@kamerabali.id',
            'admin_password' => $user->password,
            'status' => 'menunggu_verifikasi',
            'payment_status' => 'menunggu',
            'payment_proof' => 'subscriptions/proofs/sample.jpg',
        ]);

        $rejectionReason = 'Bukti pembayaran buram dan tidak terbaca. Mohon unggah struk transfer yang jelas.';

        $response = $this->actingAs($this->owner)->post(route('owner.registrations.reject', $reg->id), [
            'rejection_reason' => $rejectionReason,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Client registration record must be updated to ditolak with reason and audit info
        $this->assertDatabaseHas('client_registrations', [
            'id' => $reg->id,
            'status' => 'ditolak',
            'payment_status' => 'ditolak',
            'rejection_reason' => $rejectionReason,
            'reviewed_by' => $this->owner->id,
        ]);

        // Related records must also be updated to ditolak without deletion
        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'status' => 'ditolak',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => 'ditolak',
        ]);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'status' => 'ditolak',
            'payment_status' => 'ditolak',
        ]);

        // Assert rejection email was sent to tenant's admin email
        Mail::assertSent(TenantRejected::class, function (TenantRejected $mail) use ($reg, $rejectionReason) {
            return $mail->hasTo('wayan@kamerabali.id')
                && str_contains($mail->envelope()->subject, 'Kamera Bali Rental')
                && $mail->businessName === 'Kamera Bali Rental'
                && $mail->rejectionReason === $rejectionReason;
        });
    }

    public function test_owner_reject_handles_email_failure_gracefully(): void
    {
        $client = Client::create([
            'business_name' => 'Surabaya Sound Rental',
            'subdomain' => 'surabayasound',
            'status' => 'menunggu_verifikasi',
        ]);

        $user = User::create([
            'name' => 'Joko PIC',
            'email' => 'joko@surabayasound.id',
            'password' => Hash::make('password123'),
            'role' => 'admin_rental',
            'client_id' => $client->id,
            'status' => 'menunggu_verifikasi',
        ]);

        $subscription = Subscription::create([
            'client_id' => $client->id,
            'plan_name' => 'business',
            'price' => 99000,
            'billing_cycle' => 'monthly',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'status' => 'menunggu_verifikasi',
            'payment_status' => 'menunggu',
        ]);

        $reg = ClientRegistration::create([
            'business_name' => 'Surabaya Sound Rental',
            'subdomain' => 'surabayasound',
            'plan_name' => 'business',
            'admin_name' => 'Joko PIC',
            'admin_email' => 'joko@surabayasound.id',
            'admin_password' => $user->password,
            'status' => 'menunggu_verifikasi',
            'payment_status' => 'menunggu',
        ]);

        // Simulate mail failure
        Mail::shouldReceive('to')
            ->once()
            ->with('joko@surabayasound.id')
            ->andThrow(new \Exception('SMTP connection timeout'));

        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) use ($reg) {
                return str_contains($message, 'Gagal mengirim email penolakan')
                    && $context['registration_id'] === $reg->id
                    && $context['email'] === 'joko@surabayasound.id';
            });

        $response = $this->actingAs($this->owner)->post(route('owner.registrations.reject', $reg->id), [
            'rejection_reason' => 'Nominal transfer tidak sesuai dengan harga paket langganan.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('warning');

        // Database transaction remains committed despite mail failure
        $this->assertDatabaseHas('client_registrations', [
            'id' => $reg->id,
            'status' => 'ditolak',
            'rejection_reason' => 'Nominal transfer tidak sesuai dengan harga paket langganan.',
        ]);

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'status' => 'ditolak',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => 'ditolak',
        ]);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'status' => 'ditolak',
        ]);
    }

    public function test_owner_cannot_reject_already_approved_registration(): void
    {
        $reg = ClientRegistration::create([
            'business_name' => 'Jogja Sound Rental',
            'subdomain' => 'jogjasound',
            'plan_name' => 'starter',
            'admin_name' => 'Bambang PIC',
            'admin_email' => 'bambang@jogjasound.id',
            'admin_password' => Hash::make('password123'),
            'status' => 'disetujui',
            'payment_status' => 'diverifikasi',
        ]);

        $response = $this->actingAs($this->owner)->post(route('owner.registrations.reject', $reg->id), [
            'rejection_reason' => 'Coba tolak pendaftaran yang sudah aktif.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Pendaftaran tenant yang sudah disetujui tidak dapat ditolak.');

        $reg->refresh();
        $this->assertEquals('disetujui', $reg->status);
    }

    public function test_rejected_tenant_login_sets_pending_setup_and_flashes_rejection_reason(): void
    {
        $user = User::create([
            'name' => 'Slamet Tenant',
            'email' => 'slamet@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin_rental',
            'status' => 'ditolak',
        ]);

        ClientRegistration::create([
            'business_name' => 'Slamet Rent Car',
            'subdomain' => 'slametrent',
            'plan_name' => 'starter',
            'admin_name' => 'Slamet Tenant',
            'admin_email' => 'slamet@example.com',
            'admin_password' => $user->password,
            'status' => 'ditolak',
            'rejection_reason' => 'Foto identitas dan bukti bayar buram.',
        ]);

        $response = $this->post('/login', [
            'email' => 'slamet@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('tenant.register'));
        $response->assertSessionHas('rejection_reason', 'Foto identitas dan bukti bayar buram.');

        // User status must now be transitioned to pending_setup to allow fixing data
        $user->refresh();
        $this->assertEquals('pending_setup', $user->status);
    }

    public function test_rejected_tenant_can_view_setup_form_with_rejection_reason_alert(): void
    {
        $this->withoutVite();

        $client = Client::create([
            'business_name' => 'Slamet Rent Car',
            'subdomain' => 'slametrent',
            'status' => 'ditolak',
        ]);

        $user = User::create([
            'name' => 'Slamet Tenant',
            'email' => 'slamet@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin_rental',
            'client_id' => $client->id,
            'status' => 'pending_setup',
        ]);

        $response = $this->actingAs($user)
            ->withSession(['rejection_reason' => 'Foto identitas dan bukti bayar buram.'])
            ->get(route('tenant.register'));

        $response->assertStatus(200);
        $response->assertSee('Pendaftaran Anda sebelumnya ditolak dengan alasan:');
        $response->assertSee('Foto identitas dan bukti bayar buram.');
        $response->assertSee('Slamet Rent Car');
    }

    public function test_rejected_tenant_resubmission_updates_records_to_menunggu_verifikasi(): void
    {
        $client = Client::create([
            'business_name' => 'Slamet Rent Car',
            'subdomain' => 'slametrent',
            'status' => 'ditolak',
        ]);

        $user = User::create([
            'name' => 'Slamet Tenant',
            'email' => 'slamet@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin_rental',
            'client_id' => $client->id,
            'status' => 'pending_setup',
        ]);

        $subscription = Subscription::create([
            'client_id' => $client->id,
            'plan_name' => 'starter',
            'price' => 0,
            'billing_cycle' => 'monthly',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'status' => 'ditolak',
            'payment_status' => 'ditolak',
            'payment_proof' => 'subscriptions/proofs/old_proof.jpg',
        ]);

        $reg = ClientRegistration::create([
            'business_name' => 'Slamet Rent Car',
            'subdomain' => 'slametrent',
            'plan_name' => 'starter',
            'admin_name' => 'Slamet Tenant',
            'admin_email' => 'slamet@example.com',
            'admin_password' => $user->password,
            'status' => 'ditolak',
            'payment_status' => 'ditolak',
            'payment_proof' => 'subscriptions/proofs/old_proof.jpg',
            'rejection_reason' => 'Foto identitas dan bukti bayar buram.',
        ]);

        // Tenant submits updated store info
        $updateResponse = $this->actingAs($user)->post('/register-tenant', [
            'business_name' => 'Slamet Rent Car Official',
            'subdomain' => 'slametrent',
            'description' => 'Sewa mobil terlengkap dan terpercaya',
            'owner_name' => 'Slamet Riyadi',
            'phone' => '081234567899',
            'address' => 'Jl. Kebon Jeruk No. 5',
            'plan_name' => 'starter',
            'terms' => '1',
        ]);

        $updateResponse->assertRedirect('/register-tenant');

        // Tenant submits updated payment proof
        $newProof = \Illuminate\Http\UploadedFile::fake()->create('struk_baru.jpg', 150, 'image/jpeg');
        $paymentResponse = $this->actingAs($user)->post('/register-tenant', [
            'action_step' => 'payment',
            'payment_proof' => $newProof,
        ]);

        $paymentResponse->assertRedirect('/register-tenant');

        // All records must now be updated back to menunggu_verifikasi and rejection_reason cleared
        $this->assertDatabaseHas('client_registrations', [
            'id' => $reg->id,
            'business_name' => 'Slamet Rent Car Official',
            'status' => 'menunggu_verifikasi',
            'payment_status' => 'menunggu',
            'rejection_reason' => null,
        ]);

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'business_name' => 'Slamet Rent Car Official',
            'status' => 'menunggu_verifikasi',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => 'menunggu_verifikasi',
        ]);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'status' => 'menunggu_verifikasi',
            'payment_status' => 'menunggu',
        ]);

        // It must reappear in owner verification queue
        $ownerIndex = $this->actingAs($this->owner)->get(route('owner.registrations.index'));
        $ownerIndex->assertSee('Slamet Rent Car Official');
        $ownerIndex->assertSee('Menunggu Verifikasi');
    }

    public function test_rejected_tenant_login_with_google_redirects_to_setup_with_rejection_reason(): void
    {
        $user = User::create([
            'name' => 'google_user',
            'email' => 'google@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin_rental',
            'status' => 'ditolak',
        ]);

        ClientRegistration::create([
            'business_name' => 'Google Rental',
            'subdomain' => 'googlerental',
            'plan_name' => 'starter',
            'admin_name' => 'google_user',
            'admin_email' => 'google@example.com',
            'admin_password' => $user->password,
            'status' => 'ditolak',
            'rejection_reason' => 'KTP tidak sesuai dengan nama rekening.',
        ]);

        $abstractUser = \Mockery::mock(\Laravel\Socialite\Two\User::class);
        $abstractUser->shouldReceive('getId')->andReturn('google-id-12345');
        $abstractUser->shouldReceive('getName')->andReturn('Google User');
        $abstractUser->shouldReceive('getEmail')->andReturn('google@example.com');

        $provider = \Mockery::mock(\Laravel\Socialite\Two\GoogleProvider::class);
        $provider->shouldReceive('user')->andReturn($abstractUser);

        \Laravel\Socialite\Facades\Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect(route('tenant.register'));
        $response->assertSessionHas('rejection_reason', 'KTP tidak sesuai dengan nama rekening.');

        $user->refresh();
        $this->assertEquals('pending_setup', $user->status);
    }
}
