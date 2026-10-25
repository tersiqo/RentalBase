<?php

namespace Tests\Unit;

use App\Models\Client;
use App\Models\TenantProfile;
use Tests\TestCase;

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
        ];

        $this->assertEqualsCanonicalizing($expectedFillable, $profile->getFillable());
    }

    public function test_relationship_methods_return_belongs_to(): void
    {
        $profile = new TenantProfile();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $profile->client());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $profile->tenant());
        $this->assertSame('client_id', $profile->client()->getForeignKeyName());
        $this->assertSame('client_id', $profile->tenant()->getForeignKeyName());
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

        // Oranye RentalBase (#F97316) -> YIQ ~ 144.4 (>= 128)
        $profile = new TenantProfile(['theme_color' => '#F97316']);
        $this->assertSame('#000000', $profile->text_color);

        // Cyan / Sky Blue (#00FFFF)
        $profile = new TenantProfile(['theme_color' => '#00FFFF']);
        $this->assertSame('#000000', $profile->text_color);

        // 3 karakter hex terang (#EEE)
        $profile = new TenantProfile(['theme_color' => '#EEE']);
        $this->assertSame('#000000', $profile->text_color);
    }
}
