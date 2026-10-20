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

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getRentalPriceAttribute()
    {
        return $this->rental_price_per_day;
    }

    public function setRentalPriceAttribute($value)
    {
        $this->attributes['rental_price_per_day'] = $value;
    }

    public function getImageAttribute()
    {
        if (! empty($this->attributes['image'])) {
            return is_array($this->attributes['image']) 
                ? $this->attributes['image'] 
                : json_decode($this->attributes['image'], true);
        }

        return $this->main_image ? [$this->main_image] : [];
    }

    public function getCoverImageUrlAttribute(): string
    {
        if ($this->main_image) {
            return str_starts_with($this->main_image, 'http')
                ? $this->main_image
                : \Illuminate\Support\Facades\Storage::url($this->main_image);
        }

        return 'https://placehold.co/400x300/f8fafc/64748b?text=' . urlencode($this->name ?? 'No Image');
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
