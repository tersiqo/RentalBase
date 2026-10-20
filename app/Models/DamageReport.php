<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DamageReport extends Model
{
    protected $guarded = [];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function equipmentUnit()
    {
        return $this->belongsTo(EquipmentUnit::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function damageCase()
    {
        return $this->hasOne(DamageCase::class);
    }

    public function getImagesListAttribute(): array
    {
        if (empty($this->image)) {
            return [];
        }

        $images = [];
        if (is_array($this->image)) {
            $images = $this->image;
        } elseif (str_starts_with($this->image, '[') && str_ends_with($this->image, ']')) {
            $decoded = json_decode($this->image, true);
            if (is_array($decoded)) {
                $images = $decoded;
            }
        } else {
            $images = array_filter(array_map('trim', explode(',', $this->image)));
        }

        return array_map(function ($img) {
            if (str_starts_with($img, 'http')) {
                return $img;
            }
            if (file_exists(public_path('storage/' . $img))) {
                return asset('storage/' . $img);
            }
            return Storage::url($img);
        }, $images);
    }
}
