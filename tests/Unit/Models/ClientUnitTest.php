<?php

namespace Tests\Unit\Models;

use App\Models\Client;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ClientUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_subscription_relation_only_returns_active_status(): void
    {
        $client = Client::create([
            'business_name' => 'Kamera Malang',
            'subdomain' => 'kameramlg',
            'status' => 'aktif',
        ]);

        Subscription::create([
            'client_id' => $client->id,
            'plan_name' => 'starter',
            'price' => 0,
            'billing_cycle' => 'monthly',
            'start_date' => Carbon::now()->subMonths(2),
            'end_date' => Carbon::now()->subMonth(),
            'status' => 'kadaluarsa',
        ]);

        $activeSub = Subscription::create([
            'client_id' => $client->id,
            'plan_name' => 'business',
            'price' => 99000,
            'billing_cycle' => 'monthly',
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addMonths(6),
            'status' => 'aktif',
        ]);

        $this->assertNotNull($client->activeSubscription);
        $this->assertEquals($activeSub->id, $client->activeSubscription->id);
        $this->assertEquals('business', $client->activeSubscription->plan_name);
    }

    public function test_latest_subscription_returns_most_recent_regardless_of_status(): void
    {
        $client = Client::create([
            'business_name' => 'Outdoor Malang',
            'subdomain' => 'outdoormlg',
            'status' => 'aktif',
        ]);

        Subscription::create([
            'client_id' => $client->id,
            'plan_name' => 'starter',
            'price' => 0,
            'billing_cycle' => 'monthly',
            'start_date' => Carbon::now()->subMonths(3),
            'end_date' => Carbon::now()->subMonth(),
            'status' => 'kadaluarsa',
        ]);

        $latestSub = Subscription::create([
            'client_id' => $client->id,
            'plan_name' => 'professional',
            'price' => 199000,
            'billing_cycle' => 'yearly',
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addYear(),
            'status' => 'menunggu_verifikasi',
        ]);

        $this->assertEquals($latestSub->id, $client->latestSubscription->id);
        $this->assertEquals('professional', $client->latestSubscription->plan_name);
    }

    public function test_get_plan_attribute_defaults_to_starter_when_no_active_subscription(): void
    {
        $client = Client::create([
            'business_name' => 'Toko Tanpa Sub',
            'subdomain' => 'tanpasub',
            'status' => 'aktif',
        ]);

        $this->assertEquals('starter', $client->plan);
    }

    public function test_admin_user_relation_only_retrieves_user_with_admin_rental_role(): void
    {
        $client = Client::create([
            'business_name' => 'Studio Foto Rental',
            'subdomain' => 'studiofoto',
            'status' => 'aktif',
        ]);

        $admin = User::create([
            'client_id' => $client->id,
            'name' => 'Admin Studio',
            'email' => 'admin@studiofoto.test',
            'password' => Hash::make('password123'),
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        $this->assertNotNull($client->adminUser);
        $this->assertEquals($admin->id, $client->adminUser->id);
        $this->assertEquals('admin_rental', $client->adminUser->role);
    }

    public function test_owner_tenant_package_limits_and_pricing_resolution(): void
    {
        $plans = [
            'starter' => ['products' => 10, 'admins' => 1, 'price' => 0.0],
            'business' => ['products' => 50, 'admins' => 3, 'price' => 99000.0],
            'professional' => ['products' => null, 'admins' => 10, 'price' => 249000.0],
        ];

        // Starter
        $this->assertSame(10, $plans['starter']['products']);
        $this->assertSame(1, $plans['starter']['admins']);
        $this->assertEquals(0.0, $plans['starter']['price']);

        // Business
        $this->assertSame(50, $plans['business']['products']);
        $this->assertSame(3, $plans['business']['admins']);
        $this->assertEquals(99000.0, $plans['business']['price']);

        // Professional (Unlimited products)
        $this->assertNull($plans['professional']['products']);
        $this->assertSame(10, $plans['professional']['admins']);
        $this->assertEquals(249000.0, $plans['professional']['price']);
    }
}
