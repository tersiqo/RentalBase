<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Notification;
use App\Models\PushSubscription;
use App\Models\User;
use App\Services\WebPushService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebPushNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_vapid_public_key(): void
    {
        $response = $this->getJson('/webpush/vapid-public-key');

        $response->assertStatus(200);
        $response->assertJsonStructure(['publicKey']);
        $this->assertNotEmpty($response->json('publicKey'));

        $service = app(WebPushService::class);
        $rawPub = $service->base64UrlDecode($response->json('publicKey'));
        $this->assertEquals(65, strlen($rawPub));
        $this->assertEquals("\x04", $rawPub[0]);
    }

    public function test_authenticated_user_can_subscribe_to_webpush(): void
    {
        $user = User::create([
            'name' => 'Customer Test',
            'email' => 'customer.webpush@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        $mockEndpoint = 'https://fcm.googleapis.com/fcm/send/sample-token-' . uniqid();
        $mockKey = 'BNcRdreALRFXTkOOUHK1EtK2wtaz5Ry4YfYCA_0QT9AcUbVYOEkOMqWIKzp7';
        $mockAuth = 'tBHItJI5svbpez7KI4CCXg';

        $response = $this->actingAs($user)->postJson('/webpush/subscribe', [
            'endpoint' => $mockEndpoint,
            'keys' => [
                'p256dh' => $mockKey,
                'auth' => $mockAuth,
            ],
            'content_encoding' => 'aes128gcm',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('push_subscriptions', [
            'user_id' => $user->id,
            'endpoint' => $mockEndpoint,
            'endpoint_hash' => hash('sha256', $mockEndpoint),
        ]);
    }

    public function test_re_subscribing_same_endpoint_does_not_create_duplicate(): void
    {
        $user = User::create([
            'name' => 'Admin Test',
            'email' => 'admin.webpush@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        $mockEndpoint = 'https://fcm.googleapis.com/fcm/send/fixed-token-123';
        $payload = [
            'endpoint' => $mockEndpoint,
            'keys' => [
                'p256dh' => 'key1',
                'auth' => 'auth1',
            ],
        ];

        $this->actingAs($user)->postJson('/webpush/subscribe', $payload)->assertStatus(200);
        $this->actingAs($user)->postJson('/webpush/subscribe', $payload)->assertStatus(200);

        $this->assertEquals(1, PushSubscription::where('endpoint_hash', hash('sha256', $mockEndpoint))->count());
    }

    public function test_user_can_unsubscribe(): void
    {
        $user = User::create([
            'name' => 'Unsub Test',
            'email' => 'unsub.webpush@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'status' => 'aktif',
        ]);
        $mockEndpoint = 'https://fcm.googleapis.com/fcm/send/unsub-test';

        PushSubscription::create([
            'user_id' => $user->id,
            'endpoint' => $mockEndpoint,
            'endpoint_hash' => hash('sha256', $mockEndpoint),
            'public_key' => 'pub1',
            'auth_token' => 'auth1',
        ]);

        $this->assertDatabaseHas('push_subscriptions', ['endpoint_hash' => hash('sha256', $mockEndpoint)]);

        $response = $this->actingAs($user)->postJson('/webpush/unsubscribe', [
            'endpoint' => $mockEndpoint,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('push_subscriptions', ['endpoint_hash' => hash('sha256', $mockEndpoint)]);
    }

    public function test_test_push_returns_clean_feedback_when_no_subscription(): void
    {
        $user = User::create([
            'name' => 'No Sub Test',
            'email' => 'nosub.webpush@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($user)->postJson('/webpush/test');

        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    public function test_notification_creation_triggers_webpush_hook_cleanly(): void
    {
        $client = Client::create([
            'subdomain' => 'test-push-store',
            'business_name' => 'Toko Push',
            'status' => 'aktif',
        ]);

        $user = User::create([
            'name' => 'Admin Toko Push',
            'email' => 'admin.push@example.com',
            'password' => bcrypt('password'),
            'client_id' => $client->id,
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        // Trigger Notification::create()
        $notification = Notification::create([
            'client_id' => $client->id,
            'user_id' => $user->id,
            'title' => 'Pesanan Baru Masuk!',
            'message' => 'Pesanan #ORD-999 baru saja dibuat.',
            'type' => 'order',
            'is_read' => false,
        ]);

        $this->assertDatabaseHas('notifications', ['id' => $notification->id]);
    }

    public function test_web_push_service_generates_valid_p256_keys_and_rfc8291_encryption_roundtrip(): void
    {
        $service = app(WebPushService::class);
        $keys = $service->generateVapidKeys();

        $this->assertArrayHasKey('public_key', $keys);
        $this->assertArrayHasKey('private_key', $keys);

        $rawPub = $service->base64UrlDecode($keys['public_key']);
        $this->assertEquals(65, strlen($rawPub));

        // Buat simulasi keypair browser (User Agent)
        $uaKey = openssl_pkey_new([
            'curve_name' => 'prime256v1',
            'private_key_type' => OPENSSL_KEYTYPE_EC,
        ]);
        $uaDetails = openssl_pkey_get_details($uaKey);
        $uaPubRaw = "\x04"
            . str_pad($uaDetails['ec']['x'], 32, "\x00", STR_PAD_LEFT)
            . str_pad($uaDetails['ec']['y'], 32, "\x00", STR_PAD_LEFT);
        $authSecret = random_bytes(16);

        $plaintext = json_encode(['title' => 'Pesanan Baru!', 'body' => 'Ada pesanan masuk.']);
        $encrypted = $service->encryptAes128Gcm($plaintext, $uaPubRaw, $authSecret);

        // Header aes128gcm adalah 16 (salt) + 4 (rs) + 1 (idlen=65) + 65 (asPubRaw) = 86 byte
        $this->assertGreaterThan(86 + 16, strlen($encrypted));

        $salt = substr($encrypted, 0, 16);
        $idlen = ord($encrypted[20]);
        $this->assertEquals(65, $idlen);

        $asPubRaw = substr($encrypted, 21, 65);
        $cipherAndTag = substr($encrypted, 86);
        $ciphertext = substr($cipherAndTag, 0, -16);
        $tag = substr($cipherAndTag, -16);

        // Dekripsi dari sisi Browser (User Agent) untuk memastikan kesesuaian RFC 8291
        $spkiDer = hex2bin('3059301306072a8648ce3d020106082a8648ce3d030107034200') . $asPubRaw;
        $spkiPem = "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($spkiDer), 64, "\n") . "-----END PUBLIC KEY-----\n";
        $asPubKey = openssl_pkey_get_public($spkiPem);
        $ecdh = str_pad(openssl_pkey_derive($asPubKey, $uaKey, 32), 32, "\x00", STR_PAD_LEFT);

        $ikm = hash_hkdf('sha256', $ecdh, 32, "WebPush: info\x00" . $uaPubRaw . $asPubRaw, $authSecret);
        $cek = hash_hkdf('sha256', $ikm, 16, "Content-Encoding: aes128gcm\x00", $salt);
        $nonce = hash_hkdf('sha256', $ikm, 12, "Content-Encoding: nonce\x00", $salt);

        $decryptedRecord = openssl_decrypt($ciphertext, 'aes-128-gcm', $cek, OPENSSL_RAW_DATA, $nonce, $tag);
        $this->assertNotFalse($decryptedRecord);
        $this->assertEquals($plaintext . "\x02", $decryptedRecord);
    }

    public function test_vapid_subject_always_conforms_to_rfc8292(): void
    {
        config(['services.webpush.vapid.subject' => 'http://localhost:8000']);
        $service = app(WebPushService::class);

        $subject = $service->resolveVapidSubject();
        $this->assertTrue(str_starts_with($subject, 'mailto:') || str_starts_with($subject, 'https://'));
    }
}
