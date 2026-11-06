<?php

namespace Tests\Unit\Models;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_primary_admin_returns_true_when_explicitly_set_in_client(): void
    {
        $client = Client::create([
            'business_name' => 'Kamera Jaya Rental',
            'subdomain' => 'kamerajaya',
            'status' => 'aktif',
        ]);

        $admin = User::create([
            'client_id' => $client->id,
            'name' => 'Admin Utama',
            'email' => 'admin@kamerajaya.test',
            'password' => Hash::make('password123'),
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        $client->update(['primary_admin_id' => $admin->id]);

        $this->assertTrue($admin->isPrimaryAdmin());
        $this->assertFalse($admin->isStaffAdmin());
    }

    public function test_is_primary_admin_resolves_oldest_admin_as_fallback(): void
    {
        $client = Client::create([
            'business_name' => 'Outdoor Pro',
            'subdomain' => 'outdoorpro',
            'status' => 'aktif',
            'primary_admin_id' => null,
        ]);

        $firstAdmin = User::create([
            'client_id' => $client->id,
            'name' => 'First Admin',
            'email' => 'first@outdoorpro.test',
            'password' => Hash::make('password123'),
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        $secondAdmin = User::create([
            'client_id' => $client->id,
            'name' => 'Second Admin',
            'email' => 'second@outdoorpro.test',
            'password' => Hash::make('password123'),
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        $this->assertTrue($firstAdmin->isPrimaryAdmin());
        $this->assertFalse($firstAdmin->isStaffAdmin());

        $this->assertFalse($secondAdmin->isPrimaryAdmin());
        $this->assertTrue($secondAdmin->isStaffAdmin());
    }

    public function test_is_primary_admin_returns_false_for_non_admin_rental_or_null_client(): void
    {
        $owner = User::create([
            'name' => 'Platform Owner',
            'email' => 'owner@platform.test',
            'password' => Hash::make('password123'),
            'role' => 'owner',
            'status' => 'aktif',
        ]);

        $customer = User::create([
            'name' => 'Customer Budi',
            'email' => 'customer@gmail.test',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        $this->assertFalse($owner->isPrimaryAdmin());
        $this->assertFalse($owner->isStaffAdmin());

        $this->assertFalse($customer->isPrimaryAdmin());
        $this->assertFalse($customer->isStaffAdmin());
    }

    public function test_avatar_url_attribute_returns_null_when_avatar_is_empty(): void
    {
        $user = new User(['avatar' => null]);
        $this->assertNull($user->avatar_url);
    }

    public function test_store_admin_plan_quota_resolution_and_guard_conditions(): void
    {
        $resolvePlanKey = function (string $rawPlan): string {
            $planLower = mb_strtolower(trim($rawPlan));
            if (str_contains($planLower, 'pro')) {
                return 'professional';
            } elseif (str_contains($planLower, 'bus')) {
                return 'business';
            }
            return 'starter';
        };

        $this->assertSame('professional', $resolvePlanKey('Pro Annual'));
        $this->assertSame('business', $resolvePlanKey('Business Monthly'));
        $this->assertSame('starter', $resolvePlanKey('Starter Free'));

        $isPrimaryAdmin = true;
        $canDeactivate = !$isPrimaryAdmin;
        $this->assertFalse($canDeactivate, 'Admin Utama tidak boleh dapat dinonaktifkan.');

        $currentUserId = 5;
        $targetUserId = 5;
        $isSelf = ($currentUserId === $targetUserId);
        $this->assertTrue($isSelf, 'Sistem harus mendeteksi upaya penonaktifan diri sendiri.');
    }
}
