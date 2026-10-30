<?php

namespace App\Events;

use App\Models\ClientRegistration;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TenantRegistrationSubmitted
{
    use Dispatchable, SerializesModels;

    public function __construct(public ClientRegistration $registration) {}
}
