<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public const SHIPPING_KURIR = 'kurir';

    public const SHIPPING_AMBIL_TOKO = 'ambil_toko';

    protected $guarded = [];

    protected $casts = [
        'rental_start_date' => 'datetime',
        'rental_end_date' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    public function isPickup(): bool
    {
        return $this->shipping_method === self::SHIPPING_AMBIL_TOKO;
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
