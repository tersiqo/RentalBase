<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    // Membuka kunci keamanan agar data bisa disimpan otomatis (Mass Assignment)
    protected $guarded = [];
}
