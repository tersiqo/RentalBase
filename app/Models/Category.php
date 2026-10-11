<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'client_id',
        'name',
        'slug',
        'description',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
