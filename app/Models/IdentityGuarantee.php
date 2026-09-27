<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IdentityGuarantee extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'customer_id',
        'nama_lengkap',
        'nomor_identitas',
        'foto_identitas',
        'foto_wajah',
        'alamat',
        'status',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}