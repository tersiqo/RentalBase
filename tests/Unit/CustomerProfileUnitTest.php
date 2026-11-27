<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerProfileUnitTest extends TestCase
{
    /**
     * Memastikan accessor avatar_url menghasilkan URL storage saat foto tersimpan.
     */
    public function test_ut_01_001_avatar_url_accessor_returns_storage_url_when_avatar_present(): void
    {
        $user = new User(['avatar' => 'avatars/customer.jpg']);

        $this->assertNotNull($user->avatar_url);
        $this->assertStringContainsString('avatars/customer.jpg', $user->avatar_url);
    }

    /**
     * Memastikan accessor avatar_url mengembalikan null saat belum ada foto profil.
     */
    public function test_ut_01_002_avatar_url_accessor_returns_null_when_avatar_is_null(): void
    {
        $user = new User(['avatar' => null]);

        $this->assertNull($user->avatar_url);
    }

    /**
     * Memastikan field profil customer (name, phone, address, avatar) ada di array fillable.
     */
    public function test_ut_01_003_customer_profile_attributes_are_fillable(): void
    {
        $user = new User();
        $fillable = $user->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('phone', $fillable);
        $this->assertContains('address', $fillable);
        $this->assertContains('avatar', $fillable);
    }

    /**
     * Memastikan verifikasi hash password profil cocok dengan password plaintext.
     */
    public function test_ut_01_004_password_is_properly_hashed_and_verified(): void
    {
        $plainPassword = 'newsecretpass123';
        $hashed = Hash::make($plainPassword);

        $this->assertTrue(Hash::check($plainPassword, $hashed));
    }

    /**
     * [9.2-I1] Memastikan user dengan role 'customer' mengembalikan false pada pengecekan admin toko.
     */
    public function test_ut_01_011_customer_role_returns_false_for_admin_checks(): void
    {
        $customer = new User(['role' => 'customer', 'client_id' => null]);

        $this->assertFalse($customer->isPrimaryAdmin());
        $this->assertFalse($customer->isStaffAdmin());
    }

    /**
     * [9.2-I1] Memastikan casts password pada model User dikonfigurasi ke hashed.
     */
    public function test_ut_01_012_user_password_is_cast_to_hashed(): void
    {
        $user = new User(['password' => 'secret123']);

        $this->assertArrayHasKey('password', $user->getCasts());
        $this->assertEquals('hashed', $user->getCasts()['password']);
    }
}
