<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnModel extends Model
{
    protected $table = 'returns';

    protected $guarded = [];

    protected $attributes = [
        'late_hours' => 0,
        'calculated_late_fee' => 0,
        'late_fee_amount' => 0,
        'late_fee_status' => 'tidak_ada',
        'return_status' => 'diajukan',
    ];

    protected $casts = [
        'return_date' => 'datetime',
        'calculated_late_fee' => 'decimal:2',
        'late_fee_amount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
