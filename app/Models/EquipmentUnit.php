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

    /**
     * Periksa ketersediaan unit spesifik untuk rentang waktu sewa tertentu.
     * Mencegah double-booking pada unit fisik antar pesanan yang saling tumpang-tindih.
     */
    public function isAvailableForPeriod($startDate, $endDate, $excludeOrderId = null): bool
    {
        if (in_array(strtolower((string) $this->status), ['rusak', 'maintenance'])) {
            return false;
        }

        if (! $startDate || ! $endDate) {
            return ! in_array(strtolower((string) $this->status), ['disewa', 'rented']);
        }

        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);

        if ($end->lt($start)) {
            return false;
        }

        $hasConflict = $this->orderItemUnits()
            ->whereHas('orderItem.order', function ($q) use ($start, $end, $excludeOrderId) {
                $q->whereNotIn('status', ['batal', 'dibatalkan', 'ditolak', 'selesai'])
                  ->where('rental_start_date', '<=', $end)
                  ->where('rental_end_date', '>=', $start);

                if ($excludeOrderId) {
                    $q->where('id', '!=', $excludeOrderId);
                }
            })
            ->exists();

        return ! $hasConflict;
    }

    public function latestOrder()
    {
        return $this->orderItemUnits()
            ->with('orderItem.order')
            ->latest()
            ->first()?->orderItem?->order;
    }
}
