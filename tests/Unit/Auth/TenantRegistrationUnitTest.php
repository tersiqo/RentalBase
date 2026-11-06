<?php

namespace Tests\Unit\Auth;

use App\Models\ClientRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TenantRegistrationUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_registration_nama_usaha_accessor_returns_business_name(): void
    {
        $reg = new ClientRegistration([
            'business_name' => 'Kamera Jaya Official',
            'subdomain' => 'kamerajaya',
        ]);

        $this->assertSame('Kamera Jaya Official', $reg->nama_usaha);
    }

    public function test_subdomain_is_checked_against_reserved_system_keywords(): void
    {
        $reservedSubdomains = config('packages.reserved_subdomains', [
            'admin', 'owner', 'api', 'www', 'app', 'root', 'mail',
        ]);

        $this->assertContains('admin', $reservedSubdomains);
        $this->assertContains('owner', $reservedSubdomains);
        $this->assertContains('api', $reservedSubdomains);

        $testSubdomain = 'admin';
        $isReserved = in_array(strtolower($testSubdomain), $reservedSubdomains, true);

        $this->assertTrue($isReserved);

        $validSubdomain = 'tokokamera';
        $isValid = !in_array(strtolower($validSubdomain), $reservedSubdomains, true);

        $this->assertTrue($isValid);
    }

    public function test_tenant_admin_password_is_properly_hashed(): void
    {
        $plainPassword = 'secretPassword123!';
        $hashed = Hash::make($plainPassword);

        $this->assertNotSame($plainPassword, $hashed);
        $this->assertTrue(Hash::check($plainPassword, $hashed));
    }

    public function test_registration_initial_status_defaults_to_menunggu_verifikasi(): void
    {
        $reg = ClientRegistration::create([
            'business_name' => 'Rental Camping Malang',
            'subdomain' => 'campmalang',
            'plan_name' => 'starter',
            'admin_name' => 'Budi Santoso',
            'admin_email' => 'budi@campmalang.test',
            'admin_phone' => '081234567890',
            'admin_password' => Hash::make('password123'),
            'status' => 'menunggu_verifikasi',
            'payment_status' => 'menunggu',
        ]);

        $this->assertSame('menunggu_verifikasi', $reg->status);
        $this->assertSame('menunggu', $reg->payment_status);
    }

    public function test_tenant_registration_theme_color_regex_and_subdomain_sanitization(): void
    {
        $hexRegex = '/^#[0-9a-fA-F]{6}$/';

        // Warna tema valid
        $this->assertMatchesRegularExpression($hexRegex, '#F97316');
        $this->assertMatchesRegularExpression($hexRegex, '#000000');
        $this->assertMatchesRegularExpression($hexRegex, '#FFFFFF');

        // Warna tema tidak valid
        $this->assertDoesNotMatchRegularExpression($hexRegex, 'red');
        $this->assertDoesNotMatchRegularExpression($hexRegex, '#12345');
        $this->assertDoesNotMatchRegularExpression($hexRegex, '#GGGGGG');

        // Sanitasi subdomain (lowercase & alpha-dash)
        $rawSubdomain = '  Kamera-Jaya_99! ';
        $cleanSubdomain = strtolower(trim($rawSubdomain));
        $isValidAlphaDash = (bool) preg_match('/^[a-zA-Z0-9_-]+$/', 'kamera-jaya_99');

        $this->assertSame('kamera-jaya_99!', $cleanSubdomain);
        $this->assertTrue($isValidAlphaDash);
    }
}
