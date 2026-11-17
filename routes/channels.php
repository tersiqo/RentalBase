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

// Channel obrolan real-time (hanya untuk customer pemilik chat atau admin toko terkait)
Broadcast::channel('chat.conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = \App\Models\ChatConversation::find($conversationId);
    if (! $conversation) {
        return false;
    }

    if ((int) $user->id === (int) $conversation->customer_id) {
        return true;
    }

    if ($user->role === 'admin_rental' && (int) $user->client_id === (int) $conversation->client_id) {
        return true;
    }

    if ($user->role === 'owner') {
        return true;
    }

    return false;
});
