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
}
