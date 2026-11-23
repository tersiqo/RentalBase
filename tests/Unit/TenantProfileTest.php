<?php

namespace Tests\Unit;

use App\Models\Client;
use App\Models\Product;
use App\Models\TenantProfile;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\TestCase;

/**
 * ============================================================================
 * UNIT TEST: UT-MOD-04 (TENANT PROFILE & THEME COLOR CONTRAST)
 * ============================================================================
 */
class TenantProfileTest extends TestCase
{
    public function test_fillable_attributes_are_properly_configured(): void
    {
        $profile = new TenantProfile();

        $expectedFillable = [
            'client_id',
            'name',
            'description',
            'address',
            'city',
            'whatsapp',
            'subdomain',
            'logo_path',
            'theme_color',
            'hero_showcase_mode',
            'hero_featured_product_id',
            'maps_url',
        ];

        $this->assertEqualsCanonicalizing($expectedFillable, $profile->getFillable());
    }

    public function test_relationship_methods_return_belongs_to(): void
    {
        $profile = new TenantProfile();

        $this->assertInstanceOf(BelongsTo::class, $profile->client());
        $this->assertInstanceOf(BelongsTo::class, $profile->tenant());
        $this->assertSame('client_id', $profile->client()->getForeignKeyName());
        $this->assertSame('client_id', $profile->tenant()->getForeignKeyName());
        $this->assertInstanceOf(Client::class, $profile->client()->getRelated());
    }

    public function test_hero_featured_product_relationship_returns_belongs_to(): void
    {
        $profile = new TenantProfile();

        $relation = $profile->heroFeaturedProduct();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertSame('hero_featured_product_id', $relation->getForeignKeyName());
        $this->assertInstanceOf(Product::class, $relation->getRelated());
    }

    public function test_tenant_profile_appends_and_casts_configuration(): void
    {
        $profile = new TenantProfile();

        $this->assertContains('text_color', $profile->getAppends());

        $profileWithData = new TenantProfile([
            'client_id' => '10',
            'hero_featured_product_id' => '25',
        ]);

        $this->assertSame(10, $profileWithData->client_id);
        $this->assertSame(25, $profileWithData->hero_featured_product_id);
    }

    public function test_get_text_color_attribute_calculates_yiq_for_dark_colors(): void
    {
        // Hitam pekat
        $profile = new TenantProfile(['theme_color' => '#000000']);
        $this->assertSame('#ffffff', $profile->text_color);

        // Biru tua / navy (#0F172A)
        $profile = new TenantProfile(['theme_color' => '#0F172A']);
        $this->assertSame('#ffffff', $profile->text_color);

        // Merah marun (#800000)
        $profile = new TenantProfile(['theme_color' => '#800000']);
        $this->assertSame('#ffffff', $profile->text_color);

        // Oranye RentalBase (#F97316) -> YIQ ~ 144.4 (< 150 threshold, teks putih bersih)
        $profile = new TenantProfile(['theme_color' => '#F97316']);
        $this->assertSame('#ffffff', $profile->text_color);

        // Hex tanpa tanda '#'
        $profile = new TenantProfile(['theme_color' => '1E3A8A']);
        $this->assertSame('#ffffff', $profile->text_color);

        // 3 karakter hex gelap (#111)
        $profile = new TenantProfile(['theme_color' => '#111']);
        $this->assertSame('#ffffff', $profile->text_color);
    }

    public function test_get_text_color_attribute_calculates_yiq_for_light_colors(): void
    {
        // Putih (#FFFFFF)
        $profile = new TenantProfile(['theme_color' => '#FFFFFF']);
        $this->assertSame('#000000', $profile->text_color);

        // Kuning terang (#FFFF00)
        $profile = new TenantProfile(['theme_color' => '#FFFF00']);
        $this->assertSame('#000000', $profile->text_color);

        // Cyan / Sky Blue (#00FFFF)
        $profile = new TenantProfile(['theme_color' => '#00FFFF']);
        $this->assertSame('#000000', $profile->text_color);

        // 3 karakter hex terang (#EEE)
        $profile = new TenantProfile(['theme_color' => '#EEE']);
        $this->assertSame('#000000', $profile->text_color);
    }

    public function test_get_text_color_attribute_defaults_to_white_when_theme_color_is_null(): void
    {
        // Default color adalah #F97316 (RentalBase orange, YIQ < 150) -> menghasilkan teks putih
        $profile = new TenantProfile(['theme_color' => null]);
        $this->assertSame('#ffffff', $profile->text_color);
    }

    public function test_get_text_color_attribute_fallback_when_hex_is_invalid(): void
    {
        // Non-hex strings atau format salah
        $invalidColors = ['invalid', '#12', '#GGGGGG', '', 'abcde'];

        foreach ($invalidColors as $color) {
            $profile = new TenantProfile(['theme_color' => $color]);
            $this->assertSame('#ffffff', $profile->text_color, "Format '{$color}' harus fallback ke teks putih #ffffff.");
        }
    }
}
