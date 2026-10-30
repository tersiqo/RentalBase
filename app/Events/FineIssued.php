<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FineIssued
{
    use Dispatchable, SerializesModels;

    public function __construct(public Order $order, public float $amount = 0) {}
}
