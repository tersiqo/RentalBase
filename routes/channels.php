<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels (RentalBase)
|--------------------------------------------------------------------------
*/

// Channel pesanan untuk admin rental per-toko
Broadcast::channel('tenant.{clientId}.orders', function ($user, $clientId) {
    return (int) $user->client_id === (int) $clientId || $user->role === 'owner';
});

// Channel notifikasi untuk owner SaaS platform
Broadcast::channel('owner.platform', function ($user) {
    return $user->role === 'owner';
});

// Channel pribadi customer untuk tracking pesanan mereka
Broadcast::channel('customer.{userId}.orders', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
