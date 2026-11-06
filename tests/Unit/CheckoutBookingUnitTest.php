<?php

namespace Tests\Unit;

use App\Models\IdentityGuarantee;
use App\Models\Order;
use Tests\TestCase;

class CheckoutBookingUnitTest extends TestCase
{
    /**
     * [7.2-I2] Memastikan kontrak relasi model Order terhubung dengan Client, User, dan OrderItems.
     */
    public function test_ut_07_001_order_model_relationships(): void
    {
        $order = new Order();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $order->client());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $order->user());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $order->items());
    }

    /**
     * [7.2-I2] Memastikan data jaminan identitas KTP terdaftar pada array fillable model.
     */
    public function test_ut_07_002_identity_guarantee_fillable_attributes(): void
    {
        $guarantee = new IdentityGuarantee();
        $expectedFillable = [
            'order_id',
            'customer_id',
            'full_name',
            'identity_number',
            'identity_document_image',
            'face_image',
            'id_card_photo',
            'selfie_photo',
            'identity_address',
            'status',
            'reviewed_by',
            'deposited_at',
            'returned_at',
        ];

        $this->assertEqualsCanonicalizing($expectedFillable, $guarantee->getFillable());
    }

    /**
     * [7.2-I2] Memastikan accessor foto KTP menghasilkan link storage publik.
     */
    public function test_ut_07_003_identity_guarantee_id_card_photo_url_accessor(): void
    {
        $guarantee = new IdentityGuarantee(['id_card_photo' => 'ktp/nik_user.jpg']);

        $this->assertNotNull($guarantee->id_card_photo_url);
        $this->assertStringContainsString('ktp/nik_user.jpg', $guarantee->id_card_photo_url);
    }

    /**
     * [7.2-I2] Memastikan accessor foto selfie menghasilkan link storage publik.
     */
    public function test_ut_07_004_identity_guarantee_selfie_photo_url_accessor(): void
    {
        $guarantee = new IdentityGuarantee(['selfie_photo' => 'selfie/face_user.jpg']);

        $this->assertNotNull($guarantee->selfie_photo_url);
        $this->assertStringContainsString('selfie/face_user.jpg', $guarantee->selfie_photo_url);
    }
}
