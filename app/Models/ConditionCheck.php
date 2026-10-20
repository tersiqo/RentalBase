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
}
