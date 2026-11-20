<?php

namespace Tests\Feature\Admin;

use App\Models\Client;
use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * MOD-04 — Modul Pengelolaan Toko (Admin Rental)
 * Fitur: Infrastruktur Web Push Background Notifications / PWA (ID: 9.1-A3)
 *
 * TINGKAT PENGUJIAN: INTEGRATION TESTING (Laravel HTTP Testing)
 * Runner: $this->actingAs(), $this->getJson(), $this->postJson()
 * Rentang ID: IT-04-042 s/d IT-04-052 (11 Skenario)
 */
class AdminWebPushNotificationTest extends TestCase
{
    use RefreshDatabase;

    private Client $store;
    private User $admin;
    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->store = Client::create([
            'business_name' => 'Baby Rental Studio',
            'subdomain'     => 'babyrental',
            'status'        => 'aktif',
        ]);

        $this->admin = User::create([
            'client_id' => $this->store->id,
            'name'      => 'Admin Budi',
            'email'     => 'budi@babyrental.com',
            'password'  => bcrypt('password123'),
            'role'      => 'admin_rental',
            'status'    => 'aktif',
        ]);

        $this->customer = User::create([
            'client_id' => $this->store->id,
            'name'      => 'Customer Biasa',
            'email'     => 'customer@gmail.com',
            'password'  => bcrypt('password123'),
            'role'      => 'customer',
            'status'    => 'aktif',
        ]);
    }

    /**
     * IT-04-042: Handshake VAPID Public Key mengembalikan respon HTTP 200 dengan payload JSON valid.
     */
    public function test_it_04_042_get_vapid_public_key_successful(): void
    {
        $response = $this->getJson('/webpush/vapid-public-key');

        $response->assertStatus(200);
        $response->assertJsonStructure(['publicKey']);
        $this->assertNotEmpty($response->json('publicKey'));
    }

    /**
     * IT-04-043: Admin rental sukses mendaftarkan push subscription browser (HTTP 200).
     */
    public function test_it_04_043_admin_can_subscribe_push_notification(): void
    {
        $mockEndpoint = 'https://fcm.googleapis.com/fcm/send/token-admin-123';

        $response = $this->actingAs($this->admin)->postJson('/webpush/subscribe', [
            'endpoint' => $mockEndpoint,
            'keys' => [
                'p256dh' => 'BNcRdreALRFXTkOOUHK1EtK2wtaz5Ry4YfYCA_0QT9AcUbVYOEkOMqWIKzp7',
                'auth'   => 'tBHItJI5svbpez7KI4CCXg',
            ],
            'content_encoding' => 'aes128gcm',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('push_subscriptions', [
            'user_id'       => $this->admin->id,
            'client_id'     => $this->store->id,
            'endpoint'      => $mockEndpoint,
            'endpoint_hash' => hash('sha256', $mockEndpoint),
        ]);
    }

    /**
     * IT-04-044: Re-subscribing dengan endpoint yang sama memperbarui data tanpa membuat record ganda.
     */
    public function test_it_04_044_re_subscribing_same_endpoint_does_not_create_duplicate(): void
    {
        $mockEndpoint = 'https://fcm.googleapis.com/fcm/send/fixed-token-123';
        $payload = [
            'endpoint' => $mockEndpoint,
            'keys' => [
                'p256dh' => 'key1',
                'auth'   => 'auth1',
            ],
        ];

        $this->actingAs($this->admin)->postJson('/webpush/subscribe', $payload)->assertStatus(200);
        $this->actingAs($this->admin)->postJson('/webpush/subscribe', $payload)->assertStatus(200);

        $this->assertEquals(1, PushSubscription::where('endpoint_hash', hash('sha256', $mockEndpoint))->count());
    }

    /**
     * IT-04-045: Validasi request menolak jika endpoint bukan format URL yang valid (HTTP 422).
     */
    public function test_it_04_045_subscribe_validation_rejects_invalid_url_endpoint(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/webpush/subscribe', [
            'endpoint' => 'bukan-url-valid',
            'keys' => [
                'p256dh' => 'key',
                'auth'   => 'auth',
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['endpoint']);
    }

    /**
     * IT-04-046: Validasi request menolak payload subscribe tanpa parameter keys yang wajib (HTTP 422).
     */
    public function test_it_04_046_subscribe_validation_rejects_missing_keys(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/webpush/subscribe', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/invalid-test',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['keys.p256dh', 'keys.auth']);
    }

    /**
     * IT-04-047: Proteksi otentikasi mencegah pengunjung tamu mendaftarkan push subscription (HTTP 401).
     */
    public function test_it_04_047_guest_cannot_subscribe_push_notification(): void
    {
        $response = $this->postJson('/webpush/subscribe', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/guest-endpoint',
            'keys' => [
                'p256dh' => 'key',
                'auth'   => 'auth',
            ],
        ]);

        $response->assertStatus(401);
    }

    /**
     * IT-04-048: Pengguna dapat melakukan unsubscribe token perangkat dari sistem (HTTP 200).
     */
    public function test_it_04_048_user_can_unsubscribe_push_notification(): void
    {
        $mockEndpoint = 'https://fcm.googleapis.com/fcm/send/token-unsub-999';

        PushSubscription::create([
            'user_id'       => $this->admin->id,
            'client_id'     => $this->store->id,
            'endpoint'      => $mockEndpoint,
            'endpoint_hash' => hash('sha256', $mockEndpoint),
        ]);

        $response = $this->actingAs($this->admin)->postJson('/webpush/unsubscribe', [
            'endpoint' => $mockEndpoint,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('push_subscriptions', [
            'endpoint' => $mockEndpoint,
        ]);
    }

    /**
     * IT-04-049: Unsubscribe menolak jika parameter endpoint kosong (HTTP 422).
     */
    public function test_it_04_049_unsubscribe_requires_endpoint(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/webpush/unsubscribe', []);

        $response->assertStatus(422);
        $response->assertJson(['message' => 'Endpoint diperlukan']);
    }

    /**
     * IT-04-050: Uji coba push memberikan respon bersih HTTP 400 jika belum ada perangkat terdaftar.
     */
    public function test_it_04_050_test_push_without_registered_devices_returns_clean_error(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/webpush/test');

        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    /**
     * IT-04-051: Pengunjung tamu dilarang mengakses endpoint uji coba push notification (HTTP 401).
     */
    public function test_it_04_051_guest_cannot_trigger_test_push(): void
    {
        $response = $this->postJson('/webpush/test');

        $response->assertStatus(401);
    }

    /**
     * IT-04-052: Header User-Agent panjang dipotong aman maksimal 255 karakter saat subscribe.
     */
    public function test_it_04_052_user_agent_length_is_safely_sanitized(): void
    {
        $longUserAgent = str_repeat('Mozilla/5.0 (Windows NT 10.0; Win64; x64) ', 10);
        $mockEndpoint = 'https://fcm.googleapis.com/fcm/send/token-ua-sanitize';

        $response = $this->actingAs($this->admin)
            ->withServerVariables(['HTTP_USER_AGENT' => $longUserAgent])
            ->postJson('/webpush/subscribe', [
                'endpoint' => $mockEndpoint,
                'keys' => [
                    'p256dh' => 'key',
                    'auth'   => 'auth',
                ],
            ]);

        $response->assertStatus(200);

        $subscription = PushSubscription::where('endpoint_hash', hash('sha256', $mockEndpoint))->first();
        $this->assertNotNull($subscription);
        $this->assertLessThanOrEqual(255, strlen($subscription->user_agent));
    }
}
