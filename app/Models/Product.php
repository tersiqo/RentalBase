<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = [];

    protected $casts = [
        'images' => 'array',
    ];

    protected $appends = [
        'cover_image_url',
        'gallery_images',
    ];

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
            return storage_url($this->main_image) ?? 'https://placehold.co/400x300/f8fafc/64748b?text=' . urlencode($this->name ?? 'No Image');
        }

        return 'https://placehold.co/400x300/f8fafc/64748b?text=' . urlencode($this->name ?? 'No Image');
    }

    public function getGalleryImagesAttribute(): array
    {
        $gallery = [];

        if (! empty($this->main_image)) {
            $gallery[] = storage_url($this->main_image);
        }

        $extraImages = $this->images ?? [];
        if (is_string($extraImages)) {
            $extraImages = json_decode($extraImages, true) ?? [];
        }

        if (is_array($extraImages)) {
            foreach ($extraImages as $img) {
                if (! empty($img)) {
                    $url = storage_url($img);
                    if ($url && ! in_array($url, $gallery, true)) {
                        $gallery[] = $url;
                    }
                }
            }
        }

        if (empty($gallery)) {
            $gallery[] = 'https://placehold.co/800x600/f8fafc/64748b?text=' . urlencode($this->name ?? 'No Image');
        }

        return $gallery;
    }

    public function getAvailableUnitsForPeriod($startDate = null, $endDate = null): int
    {
        try {
            $start = $startDate
                ? \Carbon\Carbon::parse($startDate)->startOfDay()
                : \Carbon\Carbon::now()->startOfDay();

            $end = $endDate
                ? \Carbon\Carbon::parse($endDate)->endOfDay()
                : \Carbon\Carbon::now()->endOfDay();

            if ($end->lt($start)) {
                return 0;
            }

            $totalUnits = $this->units()
                ->whereNotIn('status', ['rusak', 'maintenance'])
                ->count();

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
            return $this->units()->whereNotIn('status', ['rusak', 'maintenance'])->count();
        }
    }
}
