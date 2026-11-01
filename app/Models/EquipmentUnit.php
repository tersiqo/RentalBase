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
        $todayStart = \Carbon\Carbon::now()->startOfDay()->format('Y-m-d H:i:s');
        $todayEnd = \Carbon\Carbon::now()->endOfDay()->format('Y-m-d H:i:s');

        return $this->orderItemUnits()
            ->whereHas('orderItem.order', function ($q) use ($todayStart, $todayEnd) {
                $q->whereNotIn('status', ['batal', 'dibatalkan', 'ditolak', 'selesai'])
                  ->where(function ($sub) use ($todayStart, $todayEnd) {
                      $sub->whereIn('status', ['dikirim', 'sedang_disewa'])
                          ->orWhere(function ($period) use ($todayStart, $todayEnd) {
                              $period->where('rental_start_date', '<=', $todayEnd)
                                     ->where('rental_end_date', '>=', $todayStart);
                          });
                  });
            })
            ->exists();
    }

    /**
     * Sinkronisasi status fisik unit di database ('disewa' vs 'tersedia')
     * berdasarkan apakah unit saat ini sedang aktif disewa/berada di tangan customer.
     */
    public function syncPhysicalStatus(): void
    {
        $currentStatus = strtolower((string) ($this->status ?? 'tersedia'));

        if (in_array($currentStatus, ['rusak', 'maintenance'])) {
            return;
        }

        if ($this->isActivelyRented()) {
            if ($currentStatus !== 'disewa') {
                $this->update(['status' => 'disewa']);
            }
        } else {
            if ($currentStatus !== 'tersedia') {
                $this->update(['status' => 'tersedia']);
            }
        }
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
            return ! $this->isActivelyRented();
        }

        $start = \Carbon\Carbon::parse($startDate)->startOfDay();
        $end = \Carbon\Carbon::parse($endDate)->endOfDay();

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
