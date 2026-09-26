<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
        'client_id',
        'category_id',
        'nama',
        'deskripsi',
        'harga_sewa',
        'stok',
        'gambar',
        'status',
    ];

    protected $casts = [
        'harga_sewa' => 'decimal:2',
        'stok' => 'integer',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}