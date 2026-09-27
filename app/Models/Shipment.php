<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'metode_pengiriman',
        'nama_kurir',
        'nomor_resi',
        'tanggal_kirim',
        'tanggal_diterima',
        'status_pengiriman',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_kirim' => 'date',
            'tanggal_diterima' => 'date',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}