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

    public function conditionChecks()
    {
        return $this->hasMany(ConditionCheck::class);
    }

    public function getAssetCodeAttribute(): ?string
    {
        return $this->unit_code ?? $this->attributes['asset_code'] ?? null;
    }

    public function isActivelyRented(): bool
    {
        if (in_array(strtolower((string) $this->status), ['disewa', 'rented'])) {
            return true;
        }

        return $this->orderItemUnits()
            ->whereHas('orderItem.order', function ($q) {
                $q->whereIn('status', [
                    'menunggu_pembayaran',
                    'pembayaran_terverifikasi',
                    'dikonfirmasi',
                    'siap_kirim',
                    'sedang_dikirim',
                    'sedang_disewa',
                ]);
            })
            ->exists();
    }

    public function latestOrder()
    {
        return $this->orderItemUnits()
            ->with('orderItem.order')
            ->latest()
            ->first()?->orderItem?->order;
    }
}
