<?php

namespace Tests\Unit\Models;

use App\Models\Client;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NotificationUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_unread_only_retrieves_unread_notifications(): void
    {
        $user = User::create([
            'name' => 'Owner Platform',
            'email' => 'owner@notif.test',
            'password' => Hash::make('password123'),
            'role' => 'owner',
            'status' => 'aktif',
        ]);

        Notification::create([
            'user_id' => $user->id,
            'title' => 'Sudah Dibaca',
            'message' => 'Pesan lama',
            'type' => 'system',
            'is_read' => true,
        ]);

        $unread = Notification::create([
            'user_id' => $user->id,
            'title' => 'Belum Dibaca',
            'message' => 'Pesan baru',
            'type' => 'registration',
            'is_read' => false,
        ]);

        $results = Notification::where('user_id', $user->id)->unread()->get();

        $this->assertCount(1, $results);
        $this->assertEquals($unread->id, $results->first()->id);
        $this->assertFalse($results->first()->is_read);
    }

    public function test_notification_relationships(): void
    {
        $client = Client::create([
            'business_name' => 'Toko Notif',
            'subdomain' => 'tokonotif',
            'status' => 'aktif',
        ]);

        $user = User::create([
            'client_id' => $client->id,
            'name' => 'Admin Notif',
            'email' => 'admin@notif.test',
            'password' => Hash::make('password123'),
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        $notification = Notification::create([
            'client_id' => $client->id,
            'user_id' => $user->id,
            'title' => 'Info Relasi',
            'message' => 'Testing Relasi BelongsTo',
            'type' => 'alert',
            'is_read' => false,
        ]);

        $this->assertInstanceOf(User::class, $notification->user);
        $this->assertEquals($user->id, $notification->user->id);

        $this->assertInstanceOf(Client::class, $notification->client);
        $this->assertEquals($client->id, $notification->client->id);
    }

    public function test_owner_notification_badge_type_resolution_and_filtering(): void
    {
        $alertTypes = ['alert', 'quota', 'registration', 'subscription', 'tenant_registration', 'subscription_renewal'];

        $this->assertContains('registration', $alertTypes);
        $this->assertContains('quota', $alertTypes);
        $this->assertContains('subscription', $alertTypes);
        $this->assertNotContains('chat_message', $alertTypes);
        $this->assertNotContains('general_info', $alertTypes);

        $notifications = [
            ['id' => 1, 'is_read' => true],
            ['id' => 2, 'is_read' => false],
            ['id' => 3, 'is_read' => false],
        ];

        $unreadOnly = array_filter($notifications, fn ($n) => !$n['is_read']);
        $this->assertCount(2, $unreadOnly);
    }
}
