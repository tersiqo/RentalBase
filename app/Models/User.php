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

    /**
     * Memeriksa apakah user adalah Admin Utama toko (Primary Admin).
     */
    public function isPrimaryAdmin(): bool
    {
        if ($this->role !== 'admin_rental' || ! $this->client_id) {
            return false;
        }

        return (int) $this->client?->primary_admin_id === (int) $this->id;
    }

    /**
     * Memeriksa apakah user adalah Admin Staf toko.
     */
    public function isStaffAdmin(): bool
    {
        return $this->role === 'admin_rental' && ! $this->isPrimaryAdmin();
    }
}
