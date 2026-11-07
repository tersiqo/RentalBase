<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'amount'      => 'decimal:2',
        'paid_at'     => 'datetime',
        'verified_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::saved(function ($payment) {
            $clientId = $payment->order?->client_id ?? $payment->client_id;
            if ($clientId) {
                \Illuminate\Support\Facades\Cache::forget('admin_dashboard_counts_' . $clientId);
            }
        });

        static::deleted(function ($payment) {
            $clientId = $payment->order?->client_id ?? $payment->client_id;
            if ($clientId) {
                \Illuminate\Support\Facades\Cache::forget('admin_dashboard_counts_' . $clientId);
            }
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function clientPaymentMethod()
    {
        return $this->belongsTo(ClientPaymentMethod::class, 'client_payment_method_id');
    }

    public function getPaymentProofUrlAttribute(): ?string
    {
        return storage_url($this->payment_proof ?? $this->proof_of_payment);
    }
}
