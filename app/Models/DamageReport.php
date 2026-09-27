<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DamageReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'dilaporkan_oleh',
        'deskripsi',
        'foto',
        'status',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'dilaporkan_oleh');
    }

    public function damageCase()
    {
        return $this->hasOne(DamageCase::class);
    }
}