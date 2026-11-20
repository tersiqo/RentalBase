<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ChatMessage $message
    ) {}

    /**
     * Tentukan channel siaran WebSocket.
     */
    public function broadcastOn(): array
    {
        $conversation = $this->message->conversation;
        if (! $conversation) {
            return [];
        }

        // 1. Channel ruang obrolan spesifik (untuk yang sedang membuka dialog chat)
        $channels = [
            new PrivateChannel('chat.conversation.' . $this->message->conversation_id),
        ];

        // 2. Channel notifikasi global toko / customer (untuk lonceng/chime/badge)
        if ($this->message->sender_role === 'customer') {
            $channels[] = new PrivateChannel('tenant.' . $conversation->client_id . '.orders');
        } else {
            $channels[] = new PrivateChannel('customer.' . $conversation->customer_id . '.orders');
        }

        return $channels;
    }

    /**
     * Nama event broadcast.
     */
    public function broadcastAs(): string
    {
        return 'chat.message';
    }

    /**
     * Data payload yang dikirim ke frontend.
     */
    public function broadcastWith(): array
    {
        $conversation = $this->message->conversation;
        $sender = $this->message->sender;

        return [
            'id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'client_id' => $conversation?->client_id,
            'customer_id' => $conversation?->customer_id,
            'sender_id' => $this->message->sender_id,
            'sender_role' => $this->message->sender_role,
            'sender_name' => $this->message->sender_role === 'admin'
                ? ($conversation?->client?->nama_usaha ?? 'Admin Toko')
                : ($sender?->name ?? 'Customer'),
            'sender_avatar' => $this->message->sender_role === 'admin'
                ? $conversation?->client?->logo_url
                : $sender?->avatar_url,
            'message' => $this->message->message,
            'attachment_url' => $this->message->attachment_url,
            'context_data' => $this->message->context_data,
            'is_read' => $this->message->is_read,
            'read_at' => $this->message->read_at?->toIso8601String(),
            'created_at' => $this->message->created_at?->toIso8601String() ?? now()->toIso8601String(),
            'time_formatted' => $this->message->created_at?->format('H:i') ?? now()->format('H:i'),
            'unread_by_admin' => $conversation?->unread_by_admin ?? 0,
            'unread_by_customer' => $conversation?->unread_by_customer ?? 0,
        ];
    }
}
