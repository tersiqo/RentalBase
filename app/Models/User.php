<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'client_id',
    'name',
    'email',
    'password',
    'role',
    'status',
    'phone',
    'address',
    'avatar',
])]
#[Hidden([
    'password',
    'remember_token',
])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    /**
     * Memeriksa apakah user adalah Admin Utama toko (Primary Admin).
     */
    public function isPrimaryAdmin(): bool
    {
        if ($this->role !== 'admin_rental' || ! $this->client_id) {
            return false;
        }

        if ($this->client?->primary_admin_id) {
            return (int) $this->client->primary_admin_id === (int) $this->id;
        }

        $firstAdminId = static::where('role', 'admin_rental')
            ->where('client_id', $this->client_id)
            ->oldest('id')
            ->value('id');

        return (int) $firstAdminId === (int) $this->id;
    }

    /**
     * Memeriksa apakah user adalah Admin Staf toko.
     */
    public function isStaffAdmin(): bool
    {
        return $this->role === 'admin_rental' && ! $this->isPrimaryAdmin();
    }
}
