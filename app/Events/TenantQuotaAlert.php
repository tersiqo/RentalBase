<?php

namespace App\Events;

use App\Models\Client;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TenantQuotaAlert implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Client $client,
        public int $currentUsage,
        public int $maxLimit,
        public float $percentage
    ) {}

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('tenant.' . $this->client->id . '.orders'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'tenant.quota_alert';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'client_id' => $this->client->id,
            'current_usage' => $this->currentUsage,
            'max_limit' => $this->maxLimit,
            'percentage' => $this->percentage,
            'message' => 'Peringatan: Kuota produk toko Anda telah terpakai ' . round($this->percentage) . '% (' . $this->currentUsage . '/' . $this->maxLimit . ' produk). Segera upgrade paket langganan.',
        ];
    }
}
