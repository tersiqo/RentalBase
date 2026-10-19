<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientPaymentMethod extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
