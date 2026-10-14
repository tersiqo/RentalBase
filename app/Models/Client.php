<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $guarded = [];

    public function getNamaUsahaAttribute(): string
    {
        return $this->attributes['nama_usaha'] ?? $this->attributes['business_name'] ?? 'RentalBase Tenant';
    }

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
        return $this->hasOne(Subscription::class)->where('status', 'aktif')->latestOfMany();
    }

    public function latestSubscription()
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function adminUser()
    {
        return $this->hasOne(User::class)->where('role', 'admin_rental');
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
