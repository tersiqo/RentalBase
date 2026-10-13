<?php

namespace Tests\Unit;

use Tests\TestCase;

class PackageConfigTest extends TestCase
{
    public function test_packages_configuration_exists_and_has_all_plans(): void
    {
        $packages = config('packages');

        $this->assertIsArray($packages);
        $this->assertArrayHasKey('starter', $packages);
        $this->assertArrayHasKey('business', $packages);
        $this->assertArrayHasKey('professional', $packages);
    }

    public function test_package_names_are_correct(): void
    {
        $this->assertSame('Starter', config('packages.starter.name'));
        $this->assertSame('Business', config('packages.business.name'));
        $this->assertSame('Professional', config('packages.professional.name'));
    }

    public function test_package_limits_are_correct(): void
    {
        // Starter limits
        $this->assertSame(10, config('packages.starter.limits.products'));
        $this->assertSame(50, config('packages.starter.limits.units'));
        $this->assertSame(5, config('packages.starter.limits.categories'));
        $this->assertSame(1, config('packages.starter.limits.admins'));

        // Business limits
        $this->assertSame(50, config('packages.business.limits.products'));
        $this->assertSame(100, config('packages.business.limits.units'));
        $this->assertSame(20, config('packages.business.limits.categories'));
        $this->assertSame(3, config('packages.business.limits.admins'));

        // Professional limits (null represents unlimited per PRD)
        $this->assertNull(config('packages.professional.limits.products'));
        $this->assertNull(config('packages.professional.limits.units'));
        $this->assertNull(config('packages.professional.limits.categories'));
        $this->assertSame(10, config('packages.professional.limits.admins'));
    }

    public function test_package_features_are_correct(): void
    {
        $this->assertFalse(config('packages.starter.features.custom_theme'));
        $this->assertTrue(config('packages.business.features.custom_theme'));
        $this->assertTrue(config('packages.professional.features.custom_theme'));
    }
}
