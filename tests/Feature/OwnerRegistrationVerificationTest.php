<?php

namespace Tests\Feature;

use App\Mail\TenantApproved;
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
}
