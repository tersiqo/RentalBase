<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'payment_methods';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'client_id',
        'type',
        'bank_name',
        'account_number',
        'account_name',
        'qris_merchant_name',
        'qris_image_path',
        'instructions',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'client_id' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relasi ke Client (Tenant) pemilik metode pembayaran.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    /**
     * Alias relasi ke Tenant (Client).
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    /**
     * Scope lokal untuk memfilter metode pembayaran yang berstatus aktif.
     *
     * Contoh penggunaan: PaymentMethod::active()->get();
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Cek apakah tipe metode pembayaran adalah transfer bank.
     */
    public function isBankTransfer(): bool
    {
        return $this->type === 'bank_transfer';
    }

    /**
     * Cek apakah tipe metode pembayaran adalah QRIS.
     */
    public function isQris(): bool
    {
        return $this->type === 'qris';
    }

    /**
     * Aksesor kompatibilitas untuk view lama yang membaca account_holder.
     */
    public function getAccountHolderAttribute(): ?string
    {
        return $this->account_name;
    }

    /**
     * Aksesor kompatibilitas untuk view lama yang membaca qris_image.
     */
    public function getQrisImageAttribute(): ?string
    {
        return $this->qris_image_path;
    }
}
