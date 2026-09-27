<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'customer_id',
        'kode_order',
        'tanggal_mulai',
        'tanggal_selesai',
        'alamat_pengiriman',
        'total_harga',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'total_harga' => 'decimal:2',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    public function returns()
    {
        return $this->hasMany(ReturnModel::class, 'order_id');
    }

    public function identityGuarantee()
    {
        return $this->hasOne(IdentityGuarantee::class);
    }

    public function conditionChecks()
    {
        return $this->hasMany(ConditionCheck::class);
    }

    public function damageReports()
    {
        return $this->hasMany(DamageReport::class);
    }
}