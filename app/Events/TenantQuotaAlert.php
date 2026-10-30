<?php

namespace App\Events;

use App\Models\Client;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TenantQuotaAlert
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Client $client,
        public int $currentUsage,
        public int $maxLimit,
        public float $percentage
    ) {}
}
