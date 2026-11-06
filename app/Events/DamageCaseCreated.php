<?php

namespace App\Events;

use App\Models\DamageCase;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DamageCaseCreated implements ShouldBroadcastNow
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
        return 'damage.created';
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
            'message' => 'Laporan kompensasi kerusakan telah diterbitkan untuk pesanan #' . ($order?->order_number ?? '') . '.',
        ];
    }
}
