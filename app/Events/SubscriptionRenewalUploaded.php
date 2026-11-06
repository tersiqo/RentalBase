<?php

namespace App\Events;

use App\Models\Subscription;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubscriptionRenewalUploaded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Subscription $subscription) {}

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('owner.platform'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'subscription.renewal_uploaded';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        $businessName = $this->subscription->client?->business_name ?? 'Tenant';

        return [
            'subscription_id' => $this->subscription->id,
            'client_id' => $this->subscription->client_id,
            'client_name' => $businessName,
            'plan_name' => $this->subscription->plan_name,
            'message' => 'Toko "' . $businessName . '" mengunggah bukti pembayaran langganan paket ' . $this->subscription->plan_name . '.',
        ];
    }
}
