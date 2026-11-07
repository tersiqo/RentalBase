<?php

namespace App\Events;

use App\Models\DamageCase;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DamageCasePaymentUploaded implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public DamageCase $damageCase) {}

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('tenant.' . $this->damageCase->client_id . '.orders'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'damage.payment_uploaded';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        $order = $this->damageCase->damageReport?->order;
        $orderNumber = $order?->order_number ?? $this->damageCase->id;

        return [
            'case_id' => $this->damageCase->id,
            'order_id' => $order?->id,
            'order_number' => $orderNumber,
            'total_amount' => $this->damageCase->total_amount,
            'message' => 'Bukti pembayaran kompensasi kerusakan pesanan #' . $orderNumber . ' telah diunggah oleh customer.',
        ];
    }
}
