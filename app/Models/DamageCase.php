<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DamageCase extends Model
{
    use HasFactory;

    protected $fillable = [
        'damage_report_id',
        'status',
        'tanggapan_customer',
        'catatan_admin',
        'hasil_penyelesaian',
    ];

    public function damageReport()
    {
        return $this->belongsTo(DamageReport::class);
    }
}