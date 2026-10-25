<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Order extends Model
{
    public const SHIPPING_KURIR = 'kurir';

    public const SHIPPING_AMBIL_TOKO = 'ambil_toko';

    protected $guarded = [];

    protected $casts = [
        'rental_start_date' => 'datetime',
        'rental_end_date' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    public function isPickup(): bool
    {
        return $this->shipping_method === self::SHIPPING_AMBIL_TOKO;
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment()
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function identityGuarantee()
    {
        return $this->hasOne(IdentityGuarantee::class);
    }

    public function shipment()
    {
        return $this->hasOne(Shipment::class);
    }

    public function returnModel()
    {
        return $this->hasOne(ReturnModel::class);
    }

    public function returns()
    {
        return $this->hasMany(ReturnModel::class);
    }

    public function damageReports()
    {
        return $this->hasMany(DamageReport::class);
    }

    public function damageCases()
    {
        return $this->hasManyThrough(DamageCase::class, DamageReport::class);
    }

    public function getStatusLabelAttribute(): string
    {
        $status = strtolower(trim(str_replace(['-', ' '], '_', $this->status ?? '')));

        return match ($status) {
            'menunggu_pembayaran', 'belum_dibayar' => 'Belum Dibayar',
            'menunggu_konfirmasi', 'menunggu_verifikasi', 'verifikasi' => 'Menunggu Verifikasi',
            'siap_kirim', 'diproses', 'dikonfirmasi' => 'Diproses',
            'dikirim' => 'Dikirim',
            'sedang_disewa' => 'Sedang Disewa',
            'selesai' => 'Selesai',
            'batal', 'dibatalkan', 'ditolak' => 'Batal',
            default => ucwords(str_replace('_', ' ', $this->status ?? 'Menunggu')),
        };
    }

    public function getStatusColorAttribute(): string
    {
        $status = strtolower(trim(str_replace(['-', ' '], '_', $this->status ?? '')));

        return match ($status) {
            'menunggu_pembayaran', 'belum_dibayar' => 'bg-red-600 text-white',
            'menunggu_konfirmasi', 'menunggu_verifikasi', 'verifikasi' => 'bg-amber-500 text-white',
            'siap_kirim', 'diproses', 'dikonfirmasi' => 'bg-indigo-600 text-white',
            'dikirim' => 'bg-purple-600 text-white',
            'sedang_disewa' => 'bg-blue-600 text-white',
            'selesai' => 'bg-green-600 text-white',
            'batal', 'dibatalkan', 'ditolak' => 'bg-gray-500 text-white',
            default => 'bg-gray-600 text-white',
        };
    }

    public function getDateRangeAttribute(): string
    {
        if ($this->rental_start_date && $this->rental_end_date) {
            if ($this->rental_start_date->isSameDay($this->rental_end_date)) {
                return $this->rental_start_date->translatedFormat('d M Y');
            }

            if ($this->rental_start_date->isSameMonth($this->rental_end_date) && $this->rental_start_date->isSameYear($this->rental_end_date)) {
                return $this->rental_start_date->translatedFormat('d').' - '.$this->rental_end_date->translatedFormat('d M Y');
            }

            return $this->rental_start_date->translatedFormat('d M').' - '.$this->rental_end_date->translatedFormat('d M Y');
        }

        return $this->created_at ? $this->created_at->translatedFormat('d M Y') : '-';
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'Rp '.number_format((float) ($this->total_amount ?? 0), 0, ',', '.');
    }

    public function getPrimaryImageAttribute(): string
    {
        $firstItem = $this->orderItems->first();
        $image = $firstItem?->product?->main_image;

        if ($image) {
            return str_starts_with($image, 'http')
                ? $image
                : Storage::url($image);
        }

        return 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&q=80&w=200';
    }

    public function getItemsCountAttribute(): int
    {
        $count = $this->orderItems->sum('quantity');

        return $count > 0 ? (int) $count : max(1, $this->orderItems->count());
    }

    public function getStepIndexAttribute(): int
    {
        $status = strtolower(trim(str_replace(['-', ' '], '_', $this->status ?? '')));

        return match ($status) {
            'menunggu_konfirmasi' => ($this->identityGuarantee?->status === 'diverifikasi' ? 1 : 0),
            'menunggu_pembayaran', 'belum_dibayar' => 1,
            'menunggu_verifikasi', 'verifikasi' => 2,
            'siap_kirim', 'diproses', 'dikonfirmasi' => 3,
            'dikirim' => 3,
            'sedang_disewa' => 4,
            'dikembalikan' => 5,
            'selesai' => 6,
            'batal', 'dibatalkan', 'ditolak' => 1,
            default => 0,
        };
    }

    public function getStatusNoteAttribute(): string
    {
        $status = strtolower(trim(str_replace(['-', ' '], '_', $this->status ?? '')));

        return match ($status) {
            'menunggu_pembayaran', 'belum_dibayar' => 'Selesaikan pembayaran agar pesanan dapat diproses oleh toko rental.',
            'menunggu_konfirmasi' => 'Toko sedang memeriksa data pesanan dan verifikasi jaminan identitas Anda.',
            'menunggu_verifikasi', 'verifikasi' => 'Toko sedang memverifikasi bukti pembayaran yang Anda kirimkan.',
            'siap_kirim', 'diproses', 'dikonfirmasi' => 'Pembayaran terverifikasi! Toko sedang mempersiapkan unit barang pesanan Anda.',
            'dikirim' => 'Pesanan dalam perjalanan pengiriman. Tekan tombol “Terima Barang” setelah paket sampai di tujuan.',
            'sedang_disewa' => 'Masa sewa sedang berjalan. Harap gunakan barang dengan baik dan kembalikan tepat waktu sebelum masa sewa berakhir.',
            'selesai' => 'Terima kasih telah menyewa di toko kami! Pesanan ini telah selesai sepenuhnya.',
            'batal', 'dibatalkan', 'ditolak' => 'Pesanan ini telah dibatalkan / ditolak.',
            default => 'Status pesanan Anda sedang diproses oleh sistem.',
        };
    }

    public function getCanCancelAttribute(): bool
    {
        $status = strtolower(trim(str_replace(['-', ' '], '_', $this->status ?? '')));

        if (in_array($status, ['menunggu_pembayaran', 'belum_dibayar'])) {
            return true;
        }

        if ($status === 'menunggu_konfirmasi') {
            $hasVerifiedPayment = $this->payments()->where('status', 'terverifikasi')->exists();
            return ! $hasVerifiedPayment;
        }

        return false;
    }

    public function getCanReturnAttribute(): bool
    {
        $status = strtolower(trim(str_replace(['-', ' '], '_', $this->status ?? '')));

        if (! in_array($status, ['sedang_disewa', 'siap_kembali'])) {
            return false;
        }

        return ! ($this->returnModel && in_array($this->returnModel->return_status, ['diajukan', 'diproses', 'dalam_pengembalian', 'diterima', 'selesai']));
    }

    public function getCanReceiveAttribute(): bool
    {
        $status = strtolower(trim(str_replace(['-', ' '], '_', $this->status ?? '')));

        return $status === 'dikirim';
    }

    public function getCanUploadPaymentAttribute(): bool
    {
        $status = strtolower(trim(str_replace(['-', ' '], '_', $this->status ?? '')));

        if (! in_array($status, ['menunggu_pembayaran', 'belum_dibayar', 'menunggu_konfirmasi'])) {
            return false;
        }

        // Jika ada verifikasi identitas, pastikan sudah diverifikasi (atau belum ada jaminan identitas)
        if ($this->identityGuarantee && $this->identityGuarantee->status !== 'diverifikasi') {
            return false;
        }

        $hasVerifiedPayment = $this->payments()->where('status', 'terverifikasi')->exists();

        return ! $hasVerifiedPayment;
    }

    public function getCanReuploadIdentityAttribute(): bool
    {
        $status = strtolower(trim(str_replace(['-', ' '], '_', $this->status ?? '')));

        if (in_array($status, ['selesai', 'batal', 'dibatalkan', 'ditolak'])) {
            return false;
        }

        return $this->identityGuarantee && $this->identityGuarantee->status === 'ditolak';
    }
}
