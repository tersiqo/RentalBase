<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdentityGuarantee extends Model
{
    protected $guarded = [];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
