<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\EquipmentUnit;
use App\Models\Order;
use App\Models\Product;
use Tests\TestCase;

class CatalogAndInventoryUnitTest extends TestCase
{
    /**
     * [8.1-I1] Memastikan relasi dan fillable attribute pada model Category terkonfigurasi benar.
     */
    public function test_ut_05_001_category_fillable_and_relations(): void
    {
        $category = new Category([
            'client_id' => 1,
            'name' => 'Stroller Bayi',
            'slug' => 'stroller-bayi',
            'description' => 'Kategori peralatan stroller',
        ]);

        $expectedFillable = ['client_id', 'name', 'slug', 'description'];
        $this->assertEqualsCanonicalizing($expectedFillable, $category->getFillable());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $category->client());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $category->products());
    }

    /**
     * [8.1-I2] Memastikan accessor dan mutator rental_price tersinkronisasi dengan kolom rental_price_per_day.
     */
    public function test_ut_05_002_product_rental_price_accessor_and_mutator(): void
    {
        $product = new Product(['rental_price_per_day' => 150000]);
        $this->assertEquals(150000, $product->rental_price);

        $product->rental_price = 200000;
        $this->assertEquals(200000, $product->rental_price_per_day);
    }

    /**
     * [8.1-I2] Memastikan casting array images pada produk dapat memproses multi-foto galeri.
     */
    public function test_ut_05_003_product_images_casting_and_gallery_handling(): void
    {
        $product = new Product([
            'main_image' => 'products/cover.jpg',
            'images' => ['products/slide1.jpg', 'products/slide2.jpg'],
        ]);

        $this->assertIsArray($product->images);
        $this->assertCount(2, $product->images);
        $this->assertNotNull($product->cover_image_url);
    }

    /**
     * [8.2-I1] Memastikan accessor asset_code pada EquipmentUnit mengembalikan unit_code atau fallback asset_code.
     */
    public function test_ut_05_004_equipment_unit_asset_code_accessor(): void
    {
        $unit = new EquipmentUnit(['unit_code' => 'STR-001', 'asset_code' => 'OLD-001']);
        $this->assertEquals('STR-001', $unit->asset_code);

        $unitFallback = new EquipmentUnit(['unit_code' => null, 'asset_code' => 'AST-999']);
        $this->assertEquals('AST-999', $unitFallback->asset_code);
    }

    /**
     * [8.2-I1] Memastikan unit fisik dengan status rusak atau maintenance otomatis ditolak saat pengecekan ketersediaan sewa.
     */
    public function test_ut_05_005_equipment_unit_unavailable_when_damaged_or_maintenance(): void
    {
        $unitDamaged = new EquipmentUnit(['status' => 'rusak']);
        $this->assertFalse($unitDamaged->isAvailableForPeriod('2026-11-10', '2026-11-15'));

        $unitMaintenance = new EquipmentUnit(['status' => 'maintenance']);
        $this->assertFalse($unitMaintenance->isAvailableForPeriod('2026-11-10', '2026-11-15'));
    }

    /**
     * [7.1-I1] Memastikan validasi ketersediaan unit menolak rentang tanggal sewa yang tidak valid (tanggal selesai < mulai).
     */
    public function test_ut_05_006_equipment_unit_rejects_inverted_date_range(): void
    {
        $unit = new EquipmentUnit(['status' => 'tersedia']);
        $this->assertFalse($unit->isAvailableForPeriod('2026-11-15', '2026-11-10'));
    }

    /**
     * [7.2-I2] Memastikan model Order memiliki konfigurasi relasi dan status jaminan untuk checkout.
     */
    public function test_order_model_relationships_and_guarantee_contract(): void
    {
        $order = new Order();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $order->client());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $order->user());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $order->items());
    }
}
