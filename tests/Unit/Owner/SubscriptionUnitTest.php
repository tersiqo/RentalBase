<?php

namespace Tests\Unit\Owner;

use App\Models\Client;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionUnitTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Client::create([
            'business_name' => 'Toko Bayi Ceria',
            'subdomain' => 'bayiceria',
            'status' => 'aktif',
        ]);
    }

    public function test_active_subscription_renewal_extends_from_current_end_date(): void
    {
        $currentEndDate = Carbon::now()->addDays(10)->startOfDay();

        $sub = Subscription::create([
            'client_id' => $this->client->id,
            'plan_name' => 'business',
            'price' => 99000,
            'billing_cycle' => 'monthly',
            'start_date' => Carbon::now()->subMonths(5),
            'end_date' => $currentEndDate,
            'status' => 'aktif',
            'payment_status' => 'lunas',
        ]);

        $durationMonths = 6;
        $extendedEndDate = $sub->end_date->copy()->addMonths($durationMonths);

        $sub->update([
            'end_date' => $extendedEndDate,
            'payment_status' => 'diverifikasi',
        ]);

        $this->assertEquals($currentEndDate->copy()->addMonths(6), $sub->fresh()->end_date);
    }

    public function test_expired_subscription_renewal_starts_from_now(): void
    {
        $pastEndDate = Carbon::now()->subDays(15);

        $sub = Subscription::create([
            'client_id' => $this->client->id,
            'plan_name' => 'starter',
            'price' => 0,
            'billing_cycle' => 'monthly',
            'start_date' => Carbon::now()->subMonths(4),
            'end_date' => $pastEndDate,
            'status' => 'kadaluarsa',
            'payment_status' => 'lunas',
        ]);

        $now = Carbon::now()->startOfDay();
        $durationMonths = 3;
        $newStartDate = $now;
        $newEndDate = $now->copy()->addMonths($durationMonths);

        $sub->update([
            'start_date' => $newStartDate,
            'end_date' => $newEndDate,
            'status' => 'aktif',
            'payment_status' => 'diverifikasi',
        ]);

        $fresh = $sub->fresh();
        $this->assertSame('aktif', $fresh->status);
        $this->assertSame('diverifikasi', $fresh->payment_status);
        $this->assertEquals($newEndDate, $fresh->end_date);
    }

    public function test_subscription_rejection_updates_status_and_rejection_reason(): void
    {
        $sub = Subscription::create([
            'client_id' => $this->client->id,
            'plan_name' => 'business',
            'price' => 99000,
            'billing_cycle' => 'monthly',
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addMonths(6),
            'status' => 'menunggu_verifikasi',
            'payment_status' => 'menunggu',
        ]);

        $reason = 'Nominal transfer tidak sesuai tagihan perpanjangan.';

        $sub->update([
            'status' => 'ditolak',
            'payment_status' => 'ditolak',
            'rejection_reason' => $reason,
        ]);

        $fresh = $sub->fresh();
        $this->assertSame('ditolak', $fresh->status);
        $this->assertSame('ditolak', $fresh->payment_status);
        $this->assertSame($reason, $fresh->rejection_reason);
    }
}

