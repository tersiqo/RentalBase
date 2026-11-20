<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use App\Services\WebPushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebPushController extends Controller
{
    public function __construct(
        protected WebPushService $webPushService
    ) {}

    /**
     * Mengambil VAPID Public Key untuk pendaftaran browser.
     */
    public function getPublicKey(): JsonResponse
    {
        $keys = $this->webPushService->getVapidKeys();

        return response()->json([
            'publicKey' => $keys['public_key'],
        ]);
    }

    /**
     * Simpan / perbarui subscription perangkat user.
     */
    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => 'required|url',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
            'content_encoding' => 'nullable|string|in:aes128gcm,aesgcm',
        ]);

        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $endpoint = $validated['endpoint'];
        $endpointHash = hash('sha256', $endpoint);

        $subscription = PushSubscription::updateOrCreate(
            ['endpoint_hash' => $endpointHash],
            [
                'user_id' => $user->id,
                'client_id' => $user->client_id ?? null,
                'endpoint' => $endpoint,
                'public_key' => $validated['keys']['p256dh'],
                'auth_token' => $validated['keys']['auth'],
                'content_encoding' => $validated['content_encoding'] ?? 'aes128gcm',
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi perangkat berhasil diaktifkan!',
            'subscription_id' => $subscription->id,
        ]);
    }

    /**
     * Hapus subscription perangkat saat user menonaktifkan notifikasi.
     */
    public function unsubscribe(Request $request): JsonResponse
    {
        $endpoint = $request->input('endpoint');
        if (! $endpoint) {
            return response()->json(['message' => 'Endpoint diperlukan'], 422);
        }

        $hash = hash('sha256', $endpoint);
        PushSubscription::where('endpoint_hash', $hash)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi perangkat telah dinonaktifkan.',
        ]);
    }

    /**
     * Kirim notifikasi uji coba ke perangkat user yang sedang login.
     */
    public function testPush(): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $subscriptions = $user->pushSubscriptions;
        if ($subscriptions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada perangkat terdaftar. Silakan aktifkan notifikasi di browser ini terlebih dahulu.',
            ], 400);
        }

        $sentCount = $this->webPushService->sendToUser($user, [
            'title' => 'Uji Coba Notifikasi RentalBase',
            'body' => 'Mantap! Notifikasi perangkat background Anda berhasil aktif dan siap menerima pemberitahuan.',
            'url' => url()->previous() ?: '/',
            'tag' => 'rb-test-' . time(),
        ]);

        return response()->json([
            'success' => $sentCount > 0,
            'message' => $sentCount > 0
                ? "Notifikasi uji coba berhasil dikirim ke {$sentCount} perangkat Anda!"
                : 'Gagal mengirim notifikasi. Pastikan izin notifikasi di browser tidak diblokir.',
            'sent_count' => $sentCount,
        ]);
    }
}
