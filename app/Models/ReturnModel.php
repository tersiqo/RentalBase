<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnModel extends Model
{
    use HasFactory;

    protected $table = 'returns';

    protected $fillable = [
        'order_id',
        'metode_pengembalian',
        'nama_kurir',
        'nomor_resi',
        'tanggal_pengembalian',
        'status_pengembalian',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pengembalian' => 'date',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}