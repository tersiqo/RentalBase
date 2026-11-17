<?php

namespace App\Events;

use App\Models\DamageCase;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DamageCaseResponded implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param DamageCase $damageCase
     * @param string $sender 'admin'|'customer'
     * @param string $notes
     */
    public function __construct(
        public DamageCase $damageCase,
        public string $sender,
        public string $notes = ''
    ) {}

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        $order = $this->damageCase->order;
        $userId = $order?->user_id ?? 0;
        $clientId = $order?->client_id ?? 0;

        // Jika pengirimnya admin -> siarkan ke channel customer
        if ($this->sender === 'admin') {
            return [
                new PrivateChannel('customer.' . $userId . '.orders'),
            ];
        }

        // Jika pengirimnya customer -> siarkan ke channel admin toko
        return [
            new PrivateChannel('tenant.' . $clientId . '.orders'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'damage.responded';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        $order = $this->damageCase->order;

        $message = $this->sender === 'admin'
            ? 'Pihak toko mengirimkan tanggapan baru terkait klaim kerusakan pesanan #' . ($order?->order_number ?? '') . '.'
            : 'Customer mengirimkan tanggapan baru terkait klaim kerusakan pesanan #' . ($order?->order_number ?? '') . '.';

        return [
            'case_id' => $this->damageCase->id,
            'order_id' => $order?->id,
            'order_number' => $order?->order_number,
            'sender' => $this->sender,
            'notes' => $this->notes,
            'message' => $message,
        ];
    }
}
