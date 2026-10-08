<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $guarded = [];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)->where('status', 'aktif')->latest();
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function equipmentUnits()
    {
        return $this->hasMany(EquipmentUnit::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
