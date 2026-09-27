<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConditionCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'tipe',
        'catatan',
        'foto',
        'diperiksa_oleh',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function inspector()
    {
        return $this->belongsTo(User::class, 'diperiksa_oleh');
    }
}