<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DamageCase extends Model
{
    protected $guarded = [];

    protected $casts = [
        'repair_cost' => 'decimal:2',
        'fine_amount' => 'decimal:2',
    ];

    public function damageReport()
    {
        return $this->belongsTo(DamageReport::class);
    }

    public function order()
    {
        return $this->hasOneThrough(Order::class, DamageReport::class, 'id', 'id', 'damage_report_id', 'order_id');
    }

    public function getTotalAmountAttribute(): float
    {
        return (float) ($this->repair_cost ?? 0) + (float) ($this->fine_amount ?? 0);
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->total_amount, 0, ',', '.');
    }

    public function getFormattedRepairCostAttribute(): string
    {
        return 'Rp ' . number_format((float) ($this->repair_cost ?? 0), 0, ',', '.');
    }

    public function getFormattedFineAmountAttribute(): string
    {
        return 'Rp ' . number_format((float) ($this->fine_amount ?? 0), 0, ',', '.');
    }

    public function getStatusLabelAttribute(): string
    {
        $status = strtolower(trim(str_replace(['-', ' '], '_', $this->case_status ?? 'terbuka')));

        return match ($status) {
            'terbuka', 'menunggu_respons', 'menunggu_tanggapan' => 'Menunggu Tanggapan',
            'negosiasi', 'dalam_tinjauan' => 'Dalam Tinjauan',
            'menunggu_pembayaran', 'menunggu_pembayaran_kompensasi' => 'Menunggu Pembayaran Kompensasi',
            'kompensasi_dibayar', 'menunggu_verifikasi' => 'Menunggu Verifikasi Toko',
            'lunas', 'selesai' => 'Selesai / Lunas',
            'dibatalkan' => 'Dibatalkan',
            default => ucwords(str_replace('_', ' ', $this->case_status ?? 'Terbuka')),
        };
    }

    public function getStatusColorAttribute(): string
    {
        $status = strtolower(trim(str_replace(['-', ' '], '_', $this->case_status ?? 'terbuka')));

        return match ($status) {
            'terbuka', 'menunggu_respons', 'menunggu_tanggapan' => 'bg-amber-100 text-amber-800 border-amber-200',
            'negosiasi', 'dalam_tinjauan' => 'bg-blue-100 text-blue-800 border-blue-200',
            'menunggu_pembayaran', 'menunggu_pembayaran_kompensasi' => 'bg-rose-100 text-rose-800 border-rose-200',
            'kompensasi_dibayar', 'menunggu_verifikasi' => 'bg-purple-100 text-purple-800 border-purple-200',
            'lunas', 'selesai' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'dibatalkan' => 'bg-gray-100 text-gray-800 border-gray-200',
            default => 'bg-gray-100 text-gray-800 border-gray-200',
        };
    }

    public function getCanPayAttribute(): bool
    {
        $caseStatus = strtolower(trim(str_replace(['-', ' '], '_', $this->case_status ?? '')));
        $paymentStatus = strtolower(trim(str_replace(['-', ' '], '_', $this->payment_status ?? '')));

        if ($this->total_amount <= 0) {
            return false;
        }

        if (in_array($paymentStatus, ['lunas', 'menunggu_verifikasi'])) {
            return false;
        }

        if (in_array($caseStatus, ['lunas', 'selesai', 'dibatalkan', 'kompensasi_dibayar'])) {
            return false;
        }

        return true;
    }
}
