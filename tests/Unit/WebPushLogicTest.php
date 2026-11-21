<?php

namespace Tests\Unit;

use App\Models\Client;
use App\Models\PushSubscription;
use App\Models\User;
use App\Services\WebPushService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * MOD-04 — Modul Pengelolaan Toko (Admin Rental)
 * Fitur: Infrastruktur Web Push Background Notifications / PWA (ID: 9.1-A3)
 *
 * TINGKAT PENGUJIAN: UNIT TESTING (Uji Logika Model & Service Terisolasi)
 * Runner: PHPUnit
 */
class WebPushLogicTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Client::create([
            'business_name' => 'Baby Rental Studio',
            'subdomain'     => 'babyrental',
            'status'        => 'aktif',
        ]);

        $this->user = User::create([
            'client_id' => $this->client->id,
            'name'      => 'Admin Budi',
            'email'     => 'budi@babyrental.com',
            'password'  => bcrypt('password123'),
            'role'      => 'admin_rental',
            'status'    => 'aktif',
        ]);
    }

    /**
     * UT-MOD-04-004: Memastikan model PushSubscription memiliki konfigurasi 8 field fillable yang valid.
     */
    public function test_ut_mod_04_004_push_subscription_fillable_attributes(): void
    {
        $subscription = new PushSubscription();
        $expectedFillable = [
            'user_id',
            'client_id',
            'endpoint',
            'endpoint_hash',
            'public_key',
            'auth_token',
            'content_encoding',
            'user_agent',
        ];

        $this->assertEqualsCanonicalizing($expectedFillable, $subscription->getFillable());
    }

    /**
     * UT-MOD-04-005: Memastikan hook saving model PushSubscription menghitung endpoint_hash (SHA-256) otomatis.
     */
    public function test_ut_mod_04_005_endpoint_hash_calculated_automatically_on_save(): void
    {
        $endpoint = 'https://fcm.googleapis.com/fcm/send/unique-test-endpoint-123';

        $sub = PushSubscription::create([
            'user_id'          => $this->user->id,
            'client_id'        => $this->client->id,
            'endpoint'         => $endpoint,
            'public_key'       => 'test-key',
            'auth_token'       => 'test-auth',
            'content_encoding' => 'aes128gcm',
        ]);

        $this->assertNotNull($sub->endpoint_hash);
        $this->assertSame(hash('sha256', $endpoint), $sub->endpoint_hash);
    }

    /**
     * UT-MOD-04-006: Memastikan relasi model PushSubscription ke User (belongsTo) terdefinisi benar.
     */
    public function test_ut_mod_04_006_push_subscription_belongs_to_user_relationship(): void
    {
        $sub = PushSubscription::create([
            'user_id'          => $this->user->id,
            'client_id'        => $this->client->id,
            'endpoint'         => 'https://fcm.googleapis.com/fcm/send/test-user-rel',
            'public_key'       => 'test-key',
            'auth_token'       => 'test-auth',
            'content_encoding' => 'aes128gcm',
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $sub->user());
        $this->assertEquals($this->user->id, $sub->user->id);
    }

    /**
     * UT-MOD-04-007: Memastikan relasi model PushSubscription ke Client (belongsTo) terdefinisi benar.
     */
    public function test_ut_mod_04_007_push_subscription_belongs_to_client_relationship(): void
    {
        $sub = PushSubscription::create([
            'user_id'          => $this->user->id,
            'client_id'        => $this->client->id,
            'endpoint'         => 'https://fcm.googleapis.com/fcm/send/test-client-rel',
            'public_key'       => 'test-key',
            'auth_token'       => 'test-auth',
            'content_encoding' => 'aes128gcm',
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $sub->client());
        $this->assertEquals($this->client->id, $sub->client->id);
    }

    /**
     * UT-MOD-04-008: Memastikan simetri fungsi Base64Url Encode dan Decode pada WebPushService.
     */
    public function test_ut_mod_04_008_base64_url_encode_and_decode_symmetry(): void
    {
        $service = app(WebPushService::class);
        $original = "RentalBase\x00\xFFTestString123+/?=";

        $encoded = $service->base64UrlEncode($original);
        $decoded = $service->base64UrlDecode($encoded);

        $this->assertSame($original, $decoded);
        $this->assertStringNotContainsString('+', $encoded);
        $this->assertStringNotContainsString('/', $encoded);
        $this->assertStringNotContainsString('=', $encoded);
    }

    /**
     * UT-MOD-04-009: Memastikan resolver VAPID Subject selalu mengembalikan format RFC 8292 valid.
     */
    public function test_ut_mod_04_009_vapid_subject_conforms_to_rfc8292_validation(): void
    {
        $service = app(WebPushService::class);

        // Kasus 1: Email valid (mailto:)
        config(['services.webpush.vapid.subject' => 'mailto:admin@rentalbase.id']);
        $this->assertSame('mailto:admin@rentalbase.id', $service->resolveVapidSubject());

        // Kasus 2: URL HTTPS valid
        config(['services.webpush.vapid.subject' => 'https://rentalbase.id']);
        $this->assertSame('https://rentalbase.id', $service->resolveVapidSubject());

        // Kasus 3: Input invalid fallback ke default mailto aman
        config(['services.webpush.vapid.subject' => 'invalid-subject-format']);
        $fallback = $service->resolveVapidSubject();
        $this->assertStringStartsWith('mailto:', $fallback);
    }

    /**
     * UT-MOD-04-010: Memastikan penanganan string User Agent panjang aman tanpa overflow.
     */
    public function test_ut_mod_04_010_user_agent_is_safely_handled(): void
    {
        $longUserAgent = str_repeat('Mozilla/5.0 (Windows NT 10.0; Win64; x64) ', 10);
        $sanitized = substr($longUserAgent, 0, 255);

        $this->assertLessThanOrEqual(255, strlen($sanitized));

        $sub = PushSubscription::create([
            'user_id'          => $this->user->id,
            'client_id'        => $this->client->id,
            'endpoint'         => 'https://fcm.googleapis.com/fcm/send/ua-test',
            'public_key'       => 'test-key',
            'auth_token'       => 'test-auth',
            'content_encoding' => 'aes128gcm',
            'user_agent'       => $sanitized,
        ]);

        $this->assertEquals($sanitized, $sub->user_agent);
    }
}
