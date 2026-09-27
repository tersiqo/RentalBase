<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'category_id',
        'nama',
        'deskripsi',
        'harga_sewa',
        'stok',
        'foto',
        'ketentuan_jaminan',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'harga_sewa' => 'decimal:2',
            'stok' => 'integer',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}