<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConditionCheck extends Model
{
    protected $guarded = [];
    /**
     * Unit fisik yang diperiksa.
     */
    public function equipmentUnit()
    {
        return $this->belongsTo(EquipmentUnit::class);
    }
    /**
     * Admin yang melakukan pemeriksaan.
     */
    public function checker()
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function order()
    {
        return $this->belongsTo(\App\Models\Order::class);
    }

    /**
     * Dapatkan daftar path foto (mendukung single string atau JSON array).
     */
    public function getImagesListAttribute(): array
    {
        if (empty($this->image)) {
            return [];
        }
        $decoded = json_decode($this->image, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        return [$this->image];
    }

    /**
     * Dapatkan URL foto pertama untuk thumbnail/tampilan.
     */
    public function getFirstImageUrlAttribute(): ?string
    {
        $list = $this->images_list;
        if (empty($list)) {
            return null;
        }
        return \Illuminate\Support\Facades\Storage::disk('public')->url($list[0]);
    }
}
