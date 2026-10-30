<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke User (pelaku aktivitas).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke Toko/Tenant terkait (jika ada).
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Scope untuk menyaring data berdasarkan filter pencarian Owner.
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        // 1. Filter Rentang Tanggal
        if (! empty($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }
        if (! empty($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        // 2. Filter Berdasarkan Tenant (Client ID)
        if (! empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }

        // 3. Filter Berdasarkan Pelaku (User ID)
        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // 4. Filter Berdasarkan Kategori Modul
        if (! empty($filters['module'])) {
            $query->where('module', $filters['module']);
        }

        // 5. Pencarian Teks Bebas pada Deskripsi atau Aksi
        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('description', 'ILIKE', "%{$search}%")
                  ->orWhere('action', 'ILIKE', "%{$search}%");
            });
        }

        return $query;
    }

    /**
     * Helper visual badge warna untuk modul aktivitas.
     */
    public function getModuleBadgeAttribute(): array
    {
        return match (strtolower($this->module ?? '')) {
            'orders' => [
                'label' => 'Pesanan',
                'bg'    => 'bg-blue-50 text-blue-700 border-blue-200',
            ],
            'shipments' => [
                'label' => 'Logistik & Pengiriman',
                'bg'    => 'bg-purple-50 text-purple-700 border-purple-200',
            ],
            'returns' => [
                'label' => 'Pengembalian & Denda',
                'bg'    => 'bg-amber-50 text-amber-700 border-amber-200',
            ],
            'inspections', 'condition_checks' => [
                'label' => 'Inspeksi Fisik',
                'bg'    => 'bg-teal-50 text-teal-700 border-teal-200',
            ],
            'subscriptions' => [
                'label' => 'Subscription Tenant',
                'bg'    => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            ],
            'tenants', 'auth' => [
                'label' => 'Akun & Tenant',
                'bg'    => 'bg-orange-50 text-orange-700 border-orange-200',
            ],
            default => [
                'label' => ucfirst($this->module ?? 'Sistem'),
                'bg'    => 'bg-slate-50 text-slate-700 border-slate-200',
            ],
        };
    }
}
