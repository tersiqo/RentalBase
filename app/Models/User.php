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

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function verifiedPayments()
    {
        return $this->hasMany(Payment::class, 'diverifikasi_oleh');
    }

    public function conditionChecks()
    {
        return $this->hasMany(ConditionCheck::class, 'diperiksa_oleh');
    }

    public function damageReports()
    {
        return $this->hasMany(DamageReport::class, 'dilaporkan_oleh');
    }

    public function identityGuarantees()
    {
        return $this->hasMany(IdentityGuarantee::class, 'customer_id');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class, 'user_id');
    }
}