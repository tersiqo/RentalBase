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

    public function equipmentUnit()
    {
        return $this->hasOneThrough(EquipmentUnit::class, DamageReport::class, 'id', 'id', 'damage_report_id', 'equipment_unit_id');
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
            'menunggu_pembayaran', 'menunggu_pembayaran_kompensasi' => 'Menunggu Pembayaran',
            'kompensasi_dibayar', 'menunggu_verifikasi' => 'Menunggu Verifikasi Toko',
            'lunas' => 'Lunas',
            'selesai' => 'Selesai',
            'dibatalkan' => 'Dibatalkan',
            default => ucwords(str_replace('_', ' ', $this->case_status ?? 'Terbuka')),
        };
    }

    public function getStatusColorAttribute(): string
    {
        $status = strtolower(trim(str_replace(['-', ' '], '_', $this->case_status ?? 'terbuka')));

        return match ($status) {
            'terbuka', 'menunggu_respons', 'menunggu_tanggapan' => 'bg-amber-50 text-amber-700',
            'negosiasi', 'dalam_tinjauan' => 'bg-blue-50 text-blue-700',
            'menunggu_pembayaran', 'menunggu_pembayaran_kompensasi' => 'bg-rose-50 text-rose-700',
            'kompensasi_dibayar', 'menunggu_verifikasi' => 'bg-purple-50 text-purple-700',
            'lunas', 'selesai' => 'bg-emerald-50 text-emerald-700',
            'dibatalkan' => 'bg-slate-100 text-slate-700',
            default => 'bg-slate-100 text-slate-700',
        };
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        $status = strtolower(trim(str_replace(['-', ' '], '_', $this->payment_status ?? 'belum_dibayar')));

        return match ($status) {
            'lunas', 'paid' => 'Lunas',
            'menunggu_verifikasi', 'verifikasi' => 'Menunggu Verifikasi',
            'menunggu_pembayaran', 'belum_dibayar', 'unpaid' => 'Belum Dibayar',
            default => ucwords(str_replace('_', ' ', $this->payment_status ?? 'Belum Dibayar')),
        };
    }

    public function getPaymentStatusColorAttribute(): string
    {
        $status = strtolower(trim(str_replace(['-', ' '], '_', $this->payment_status ?? 'belum_dibayar')));

        return match ($status) {
            'lunas', 'paid' => 'bg-emerald-50 text-emerald-700',
            'menunggu_verifikasi', 'verifikasi' => 'bg-purple-50 text-purple-700',
            'menunggu_pembayaran', 'belum_dibayar', 'unpaid' => 'bg-rose-50 text-rose-700',
            default => 'bg-slate-100 text-slate-700',
        };
    }

    public function getUnifiedStatusAttribute(): array
    {
        $caseStatus = strtolower(trim(str_replace(['-', ' '], '_', $this->case_status ?? 'terbuka')));
        $paymentStatus = strtolower(trim(str_replace(['-', ' '], '_', $this->payment_status ?? 'belum_dibayar')));

        // 1. Selesai / Lunas
        if (in_array($caseStatus, ['selesai', 'lunas']) || $paymentStatus === 'lunas') {
            return [
                'label' => 'Selesai & Lunas',
                'color' => 'bg-emerald-50 text-emerald-700',
                'key'   => 'selesai',
            ];
        }

        // 2. Dibatalkan
        if ($caseStatus === 'dibatalkan') {
            return [
                'label' => 'Dibatalkan',
                'color' => 'bg-slate-100 text-slate-700',
                'key'   => 'dibatalkan',
            ];
        }

        // 3. Verifikasi Pembayaran (bukti bayar kompensasi sudah diupload)
        if (in_array($caseStatus, ['kompensasi_dibayar', 'menunggu_verifikasi']) || in_array($paymentStatus, ['menunggu_verifikasi', 'verifikasi'])) {
            return [
                'label' => 'Verifikasi Pembayaran',
                'color' => 'bg-purple-50 text-purple-700',
                'key'   => 'kompensasi_dibayar',
            ];
        }

        // 4. Menunggu Pembayaran
        if (in_array($caseStatus, ['menunggu_pembayaran', 'menunggu_pembayaran_kompensasi'])) {
            return [
                'label' => 'Menunggu Pembayaran',
                'color' => 'bg-rose-50 text-rose-700',
                'key'   => 'menunggu_pembayaran',
            ];
        }

        // 5. Dalam Tinjauan / Negosiasi
        if (in_array($caseStatus, ['dalam_tinjauan', 'negosiasi'])) {
            return [
                'label' => 'Dalam Tinjauan',
                'color' => 'bg-blue-50 text-blue-700',
                'key'   => 'dalam_tinjauan',
            ];
        }

        // 6. Default: Menunggu Tanggapan
        return [
            'label' => 'Menunggu Tanggapan',
            'color' => 'bg-amber-50 text-amber-700',
            'key'   => 'terbuka',
        ];
    }

    public function getTimelineNotesAttribute(): array
    {
        if (empty($this->resolution_notes)) {
            return [];
        }

        $entries = preg_split('/\n(?=\[)/', trim($this->resolution_notes));
        $result = [];

        foreach ($entries as $entry) {
            $entry = trim($entry);
            if (empty($entry)) {
                continue;
            }

            if (preg_match('/^\[(.*?)\]\s*(.*)$/s', $entry, $matches)) {
                $header = $matches[1];
                $body = $matches[2];
                $isCustomer = str_contains(strtolower($header), 'penyewa') || str_contains(strtolower($header), 'customer');
                $isPayment = str_contains(strtolower($header), 'pembayaran');
                $isStore = str_contains(strtolower($header), 'toko') || str_contains(strtolower($header), 'admin');

                $result[] = [
                    'header' => $header,
                    'message' => $body,
                    'is_customer' => $isCustomer,
                    'is_payment' => $isPayment,
                    'is_store' => $isStore,
                ];
            } else {
                $result[] = [
                    'header' => 'Catatan',
                    'message' => $entry,
                    'is_customer' => false,
                    'is_payment' => false,
                    'is_store' => true,
                ];
            }
        }

        return $result;
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
