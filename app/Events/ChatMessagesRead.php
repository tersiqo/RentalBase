<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessagesRead implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $conversationId,
        public string $readerRole // 'admin' | 'customer'
    ) {}

    /**
     * Broadcast ke channel obrolan terkait.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.conversation.' . $this->conversationId),
        ];
    }

    /**
     * Nama event.
     */
    public function broadcastAs(): string
    {
        return 'chat.read';
    }

    /**
     * Payload broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversationId,
            'reader_role' => $this->readerRole,
            'read_at' => now()->toIso8601String(),
        ];
    }
}
