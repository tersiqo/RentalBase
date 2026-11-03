<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdentityGuarantee extends Model
{
    protected $guarded = [];

    protected $casts = [
        'deposited_at' => 'datetime',
        'returned_at'  => 'datetime',
    ];

    public const STATUS_MENUNGGU = 'menunggu';
    public const STATUS_DIVERIFIKASI = 'diverifikasi';
    public const STATUS_DITOLAK = 'ditolak';
    public const STATUS_DITITIPKAN = 'dititipkan';
    public const STATUS_DIKEMBALIKAN = 'dikembalikan';

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function getStatusLabelAttribute(): string
    {
        $status = strtolower(trim($this->status ?? self::STATUS_MENUNGGU));

        return match ($status) {
            self::STATUS_MENUNGGU => 'Menunggu Verifikasi',
            self::STATUS_DIVERIFIKASI => 'Data Terverifikasi (Wajib Bawa KTP Fisik)',
            self::STATUS_DITITIPKAN => 'KTP Fisik Sedang Dititipkan',
            self::STATUS_DIKEMBALIKAN => 'KTP Fisik Telah Dikembalikan',
            self::STATUS_DITOLAK => 'Verifikasi Ditolak',
            default => ucwords(str_replace('_', ' ', $status)),
        };
    }

    public function getStatusColorAttribute(): string
    {
        $status = strtolower(trim($this->status ?? self::STATUS_MENUNGGU));

        return match ($status) {
            self::STATUS_MENUNGGU => 'bg-amber-50 text-amber-800 border border-amber-200',
            self::STATUS_DIVERIFIKASI => 'bg-blue-50 text-blue-800 border border-blue-200',
            self::STATUS_DITITIPKAN => 'bg-purple-50 text-purple-800 border border-purple-200',
            self::STATUS_DIKEMBALIKAN => 'bg-emerald-50 text-emerald-800 border border-emerald-200',
            self::STATUS_DITOLAK => 'bg-red-50 text-red-800 border border-red-200',
            default => 'bg-gray-50 text-gray-700 border border-gray-200',
        };
    }

    public function getIdentityDocumentUrlAttribute(): ?string
    {
        return storage_url($this->identity_document_image ?? $this->ktp_photo);
    }

    public function getFaceImageUrlAttribute(): ?string
    {
        return storage_url($this->face_image ?? $this->selfie_photo);
    }
}
