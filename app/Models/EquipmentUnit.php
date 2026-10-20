<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentUnit extends Model
{
    protected $guarded = [];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Tambahkan relasi ini:
    public function orderItemUnits()
    {
        return $this->hasMany(OrderItemUnit::class);
    }

    public function damageReports()
    {
        return $this->hasMany(DamageReport::class);
    }

    public function getAssetCodeAttribute(): ?string
    {
        return $this->unit_code ?? $this->attributes['asset_code'] ?? null;
    }
}
