<?php

namespace App\Events;

use App\Models\DamageCase;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DamageCaseCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public DamageCase $damageCase) {}
}
