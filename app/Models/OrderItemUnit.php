<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemUnit extends Model
{
    protected $guarded = [];
    /**
     * Item pesanan induk.
     */
    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }
    /**
     * Unit fisik yang dialokasikan untuk item ini.
     */
    public function equipmentUnit()
    {
        return $this->belongsTo(EquipmentUnit::class);
    }
}
