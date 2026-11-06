<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderShipped implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Order $order) {}

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('customer.' . $this->order->user_id . '.orders'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'order.shipped';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        $isPickup = $this->order->isPickup();

        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'status' => $this->order->status,
            'message' => $isPickup
                ? 'Pesanan #' . $this->order->order_number . ' telah diserahkan di toko. Masa sewa resmi aktif!'
                : 'Pesanan #' . $this->order->order_number . ' sedang dalam perjalanan oleh kurir!',
        ];
    }
}
