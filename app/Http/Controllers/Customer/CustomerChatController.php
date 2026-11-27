<?php

namespace App\Http\Controllers\Customer;

use App\Events\ChatMessageSent;
use App\Events\ChatMessagesRead;
use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CustomerChatController extends Controller
{
    /**
     * Dapatkan atau buat thread percakapan aktif customer dengan toko saat ini.
     */
    public function getActiveConversation(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $client = $this->resolveClient($request);
        if (! $client) {
            return response()->json(['error' => 'Toko tidak ditemukan'], 404);
        }

        $conversation = ChatConversation::firstOrCreate(
            [
                'client_id'   => $client->id,
                'customer_id' => $user->id,
            ],
            [
                'last_message_at' => now(),
            ]
        );

        // Ambil 50 pesan terakhir
        $messages = $conversation->messages()
            ->with(['productContext', 'orderContext'])
            ->latest('id')
            ->take(50)
            ->get()
            ->reverse()
            ->values();

        // Tandai pesan sebagai dibaca untuk customer
        if ($conversation->unread_by_customer > 0) {
            $conversation->markAsReadFor('customer');
            $this->safeBroadcast(new ChatMessagesRead($conversation->id, 'customer'));
        }

        return response()->json([
            'conversation' => [
                'id'                 => $conversation->id,
                'client_id'          => $client->id,
                'store_name'         => $client->tenantProfile?->name ?? $client->nama_usaha,
                'store_logo'         => $client->logo_url,
                'store_subdomain'    => $client->subdomain,
                'unread_by_customer' => $conversation->unread_by_customer,
            ],
            'messages' => $messages->map(fn (ChatMessage $m) => $this->formatMessagePayload($m, $conversation)),
        ]);
    }

    /**
     * Ambil riwayat pesan dalam percakapan.
     */
    public function getMessages(Request $request, ChatConversation $conversation): JsonResponse
    {
        $user = Auth::user();
        if ((int) $conversation->customer_id !== (int) $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $messages = $conversation->messages()
            ->with(['productContext', 'orderContext'])
            ->latest('id')
            ->take(50)
            ->get()
            ->reverse()
            ->values();

        if ($conversation->unread_by_customer > 0) {
            $conversation->markAsReadFor('customer');
            $this->safeBroadcast(new ChatMessagesRead($conversation->id, 'customer'));
        }

        return response()->json([
            'messages' => $messages->map(fn (ChatMessage $m) => $this->formatMessagePayload($m, $conversation)),
        ]);
    }

    /**
     * Kirim pesan baru dari customer ke toko.
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'message'      => 'nullable|string|max:2000|required_without:attachment',
            'attachment'   => 'nullable|image|max:4096',
            'client_id'    => 'nullable|integer|exists:clients,id',
            'subdomain'    => 'nullable|string',
            'context_type' => 'nullable|in:product,order',
            'context_id'   => 'nullable|integer',
        ]);

        $client = null;
        if ($request->filled('client_id')) {
            $client = Client::find($request->client_id);
        }
        if (! $client) {
            $client = $this->resolveClient($request);
        }

        if (! $client) {
            return response()->json(['error' => 'Toko tidak ditemukan'], 404);
        }

        // Temukan atau buat percakapan
        $conversation = ChatConversation::firstOrCreate([
            'client_id'   => $client->id,
            'customer_id' => $user->id,
        ]);

        // Simpan lampiran foto jika ada
        $attachmentPath = null;
        if ($request->hasFile('attachment') && $request->file('attachment')->isValid()) {
            $disk = \App\Services\StorageHelper::disk();
            $attachmentPath = $request->file('attachment')->store('chat_attachments', $disk);
        }

        $chatMessage = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $user->id,
            'sender_role'     => 'customer',
            'message'         => trim((string) $request->message),
            'attachment_path' => $attachmentPath,
            'context_type'    => $request->context_type,
            'context_id'      => $request->context_id,
            'is_read'         => false,
        ]);

        $conversation->recordNewMessage($chatMessage);

        // Siarkan secara instan via WebSocket Reverb (0-delay)
        $this->safeBroadcast(new ChatMessageSent($chatMessage));

        // Kirim Web Push Notification ke seluruh perangkat Admin toko (latar belakang / browser tertutup)
        try {
            app(\App\Services\WebPushService::class)->sendToClientAdmins($client->id, [
                'title' => 'Pesan Baru dari ' . ($user->name ?? 'Penyewa'),
                'body'  => $chatMessage->message ?: 'Mengirim foto lampiran',
                'url'   => url('/admin/chat?conversation_id=' . $conversation->id),
                'type'  => 'chat',
                'tag'   => 'rb-chat-conv-' . $conversation->id,
            ]);
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'message' => $this->formatMessagePayload($chatMessage, $conversation),
        ], 201);
    }

    /**
     * Tandai pesan telah dibaca oleh customer.
     */
    public function markAsRead(Request $request, ChatConversation $conversation): JsonResponse
    {
        $user = Auth::user();
        if ((int) $conversation->customer_id !== (int) $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $conversation->markAsReadFor('customer');
        $this->safeBroadcast(new ChatMessagesRead($conversation->id, 'customer'));

        return response()->json(['success' => true]);
    }

    /**
     * Resolve toko rental aktif customer saat ini.
     */
    private function resolveClient(Request $request): ?Client
    {
        $client = null;
        if ($request->filled('subdomain')) {
            $client = Client::where('subdomain', $request->subdomain)->first();
        }
        if (! $client && $request->route('subdomain')) {
            $client = Client::where('subdomain', $request->route('subdomain'))->first();
        }
        if (! $client && $request->filled('client')) {
            $client = Client::where('subdomain', $request->query('client'))->first();
        }
        if (! $client && session()->has('active_client_id')) {
            $client = Client::find(session('active_client_id'));
        }
        if (! $client && session()->has('active_client_subdomain')) {
            $client = Client::where('subdomain', session('active_client_subdomain'))->first();
        }
        if (! $client && ($referer = $request->headers->get('referer'))) {
            if (preg_match('#/client/([^/?#]+)#', $referer, $matches)) {
                $client = Client::where('subdomain', $matches[1])->first();
            }
        }
        if (! $client) {
            $client = Client::where('status', 'aktif')->first() ?? Client::first();
        }

        if ($client) {
            session()->put('active_client_id', $client->id);
            session()->put('active_client_subdomain', $client->subdomain);
        }

        return $client;
    }

    /**
     * Format payload pesan untuk dikirim ke JSON frontend.
     */
    private function formatMessagePayload(ChatMessage $m, ChatConversation $conv): array
    {
        return [
            'id'              => $m->id,
            'conversation_id' => $m->conversation_id,
            'sender_id'       => $m->sender_id,
            'sender_role'     => $m->sender_role,
            'sender_name'     => $m->sender_role === 'admin'
                ? ($conv->client?->nama_usaha ?? 'Admin Toko')
                : ($m->sender?->name ?? 'Saya'),
            'sender_avatar'   => $m->sender_role === 'admin'
                ? $conv->client?->logo_url
                : $m->sender?->avatar_url,
            'message'         => $m->message,
            'attachment_url'  => $m->attachment_url,
            'context_data'    => $m->context_data,
            'is_read'         => (bool) $m->is_read,
            'read_at'         => $m->read_at?->toIso8601String(),
            'created_at'      => $m->created_at?->toIso8601String(),
            'time_formatted'  => $m->created_at?->format('H:i') ?? now()->format('H:i'),
        ];
    }

    /**
     * Siarkan event WebSocket secara aman tanpa memicu 500 error jika provider push bermasalah.
     */
    private function safeBroadcast(object $event): void
    {
        try {
            event($event);
        } catch (\Throwable $e) {
            Log::warning('[RentalBase LiveChat] Broadcast failed: ' . $e->getMessage());
        }
    }
}
