<?php

namespace App\Events;

use App\Models\ClientRegistration;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TenantRegistrationSubmitted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public ClientRegistration $registration) {}

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
        return 'tenant.registered';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'registration_id' => $this->registration->id,
            'business_name' => $this->registration->business_name,
            'owner_name' => $this->registration->name,
            'subdomain' => $this->registration->subdomain,
            'message' => 'Pendaftaran toko rental baru: "' . $this->registration->business_name . '" menunggu peninjauan!',
        ];
    }
}
