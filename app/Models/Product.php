<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function units()
    {
        return $this->hasMany(EquipmentUnit::class);
    }

    public function getAvailableUnitsForPeriod($startDate = null, $endDate = null): int
    {
        if (! $startDate || ! $endDate) {
            return $this->units()->where('status', 'tersedia')->count();
        }

        try {
            $start = \Carbon\Carbon::parse($startDate)->startOfDay();
            $end = \Carbon\Carbon::parse($endDate)->endOfDay();

            if ($end->lt($start)) {
                return 0;
            }

            $totalUnits = $this->units()->where('status', '!=', 'rusak')->count();

            $bookedUnits = OrderItem::where('product_id', $this->id)
                ->whereHas('order', function ($query) use ($start, $end) {
                    $query->where('client_id', $this->client_id)
                        ->whereNotIn('status', ['batal', 'dibatalkan', 'ditolak', 'selesai'])
                        ->where('rental_start_date', '<=', $end)
                        ->where('rental_end_date', '>=', $start);
                })
                ->sum('quantity');

            return max(0, (int) $totalUnits - (int) $bookedUnits);
        } catch (\Exception $e) {
            return $this->units()->where('status', 'tersedia')->count();
        }
    }
}
