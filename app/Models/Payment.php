<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'metode',
        'bukti_pembayaran',
        'tanggal_bayar',
        'status',
        'diverifikasi_oleh',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_bayar' => 'datetime',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }
}