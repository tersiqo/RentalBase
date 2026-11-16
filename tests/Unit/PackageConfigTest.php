<?php

namespace Tests\Unit;

use Tests\TestCase;

/**
 * ============================================================================
 * UNIT TEST: UT-MOD-03 / UT-MOD-04 (TENANT QUOTA LIMITS & PACKAGE CONFIG)
 * ============================================================================
 * PIC: Fiza
 * Target: config/packages.php & Logika Batas Kuota Toko (Quota Exceeded)
 * Cakupan:
 * 1. Definisi Paket (Starter, Business, Professional)
 * 2. Limit Kuota: Produk, Unit Fisik, Kategori, Admin Rental
 * 3. Logika Perbandingan Pemakaian (Used Items vs Limit Quota / Quota Exceeded)
 * 4. Resolusi Kuota Unlimited (null) vs Numerik
 * ============================================================================
 */
class PackageConfigTest extends TestCase
{
    public function test_packages_configuration_exists_and_has_all_plans(): void
    {
        $packages = config('packages');

        $this->assertIsArray($packages);
        $this->assertArrayHasKey('starter', $packages);
        $this->assertArrayHasKey('business', $packages);
        $this->assertArrayHasKey('professional', $packages);
        $this->assertArrayHasKey('reserved_subdomains', $packages);
    }

    public function test_package_names_are_correct(): void
    {
        $this->assertSame('Starter', config('packages.starter.name'));
        $this->assertSame('Business', config('packages.business.name'));
        $this->assertSame('Professional', config('packages.professional.name'));
    }

    /**
     * [Case 1] Paket 'Starter':
     * Batas produk = 10, unit = 50, kategori = 5, admin = 1, fitur custom theme = false.
     * Harga = 0, durasi = 3 bulan.
     */
    public function test_starter_package_quota_limits_and_features(): void
    {
        $starter = config('packages.starter');

        $this->assertSame('Starter', $starter['name']);
        $this->assertSame(0, $starter['price']);
        $this->assertSame(3, $starter['duration_months']);

        // Batas limit Starter
        $this->assertSame(10, $starter['limits']['products']);
        $this->assertSame(50, $starter['limits']['units']);
        $this->assertSame(5, $starter['limits']['categories']);
        $this->assertSame(1, $starter['limits']['admins']);

        // Fitur Starter
        $this->assertFalse($starter['features']['custom_theme']);
    }

    /**
     * [Case 2] Paket 'Business':
     * Batas produk = 50, unit = 100, kategori = 20, admin = 3, fitur custom theme = true.
     * Harga = 99000, durasi = 6 bulan.
     */
    public function test_business_package_quota_limits_and_features(): void
    {
        $business = config('packages.business');

        $this->assertSame('Business', $business['name']);
        $this->assertSame(99000, $business['price']);
        $this->assertSame(6, $business['duration_months']);

        // Batas limit Business
        $this->assertSame(50, $business['limits']['products']);
        $this->assertSame(100, $business['limits']['units']);
        $this->assertSame(20, $business['limits']['categories']);
        $this->assertSame(3, $business['limits']['admins']);

        // Fitur Business
        $this->assertTrue($business['features']['custom_theme']);
    }

    /**
     * [Case 3] Paket 'Professional':
     * Batas produk = null (unlimited), unit = null (unlimited), kategori = null (unlimited), admin = 10.
     * Fitur custom theme = true, Harga = 249000, durasi = 12 bulan.
     */
    public function test_professional_package_quota_limits_and_features(): void
    {
        $professional = config('packages.professional');

        $this->assertSame('Professional', $professional['name']);
        $this->assertSame(249000, $professional['price']);
        $this->assertSame(12, $professional['duration_months']);

        // Batas limit Professional (null = unlimited sesuai PRD & spesifikasi)
        $this->assertNull($professional['limits']['products']);
        $this->assertNull($professional['limits']['units']);
        $this->assertNull($professional['limits']['categories']);
        $this->assertSame(10, $professional['limits']['admins']);

        // Fitur Professional
        $this->assertTrue($professional['features']['custom_theme']);
    }

    /**
     * [Case 4a] Logika Perbandingan Pemakaian (Used vs Limit) untuk Limit Numerik.
     * - used < limit: Kuota belum penuh (bisa tambah).
     * - used == limit: Kuota penuh (quota reached).
     * - used > limit: Kuota terlampaui (quota exceeded).
     */
    public function test_quota_exceeded_logic_for_numeric_limits(): void
    {
        $limitProducts = config('packages.starter.limits.products'); // 10
        $limitAdmins   = config('packages.starter.limits.admins');   // 1

        // 1. Kondisi penggunaan di bawah kuota (Under limit)
        $usedProducts = 7;
        $this->assertTrue($this->canCreateItem($usedProducts, $limitProducts));
        $this->assertSame(3, $this->calculateRemainingQuota($usedProducts, $limitProducts));
        $this->assertFalse($this->isQuotaExceeded($usedProducts, $limitProducts));

        // 2. Kondisi kuota pas mencapai limit (At limit)
        $usedProducts = 10;
        $this->assertFalse($this->canCreateItem($usedProducts, $limitProducts));
        $this->assertSame(0, $this->calculateRemainingQuota($usedProducts, $limitProducts));
        $this->assertTrue($this->isQuotaExceeded($usedProducts, $limitProducts));

        // 3. Kondisi penggunaan melebihi limit (Over limit / exceeded)
        $usedProducts = 12;
        $this->assertFalse($this->canCreateItem($usedProducts, $limitProducts));
        $this->assertSame(0, $this->calculateRemainingQuota($usedProducts, $limitProducts));
        $this->assertTrue($this->isQuotaExceeded($usedProducts, $limitProducts));

        // 4. Kuota admin starter (hanya 1)
        $this->assertTrue($this->canCreateItem(0, $limitAdmins));
        $this->assertFalse($this->canCreateItem(1, $limitAdmins));
        $this->assertTrue($this->isQuotaExceeded(1, $limitAdmins));
    }

    /**
     * [Case 4b] Logika Perbandingan Pemakaian (Used vs Limit) untuk Limit Unlimited (null).
     * Pada paket Professional, kuota produk/unit/kategori bernilai null (unlimited).
     * Berapapun item yang telah digunakan, tidak boleh terdeteksi melebihi batas (never exceeded).
     */
    public function test_quota_exceeded_logic_for_unlimited_null_limits(): void
    {
        $limitProducts = config('packages.professional.limits.products');   // null
        $limitUnits    = config('packages.professional.limits.units');      // null
        $limitCategories = config('packages.professional.limits.categories'); // null

        $this->assertNull($limitProducts);
        $this->assertNull($limitUnits);
        $this->assertNull($limitCategories);

        // Simulasi jumlah pemakaian produk dalam jumlah besar
        $usedCounts = [0, 50, 100, 500, 9999];

        foreach ($usedCounts as $count) {
            $this->assertTrue(
                $this->canCreateItem($count, $limitProducts),
                "Pemakaian {$count} item seharusnya diizinkan pada paket unlimited."
            );
            $this->assertFalse(
                $this->isQuotaExceeded($count, $limitProducts),
                "Pemakaian {$count} item tidak boleh terdeteksi quota exceeded pada paket unlimited."
            );
            $this->assertNull(
                $this->calculateRemainingQuota($count, $limitProducts),
                "Sisa kuota pada paket unlimited harus bernilai null."
            );
        }
    }

    /**
     * Validasi resolusi fallback nama paket (case-insensitive & alias).
     */
    public function test_plan_resolution_from_various_inputs_and_fallbacks(): void
    {
        $this->assertSame('starter', $this->normalizePlanKey('starter'));
        $this->assertSame('starter', $this->normalizePlanKey('Starter'));
        $this->assertSame('starter', $this->normalizePlanKey('STARTER'));
        $this->assertSame('business', $this->normalizePlanKey('business'));
        $this->assertSame('business', $this->normalizePlanKey('Business'));
        $this->assertSame('professional', $this->normalizePlanKey('professional'));
        $this->assertSame('professional', $this->normalizePlanKey('pro'));
        $this->assertSame('starter', $this->normalizePlanKey('unknown_plan'));
        $this->assertSame('starter', $this->normalizePlanKey(null));
    }

    /**
     * Validasi reserved subdomains agar rute core platform terlindungi dari pendaftaran tenant.
     */
    public function test_reserved_subdomains_protect_critical_system_routes(): void
    {
        $reserved = config('packages.reserved_subdomains');

        $this->assertIsArray($reserved);

        $criticalRoutes = ['admin', 'owner', 'api', 'login', 'register', 'dashboard', 'billing', 'payment'];
        foreach ($criticalRoutes as $route) {
            $this->assertContains($route, $reserved, "Subdomain '{$route}' harus termasuk dalam reserved_subdomains.");
        }
    }

    // =========================================================================
    // HELPER LOGIKA KUOTA DOMAIN RENTALBASE
    // =========================================================================

    private function canCreateItem(int $usedCount, ?int $limit): bool
    {
        if ($limit === null) {
            return true; // Unlimited
        }

        return $usedCount < $limit;
    }

    private function isQuotaExceeded(int $usedCount, ?int $limit): bool
    {
        if ($limit === null) {
            return false; // Unlimited never exceeded
        }

        return $usedCount >= $limit;
    }

    private function calculateRemainingQuota(int $usedCount, ?int $limit): ?int
    {
        if ($limit === null) {
            return null; // Unlimited
        }

        return max(0, $limit - $usedCount);
    }

    private function normalizePlanKey(?string $planName): string
    {
        $plan = mb_strtolower(trim((string) $planName));

        if (str_contains($plan, 'pro')) {
            return 'professional';
        }
        if (str_contains($plan, 'bus')) {
            return 'business';
        }
        if (str_contains($plan, 'start')) {
            return 'starter';
        }

        return config("packages.{$plan}") ? $plan : 'starter';
    }
}
