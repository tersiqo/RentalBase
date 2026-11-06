<?php

namespace Tests\Unit\Owner;

use App\Mail\TenantApproved;
use App\Mail\TenantRejected;
use App\Models\ClientRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OwnerRegistrationVerificationUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_approval_status_transition(): void
    {
        $reg = ClientRegistration::create([
            'business_name' => 'Kamera Bintang',
            'subdomain' => 'kamerabintang',
            'plan_name' => 'starter',
            'admin_name' => 'Bintang PIC',
            'admin_email' => 'bintang@test.com',
            'admin_password' => Hash::make('password123'),
            'status' => 'menunggu_verifikasi',
            'payment_status' => 'menunggu',
        ]);

        $reg->update([
            'status' => 'disetujui',
            'payment_status' => 'diverifikasi',
        ]);

        $this->assertSame('disetujui', $reg->fresh()->status);
        $this->assertSame('diverifikasi', $reg->fresh()->payment_status);
    }

    public function test_registration_rejection_stores_reason_and_audit(): void
    {
        $owner = User::create([
            'name' => 'Owner Platform',
            'email' => 'owner@platform.test',
            'password' => Hash::make('password123'),
            'role' => 'owner',
            'status' => 'aktif',
        ]);

        $reg = ClientRegistration::create([
            'business_name' => 'Sound Malang',
            'subdomain' => 'soundmlg',
            'plan_name' => 'business',
            'admin_name' => 'Ahmad PIC',
            'admin_email' => 'ahmad@test.com',
            'admin_password' => Hash::make('password123'),
            'status' => 'menunggu_verifikasi',
        ]);

        $rejectionReason = 'Bukti pembayaran tidak sesuai nominal paket.';

        $reg->update([
            'status' => 'ditolak',
            'payment_status' => 'ditolak',
            'rejection_reason' => $rejectionReason,
            'reviewed_by' => $owner->id,
        ]);

        $fresh = $reg->fresh();
        $this->assertSame('ditolak', $fresh->status);
        $this->assertSame($rejectionReason, $fresh->rejection_reason);
        $this->assertSame($owner->id, $fresh->reviewed_by);
    }

    public function test_tenant_approved_mailable_builds_correct_subject_and_attributes(): void
    {
        $reg = new ClientRegistration([
            'business_name' => 'Lensa Pro Malang',
            'subdomain' => 'lensapro',
            'plan_name' => 'business',
            'admin_name' => 'Doni Admin',
            'admin_email' => 'doni@lensapro.test',
        ]);

        $mailable = new TenantApproved($reg);

        $this->assertStringContainsString('Lensa Pro Malang', $mailable->envelope()->subject);
        $this->assertStringContainsString('Telah Disetujui', $mailable->envelope()->subject);
        $this->assertSame('Lensa Pro Malang', $mailable->businessName);
        $this->assertSame('lensapro', $mailable->subdomain);
    }

    public function test_tenant_rejected_mailable_includes_rejection_reason(): void
    {
        $reg = new ClientRegistration([
            'business_name' => 'Tenda Outdoor',
            'subdomain' => 'tendaoutdoor',
            'plan_name' => 'starter',
            'admin_name' => 'Eko Admin',
            'admin_email' => 'eko@outdoor.test',
        ]);

        $reason = 'Foto identitas buram dan tidak terbaca.';
        $mailable = new TenantRejected($reg, $reason);

        $this->assertStringContainsString('Belum Dapat Disetujui', $mailable->envelope()->subject);
        $this->assertSame($reason, $mailable->rejectionReason);
    }
}

