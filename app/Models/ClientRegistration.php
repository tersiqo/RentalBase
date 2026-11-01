<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientRegistration extends Model
{
    protected $guarded = [];

    public function getNamaUsahaAttribute(): string
    {
        return $this->attributes['business_name'] ?? $this->attributes['nama_usaha'] ?? 'Tenant';
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'subdomain', 'subdomain');
    }

    public function getPaymentProofUrlAttribute(): ?string
    {
        return $this->payment_proof ? storage_url($this->payment_proof) : null;
    }
}
