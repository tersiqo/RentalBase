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
}
