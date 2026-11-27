<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class WebPushService
{
    /**
     * Dapatkan kunci VAPID aktif (dari config, env, atau file storage).
     */
    public function getVapidKeys(): array
    {
        $configPublic = config('services.webpush.vapid.public_key');
        $configPrivate = config('services.webpush.vapid.private_key');

        if ($configPublic && $configPrivate) {
            return [
                'public_key' => trim($configPublic),
                'private_key' => str_replace('\n', "\n", trim($configPrivate)),
            ];
        }

        // Cek file storage lokal jika .env belum diisi
        $storageFile = storage_path('app/webpush_vapid.json');
        if (File::exists($storageFile)) {
            $data = json_decode(File::get($storageFile), true);
            if (is_array($data) && ! empty($data['public_key']) && ! empty($data['private_key'])) {
                return [
                    'public_key' => trim($data['public_key']),
                    'private_key' => str_replace('\n', "\n", trim($data['private_key'])),
                ];
            }
        }

        // Generate baru dan simpan ke file storage
        $keys = $this->generateVapidKeys();
        if (! File::isDirectory(dirname($storageFile))) {
            File::makeDirectory(dirname($storageFile), 0755, true);
        }
        File::put($storageFile, json_encode($keys, JSON_PRETTY_PRINT));

        return $keys;
    }

    /**
     * Generate sepasang kunci VAPID P-256 (NIST P-256 / prime256v1).
     */
    public function generateVapidKeys(): array
    {
        $key = openssl_pkey_new([
            'curve_name' => 'prime256v1',
            'private_key_type' => OPENSSL_KEYTYPE_EC,
        ]);

        openssl_pkey_export($key, $privatePem);
        $details = openssl_pkey_get_details($key);
        $x = str_pad($details['ec']['x'], 32, "\x00", STR_PAD_LEFT);
        $y = str_pad($details['ec']['y'], 32, "\x00", STR_PAD_LEFT);
        $publicRaw = "\x04" . $x . $y;

        return [
            'public_key' => $this->base64UrlEncode($publicRaw),
            'private_key' => $privatePem,
        ];
    }

    /**
     * Pastikan subject VAPID sesuai standar RFC 8292 (wajib mailto: atau https://).
     */
    public function resolveVapidSubject(): string
    {
        $subject = trim((string) config('services.webpush.vapid.subject', ''));
        if (str_starts_with($subject, 'mailto:') || str_starts_with($subject, 'https://')) {
            return $subject;
        }

        return 'mailto:admin@rentalbase.com';
    }

    /**
     * Kirim notifikasi Web Push ke satu subscription perangkat.
     */
    public function sendNotification(PushSubscription $subscription, array $payload): bool
    {
        try {
            $vapid = $this->getVapidKeys();
            $endpoint = $subscription->endpoint;
            $urlParts = parse_url($endpoint);
            if (! isset($urlParts['scheme'], $urlParts['host'])) {
                return false;
            }

            $origin = $urlParts['scheme'] . '://' . $urlParts['host'] . (isset($urlParts['port']) ? ':' . $urlParts['port'] : '');
            $subject = $this->resolveVapidSubject();

            // 1. Siapkan JSON payload
            $jsonPayload = json_encode([
                'title' => $payload['title'] ?? 'RentalBase',
                'body' => $payload['body'] ?? $payload['message'] ?? 'Notifikasi baru diterima.',
                'icon' => $payload['icon'] ?? asset('images/rentalbase-icon.svg'),
                'badge' => $payload['badge'] ?? asset('images/rentalbase-icon.svg'),
                'url' => $payload['url'] ?? $payload['action_url'] ?? '/',
                'tag' => $payload['tag'] ?? ('rb-' . ($payload['type'] ?? 'general') . '-' . time()),
                'data' => [
                    'url' => $payload['url'] ?? $payload['action_url'] ?? '/',
                    'time' => now()->toISOString(),
                ],
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            // 2. Decode user public key dan auth secret
            $receiverPubRaw = $this->base64UrlDecode($subscription->public_key);
            $authSecret = $this->base64UrlDecode($subscription->auth_token);

            if (strlen($receiverPubRaw) !== 65 || strlen($authSecret) < 16) {
                Log::warning('[WebPush] Format kunci browser tidak valid untuk subscription #' . $subscription->id);
                return false;
            }

            // 3. Enkripsi Payload menggunakan aes128gcm (RFC 8291 / RFC 8188)
            $encryptedBody = $this->encryptAes128Gcm($jsonPayload, $receiverPubRaw, $authSecret);

            // 4. Buat VAPID JWT (RFC 8292)
            $jwt = $this->createVapidJwt($origin, $subject, $vapid['private_key']);
            $authHeader = 'vapid t=' . $jwt . ', k=' . $vapid['public_key'];

            // 5. Kirim via cURL dengan timeout singkat
            $headers = [
                'Content-Type: application/octet-stream',
                'Content-Encoding: aes128gcm',
                'Content-Length: ' . strlen($encryptedBody),
                'TTL: 86400',
                'Urgency: high',
                'Authorization: ' . $authHeader,
            ];

            // Tambahkan header khusus Apple APNs untuk iOS / Safari agar notifikasi bangun instan (Priority 10)
            if (str_contains($endpoint, 'apple.com')) {
                $headers[] = 'apns-push-type: alert';
                $headers[] = 'apns-priority: 10';
            }

            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $encryptedBody,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 6,
                CURLOPT_CONNECTTIMEOUT => 4,
                CURLOPT_HTTP_VERSION => defined('CURL_HTTP_VERSION_2_0') ? CURL_HTTP_VERSION_2_0 : CURL_HTTP_VERSION_1_1,
                CURLOPT_HTTPHEADER => $headers,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                Log::warning('[WebPush] cURL error pada subscription #' . $subscription->id . ': ' . $curlError);
                return false;
            }

            // Jika status 404 Not Found, 410 Gone, atau VapidPkHashMismatch (kunci server berubah)
            if ($httpCode === 404 || $httpCode === 410 || ($httpCode === 400 && str_contains((string) $response, 'VapidPkHashMismatch'))) {
                Log::info('[WebPush] Auto-pruning invalid/expired subscription #' . $subscription->id . ' (HTTP ' . $httpCode . ')');
                $subscription->delete();
                return false;
            }

            if ($httpCode < 200 || $httpCode >= 300) {
                Log::warning('[WebPush] Push server returned HTTP ' . $httpCode . ' for subscription #' . $subscription->id . ': ' . substr((string) $response, 0, 200));
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('[WebPush] Gagal mengirim notifikasi #' . $subscription->id . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim notifikasi ke semua subscription milik user tertentu.
     */
    public function sendToUser(User|int $user, array $payload): int
    {
        $userId = $user instanceof User ? $user->id : (int) $user;
        $subscriptions = PushSubscription::where('user_id', $userId)->get();

        $successCount = 0;
        foreach ($subscriptions as $sub) {
            if ($this->sendNotification($sub, $payload)) {
                $successCount++;
            }
        }

        return $successCount;
    }

    /**
     * Kirim notifikasi ke semua Admin Rental di toko tertentu.
     */
    public function sendToClientAdmins(int $clientId, array $payload): int
    {
        $adminIds = User::where('role', 'admin_rental')
            ->where('client_id', $clientId)
            ->pluck('id');

        if ($adminIds->isEmpty()) {
            return 0;
        }

        $subscriptions = PushSubscription::whereIn('user_id', $adminIds)->get();

        $successCount = 0;
        foreach ($subscriptions as $sub) {
            if ($this->sendNotification($sub, $payload)) {
                $successCount++;
            }
        }

        return $successCount;
    }

    /**
     * Kirim notifikasi ke semua Owner Platform.
     */
    public function sendToOwner(array $payload): int
    {
        $ownerIds = User::where('role', 'owner')->pluck('id');
        if ($ownerIds->isEmpty()) {
            return 0;
        }

        $subscriptions = PushSubscription::whereIn('user_id', $ownerIds)->get();

        $successCount = 0;
        foreach ($subscriptions as $sub) {
            if ($this->sendNotification($sub, $payload)) {
                $successCount++;
            }
        }

        return $successCount;
    }

    /**
     * Enkripsi payload JSON dengan standar RFC 8291 (aes128gcm).
     */
    public function encryptAes128Gcm(string $plaintext, string $receiverPubRaw, string $authSecret): string
    {
        // 1. Buat pasangan kunci ephemeral lokal (AS - Application Server)
        $ephemeralKey = openssl_pkey_new([
            'curve_name' => 'prime256v1',
            'private_key_type' => OPENSSL_KEYTYPE_EC,
        ]);
        $ephDetails = openssl_pkey_get_details($ephemeralKey);
        $ephX = str_pad($ephDetails['ec']['x'], 32, "\x00", STR_PAD_LEFT);
        $ephY = str_pad($ephDetails['ec']['y'], 32, "\x00", STR_PAD_LEFT);
        $asPubRaw = "\x04" . $ephX . $ephY;

        // 2. Derive Shared Secret (ECDH)
        $spkiDer = hex2bin('3059301306072a8648ce3d020106082a8648ce3d030107034200') . $receiverPubRaw;
        $spkiPem = "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($spkiDer), 64, "\n") . "-----END PUBLIC KEY-----\n";
        $receiverPubKey = openssl_pkey_get_public($spkiPem);
        $ecdh = openssl_pkey_derive($receiverPubKey, $ephemeralKey, 32);
        $ecdh = str_pad($ecdh, 32, "\x00", STR_PAD_LEFT);

        // 3. IKM & PRK Derivation (RFC 8291 Section 3.3)
        $keyInfo = "WebPush: info\x00" . $receiverPubRaw . $asPubRaw;
        $ikm = hash_hkdf('sha256', $ecdh, 32, $keyInfo, $authSecret);

        // 4. Random Salt 16 bytes
        $salt = random_bytes(16);

        // 5. CEK dan Nonce (RFC 8188)
        $cek = hash_hkdf('sha256', $ikm, 16, "Content-Encoding: aes128gcm\x00", $salt);
        $nonce = hash_hkdf('sha256', $ikm, 12, "Content-Encoding: nonce\x00", $salt);

        // 6. Plaintext record padding (RFC 8188: last record diakhiri delimiter 0x02)
        $record = $plaintext . "\x02";
        $tag = '';
        $ciphertext = openssl_encrypt($record, 'aes-128-gcm', $cek, OPENSSL_RAW_DATA, $nonce, $tag);

        // 7. Binary format body:
        // salt (16) || rs (4) || idlen (1) || keyid (65 bytes asPubRaw) || ciphertext || tag (16)
        $rs = pack('N', 4096);
        $idlen = chr(strlen($asPubRaw));

        return $salt . $rs . $idlen . $asPubRaw . $ciphertext . $tag;
    }

    /**
     * Buat token VAPID JWT (RFC 8292) dengan tanda tangan ES256.
     */
    public function createVapidJwt(string $audience, string $subject, string $privateKeyPem): string
    {
        $header = $this->base64UrlEncode(json_encode(['typ' => 'JWT', 'alg' => 'ES256']));
        $payload = $this->base64UrlEncode(json_encode([
            'aud' => $audience,
            'exp' => time() + 43200, // 12 jam
            'sub' => $subject,
        ], JSON_UNESCAPED_SLASHES));

        $dataToSign = $header . '.' . $payload;
        $derSig = '';
        openssl_sign($dataToSign, $derSig, $privateKeyPem, OPENSSL_ALGO_SHA256);

        // Konversi signature ECDSA DER ke IEEE P1363 (raw r || s 64 bytes)
        $rawSig = $this->derToRawSignature($derSig);

        return $dataToSign . '.' . $this->base64UrlEncode($rawSig);
    }

    /**
     * Konversi ASN.1 DER ECDSA signature ke format raw 64 bytes (r || s).
     */
    protected function derToRawSignature(string $der): string
    {
        $pos = 2;
        if (ord($der[1]) & 0x80) {
            $pos += (ord($der[1]) & 0x7f);
        }

        // r
        $pos++;
        $rLen = ord($der[$pos++]);
        $r = substr($der, $pos, $rLen);
        $pos += $rLen;

        // s
        $pos++;
        $sLen = ord($der[$pos++]);
        $s = substr($der, $pos, $sLen);

        $r = ltrim($r, "\x00");
        $s = ltrim($s, "\x00");
        $r = str_pad($r, 32, "\x00", STR_PAD_LEFT);
        $s = str_pad($s, 32, "\x00", STR_PAD_LEFT);

        return $r . $s;
    }

    /**
     * Helper Base64-URL Encode (RFC 7515).
     */
    public function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Helper Base64-URL Decode (RFC 7515).
     */
    public function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
