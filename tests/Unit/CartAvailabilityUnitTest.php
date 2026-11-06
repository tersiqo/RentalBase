<?php

namespace Tests\Unit;

use App\Models\EquipmentUnit;
use App\Models\Product;
use Tests\TestCase;

class CartAvailabilityUnitTest extends TestCase
{
    /**
     * [7.2-I1] Memastikan unit fisik berstatus 'tersedia' mengembalikan nilai true saat dicek periode sewa valid.
     */
    public function test_ut_06_001_equipment_unit_is_available_for_valid_period(): void
    {
        $unit = new EquipmentUnit(['status' => 'tersedia']);

        $this->assertTrue($unit->isAvailableForPeriod('2026-12-01', '2026-12-05'));
    }

    /**
     * [7.2-I1] Memastikan sinkronisasi status fisik unit tidak menimpa status unit yang sedang rusak.
     */
    public function test_ut_06_002_sync_physical_status_preserves_damaged_status(): void
    {
        $unit = new EquipmentUnit(['status' => 'rusak']);
        $unit->syncPhysicalStatus();

        $this->assertEquals('rusak', $unit->status);
    }

    /**
     * [7.2-I1] Memastikan relasi model Product ke master unit fisik (EquipmentUnit) terhubung via HasMany.
     */
    public function test_ut_06_003_product_units_relationship(): void
    {
        $product = new Product();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $product->units());
    }
}
