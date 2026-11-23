<?php

namespace App\Http\Controllers\Admin;

use App\Events\ChatMessageSent;
use App\Events\ChatMessagesRead;
use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Client;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AdminChatController extends Controller
{
    /**
     * Halaman Inbox Obrolan Toko (Split-Pane Interface).
     */
    public function index(Request $request): View
    {
        $client = $this->resolveClient();

        $conversations = ChatConversation::where('client_id', $client->id)
            ->with(['customer', 'latestMessage'])
            ->orderBy('last_message_at', 'desc')
            ->get();

        $activeConversationId = (int) $request->query('conversation_id');
        $activeConversation = $conversations->firstWhere('id', $activeConversationId) ?? $conversations->first();

        $activeOrders = collect();
        if ($activeConversation) {
            $activeOrders = Order::where('client_id', $client->id)
                ->where('user_id', $activeConversation->customer_id)
                ->whereNotIn('status', ['batal', 'dibatalkan', 'ditolak'])
                ->with(['orderItems.product'])
                ->latest()
                ->take(3)
                ->get();

            // Tandai sudah dibaca oleh admin jika obrolan langsung dibuka
            if ($activeConversation->unread_by_admin > 0 || $activeConversation->messages()->where('sender_role', 'customer')->where('is_read', false)->exists()) {
                $activeConversation->markAsReadFor('admin');
                $activeConversation->unread_by_admin = 0;
                $convInList = $conversations->firstWhere('id', $activeConversation->id);
                if ($convInList) {
                    $convInList->unread_by_admin = 0;
                }
                $this->safeBroadcast(new ChatMessagesRead($activeConversation->id, 'admin'));
            }
        }

        return view('admin.chat.index', compact(
            'client',
            'conversations',
            'activeConversation',
            'activeOrders'
        ));
    }

    /**
     * Ambil daftar percakapan toko dalam format JSON (untuk refresh live list & search filter).
     */
    public function getConversationsJson(Request $request): JsonResponse
    {
        $client = $this->resolveClient();
        $search = trim((string) $request->query('q', ''));

        $query = ChatConversation::where('client_id', $client->id)
            ->with(['customer', 'latestMessage'])
            ->orderBy('last_message_at', 'desc');

        if ($search !== '') {
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        $conversations = $query->get()->map(function (ChatConversation $c) {
            return [
                'id'                   => $c->id,
                'customer_id'          => $c->customer_id,
                'customer_name'        => $c->customer?->name ?? 'Customer',
                'customer_avatar'      => $c->customer?->avatar_url,
                'last_message_preview' => $c->last_message_preview ?: 'Belum ada pesan',
                'last_message_at'      => $c->last_message_at?->diffForHumans() ?? '',
                'unread_by_admin'      => $c->unread_by_admin,
            ];
        });

        return response()->json([
            'conversations' => $conversations,
            'total_unread'  => ChatConversation::where('client_id', $client->id)->sum('unread_by_admin'),
        ]);
    }

    /**
     * Ambil riwayat obrolan lengkap dengan customer tertentu + info pesanan aktif.
     */
    public function getMessages(Request $request, ChatConversation $conversation): JsonResponse
    {
        $client = $this->resolveClient();
        $this->authorizeConversation($conversation, $client);

        $messages = $conversation->messages()
            ->with(['productContext', 'orderContext', 'sender'])
            ->latest('id')
            ->take(60)
            ->get()
            ->reverse()
            ->values();

        // Tandai pesan sebagai dibaca
        if ($conversation->unread_by_admin > 0 || $conversation->messages()->where('sender_role', 'customer')->where('is_read', false)->exists()) {
            $conversation->markAsReadFor('admin');
            $this->safeBroadcast(new ChatMessagesRead($conversation->id, 'admin'));
        }

        // Ambil pesanan aktif customer di toko ini
        $activeOrders = Order::where('client_id', $client->id)
            ->where('user_id', $conversation->customer_id)
            ->whereNotIn('status', ['batal', 'dibatalkan', 'ditolak'])
            ->with(['orderItems.product'])
            ->latest()
            ->take(3)
            ->get()
            ->map(function (Order $order) {
                return [
                    'id'           => $order->id,
                    'order_number' => $order->order_number ?? ('ORD-' . $order->id),
                    'status_label' => $order->status_label,
                    'status_color' => $order->status_color,
                    'total'        => $order->formatted_total,
                    'date_range'   => $order->date_range,
                    'detail_url'   => route('admin.orders.show', $order->id),
                ];
            });

        return response()->json([
            'conversation' => [
                'id'                   => $conversation->id,
                'customer_name'        => $conversation->customer?->name ?? 'Customer',
                'customer_email'       => $conversation->customer?->email ?? '-',
                'customer_phone'       => $conversation->customer?->phone ?? '-',
                'customer_avatar'      => $conversation->customer?->avatar_url,
                'unread_by_admin'      => $conversation->unread_by_admin,
            ],
            'messages'     => $messages->map(fn (ChatMessage $m) => $this->formatMessagePayload($m, $conversation)),
            'active_orders'=> $activeOrders,
        ]);
    }

    /**
     * Admin membalas pesan ke customer.
     */
    public function sendMessage(Request $request, ChatConversation $conversation): JsonResponse
    {
        $client = $this->resolveClient();
        $this->authorizeConversation($conversation, $client);

        $user = Auth::user();

        $request->validate([
            'message'    => 'nullable|string|max:2000|required_without:attachment',
            'attachment' => 'nullable|image|max:4096',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment') && $request->file('attachment')->isValid()) {
            $disk = \App\Services\StorageHelper::disk();
            $attachmentPath = $request->file('attachment')->store('chat_attachments', $disk);
        }

        $chatMessage = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $user->id,
            'sender_role'     => 'admin',
            'message'         => trim((string) $request->message),
            'attachment_path' => $attachmentPath,
            'is_read'         => false,
        ]);

        $conversation->recordNewMessage($chatMessage);

        // Siarkan secara instan via WebSocket Reverb (0-delay)
        $this->safeBroadcast(new ChatMessageSent($chatMessage));

        // Kirim Web Push Notification ke perangkat Customer (latar belakang / browser tertutup)
        try {
            $customerUrl = $client->subdomain
                ? route('customer.home', ['subdomain' => $client->subdomain, 'open_chat' => 1])
                : url('/?open_chat=1');

            app(\App\Services\WebPushService::class)->sendToUser($conversation->customer_id, [
                'title' => 'Balasan dari ' . ($client->nama_usaha ?? $client->business_name ?? 'Admin Toko'),
                'body'  => $chatMessage->message ?: 'Mengirim foto lampiran',
                'url'   => $customerUrl,
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
     * Tandai pesan customer telah dibaca oleh admin.
     */
    public function markAsRead(Request $request, ChatConversation $conversation): JsonResponse
    {
        $client = $this->resolveClient();
        $this->authorizeConversation($conversation, $client);

        $conversation->markAsReadFor('admin');
        $this->safeBroadcast(new ChatMessagesRead($conversation->id, 'admin'));

        return response()->json(['success' => true]);
    }

    /**
     * Dapatkan instance client tenant yang sedang aktif.
     */
    private function resolveClient(): Client
    {
        $user = Auth::user();

        if (! $user) {
            $user = User::whereNotNull('client_id')->first();
        }

        $client = $user?->client;

        if (! $client) {
            $client = Client::where('status', 'aktif')->first() ?? Client::first();
        }

        return $client;
    }

    /**
     * Validasi isolasi multi-tenant strictly per-toko.
     */
    private function authorizeConversation(ChatConversation $conversation, Client $client): void
    {
        $user = Auth::user();
        if ($user && $user->role === 'owner') {
            return;
        }

        if ((int) $conversation->client_id !== (int) $client->id) {
            abort(403, 'Akses obrolan ini ditolak karena milik toko rental lain.');
        }
    }

    /**
     * Format payload pesan.
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
                : ($m->sender?->name ?? 'Customer'),
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
