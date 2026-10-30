<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'user_id',
        'title',
        'message',
        'type',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke User penerima notifikasi.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke Client/Toko rental terkait.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Scope untuk menyaring notifikasi yang belum dibaca.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope untuk menyaring notifikasi yang sudah dibaca.
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Helper visual badge berdasarkan tipe notifikasi.
     */
    public function getTypeBadgeAttribute(): array
    {
        return match ($this->type) {
            'order' => [
                'label' => 'Pesanan',
                'bg' => 'bg-blue-50 text-blue-700 border-blue-200',
                'dot' => 'bg-blue-500',
            ],
            'payment' => [
                'label' => 'Pembayaran',
                'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'dot' => 'bg-emerald-500',
            ],
            'shipment' => [
                'label' => 'Pengiriman',
                'bg' => 'bg-purple-50 text-purple-700 border-purple-200',
                'dot' => 'bg-purple-500',
            ],
            'return' => [
                'label' => 'Pengembalian',
                'bg' => 'bg-amber-50 text-amber-700 border-amber-200',
                'dot' => 'bg-amber-500',
            ],
            'identity' => [
                'label' => 'Identitas',
                'bg' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                'dot' => 'bg-indigo-500',
            ],
            'damage' => [
                'label' => 'Kompensasi',
                'bg' => 'bg-red-50 text-red-700 border-red-200',
                'dot' => 'bg-red-500',
            ],
            'quota' => [
                'label' => 'Kuota Toko',
                'bg' => 'bg-amber-50 text-amber-700 border-amber-200',
                'dot' => 'bg-amber-500',
            ],
            'registration', 'tenant_registration' => [
                'label' => 'Pendaftaran Tenant',
                'bg' => 'bg-orange-50 text-orange-700 border-orange-200',
                'dot' => 'bg-orange-500',
            ],
            'subscription', 'subscription_renewal' => [
                'label' => 'Subscription',
                'bg' => 'bg-purple-50 text-purple-700 border-purple-200',
                'dot' => 'bg-purple-500',
            ],
            'alert' => [
                'label' => 'Peringatan',
                'bg' => 'bg-red-50 text-red-700 border-red-200',
                'dot' => 'bg-red-500',
            ],
            'system' => [
                'label' => 'Sistem Toko',
                'bg' => 'bg-slate-50 text-slate-700 border-slate-200',
                'dot' => 'bg-slate-500',
            ],
            default => [
                'label' => 'Informasi',
                'bg' => 'bg-gray-50 text-gray-700 border-gray-200',
                'dot' => 'bg-gray-500',
            ],
        };
    }

    /**
     * URL tautan target aksi untuk Owner Platform.
     */
    public function getOwnerTargetUrlAttribute(): string
    {
        if (in_array($this->type, ['registration', 'tenant_registration'])) {
            return route('owner.registrations.index');
        }

        if (in_array($this->type, ['subscription', 'subscription_renewal'])) {
            return route('owner.subscriptions.index');
        }

        if (in_array($this->type, ['quota', 'tenant_quota'])) {
            return route('owner.tenants.index');
        }

        return route('owner.notifications.index');
    }

    /**
     * URL tautan target aksi untuk Admin.
     */
    public function getAdminTargetUrlAttribute(): string
    {
        if (in_array($this->type, ['order', 'payment', 'identity'])) {
            if (preg_match('/#(\d+)/', $this->title . ' ' . $this->message, $matches)) {
                return route('admin.orders.show', $matches[1]);
            }
            return route('admin.orders.index');
        }

        if ($this->type === 'return') {
            return route('admin.returns.index');
        }

        if ($this->type === 'damage') {
            if (preg_match('/#(\d+)/', $this->title . ' ' . $this->message, $matches)) {
                return route('admin.damage-cases.show', $matches[1]);
            }
            return route('admin.damage-cases.index');
        }

        if (in_array($this->type, ['quota', 'system'])) {
            return route('admin.settings.profile');
        }

        return route('admin.notifications.index');
    }
}

