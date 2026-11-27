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
     * Otomatis kirimkan Web Push Notification (OS native) saat notifikasi dibuat.
     */
    protected static function booted(): void
    {
        static::created(function (self $notification) {
            try {
                $service = app(\App\Services\WebPushService::class);
                $recipient = $notification->user;
                $role = $recipient?->role;

                // Tentukan target URL akurat berdasarkan role penerima & jenis notifikasi
                if ($role === 'customer') {
                    $targetUrl = $notification->customer_target_url;
                } elseif ($role === 'admin_rental' || ($notification->client_id && $role !== 'owner')) {
                    $targetUrl = $notification->admin_target_url;
                } else {
                    $targetUrl = $notification->owner_target_url;
                }

                $payload = [
                    'title' => $notification->title,
                    'body'  => $notification->message,
                    'url'   => $targetUrl,
                    'type'  => $notification->type,
                    'tag'   => 'rb-notif-' . $notification->id,
                ];

                if ($role === 'admin_rental' && $notification->client_id) {
                    $service->sendToClientAdmins((int) $notification->client_id, $payload);
                } elseif ($notification->user_id) {
                    $service->sendToUser((int) $notification->user_id, $payload);
                } elseif ($notification->client_id) {
                    $service->sendToClientAdmins((int) $notification->client_id, $payload);
                } else {
                    $service->sendToOwner($payload);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('[WebPush] Error hook Notification: ' . $e->getMessage());
            }
        });
    }

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
                'bg' => 'bg-blue-50 text-blue-700',
                'dot' => 'bg-blue-500',
            ],
            'payment' => [
                'label' => 'Pembayaran',
                'bg' => 'bg-emerald-50 text-emerald-700',
                'dot' => 'bg-emerald-500',
            ],
            'shipment' => [
                'label' => 'Pengiriman',
                'bg' => 'bg-purple-50 text-purple-700',
                'dot' => 'bg-purple-500',
            ],
            'return' => [
                'label' => 'Pengembalian',
                'bg' => 'bg-amber-50 text-amber-700',
                'dot' => 'bg-amber-500',
            ],
            'identity' => [
                'label' => 'Identitas',
                'bg' => 'bg-indigo-50 text-indigo-700',
                'dot' => 'bg-indigo-500',
            ],
            'damage' => [
                'label' => 'Kompensasi',
                'bg' => 'bg-red-50 text-red-700',
                'dot' => 'bg-red-500',
            ],
            'late_fee' => [
                'label' => 'Denda',
                'bg' => 'bg-rose-50 text-rose-700',
                'dot' => 'bg-rose-500',
            ],
            'quota' => [
                'label' => 'Kuota Toko',
                'bg' => 'bg-amber-50 text-amber-700',
                'dot' => 'bg-amber-500',
            ],
            'registration', 'tenant_registration' => [
                'label' => 'Pendaftaran Tenant',
                'bg' => 'bg-orange-50 text-orange-700',
                'dot' => 'bg-orange-500',
            ],
            'subscription', 'subscription_renewal' => [
                'label' => 'Subscription',
                'bg' => 'bg-purple-50 text-purple-700',
                'dot' => 'bg-purple-500',
            ],
            'alert' => [
                'label' => 'Peringatan',
                'bg' => 'bg-red-50 text-red-700',
                'dot' => 'bg-red-500',
            ],
            'system' => [
                'label' => 'Sistem Toko',
                'bg' => 'bg-slate-50 text-slate-700',
                'dot' => 'bg-slate-500',
            ],
            default => [
                'label' => 'Informasi',
                'bg' => 'bg-gray-50 text-gray-700',
                'dot' => 'bg-gray-500',
            ],
        };
    }

    /**
     * URL tautan target aksi untuk Customer (mendukung deep-linking ke pesanan terkait & bayar denda).
     */
    public function getCustomerTargetUrlAttribute(): string
    {
        $text = $this->title . ' ' . $this->message;
        $order = null;

        if (preg_match('/#(ORD-[A-Za-z0-9\-]+|\d+)/i', $text, $matches)) {
            $identifier = $matches[1];
            $order = ctype_digit((string) $identifier)
                ? Order::where('id', (int) $identifier)->orWhere('order_number', $identifier)->first()
                : Order::where('order_number', $identifier)->first();
        }

        if ($order) {
            $isLateFee = in_array($this->type, ['late_fee'])
                || str_contains(strtolower($text), 'denda')
                || str_contains(strtolower($text), 'keterlambatan');

            if ($isLateFee) {
                return route('customer.orders.show', $order->id) . '?action=pay_late_fee';
            }

            return route('customer.orders.show', $order->id);
        }

        return \Illuminate\Support\Facades\Route::has('customer.orders')
            ? route('customer.orders')
            : url('/my-orders');
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
        if (in_array($this->type, ['order', 'payment', 'identity', 'late_fee'])) {
            if (preg_match('/#(ORD-[A-Za-z0-9\-]+|\d+)/i', $this->title . ' ' . $this->message, $matches)) {
                $identifier = $matches[1];
                $order = ctype_digit((string) $identifier)
                    ? Order::where('id', (int) $identifier)->orWhere('order_number', $identifier)->first()
                    : Order::where('order_number', $identifier)->first();

                if ($order) {
                    return route('admin.orders.show', $order->id);
                }

                if (ctype_digit((string) $identifier)) {
                    return route('admin.orders.show', (int) $identifier);
                }
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

