<?php

namespace App\Events;

use App\Models\DamageCase;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DamageCaseResolved implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public DamageCase $damageCase) {}

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        $order = $this->damageCase->order;
        $userId = $order?->user_id ?? 0;

        return [
            new PrivateChannel('customer.' . $userId . '.orders'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'damage.resolved';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        $order = $this->damageCase->order;

        return [
            'case_id' => $this->damageCase->id,
            'order_id' => $order?->id,
            'order_number' => $order?->order_number,
            'message' => 'Kasus klaim kerusakan untuk pesanan #' . ($order?->order_number ?? '') . ' telah diselesaikan dan jaminan KTP fisik dikembalikan.',
        ];
    }
}
