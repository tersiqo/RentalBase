<?php

namespace Tests\Feature\Console;

use App\Models\Client;
use App\Models\Notification;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class CheckExpiredSubscriptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_marks_expired_subscription_and_suspends_client(): void
    {
        $client = Client::create([
            'business_name' => 'Toko Fotografi C',
            'subdomain' => 'tokoc',
            'status' => 'aktif',
        ]);

        $admin = User::factory()->create([
            'client_id' => $client->id,
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        $owner = User::factory()->create([
            'role' => 'owner',
            'status' => 'aktif',
        ]);

        $sub = Subscription::create([
            'client_id' => $client->id,
            'plan_name' => 'starter',
            'price' => 150000,
            'billing_cycle' => 'monthly',
            'start_date' => Carbon::now()->subMonths(2),
            'end_date' => Carbon::now()->subDays(2), // Lewat 2 hari
            'status' => 'aktif',
        ]);

        $exitCode = Artisan::call('subscriptions:check-expired');

        $this->assertEquals(0, $exitCode);
        $this->assertEquals('kadaluarsa', $sub->fresh()->status);
        $this->assertEquals('suspended', $client->fresh()->status);

        // Periksa notifikasi dikirim ke admin tenant
        $this->assertDatabaseHas('notifications', [
            'client_id' => $client->id,
            'user_id' => $admin->id,
            'type' => 'system',
        ]);

        // Periksa notifikasi dikirim ke owner
        $this->assertDatabaseHas('notifications', [
            'user_id' => $owner->id,
            'type' => 'subscription',
        ]);
    }

    public function test_does_not_suspend_client_with_another_active_subscription(): void
    {
        $client = Client::create([
            'business_name' => 'Toko Fotografi D',
            'subdomain' => 'tokod',
            'status' => 'aktif',
        ]);

        // Subscription lama yang sudah lewat
        $oldSub = Subscription::create([
            'client_id' => $client->id,
            'plan_name' => 'starter',
            'price' => 150000,
            'billing_cycle' => 'monthly',
            'start_date' => Carbon::now()->subMonths(2),
            'end_date' => Carbon::now()->subDays(2),
            'status' => 'aktif',
        ]);

        // Subscription baru perpanjangan yang aktif
        $newSub = Subscription::create([
            'client_id' => $client->id,
            'plan_name' => 'pro',
            'price' => 300000,
            'billing_cycle' => 'monthly',
            'start_date' => Carbon::now()->subDays(1),
            'end_date' => Carbon::now()->addDays(28),
            'status' => 'aktif',
        ]);

        Artisan::call('subscriptions:check-expired');

        $this->assertEquals('kadaluarsa', $oldSub->fresh()->status);
        $this->assertEquals('aktif', $newSub->fresh()->status);
        // Tenant tetap aktif karena punya subscription baru
        $this->assertEquals('aktif', $client->fresh()->status);
    }
}
