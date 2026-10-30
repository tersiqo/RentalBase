<?php

namespace Tests\Feature\Owner;

use App\Models\Client;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_guest_cannot_access_owner_notifications(): void
    {
        $response = $this->get('/owner/notifications');
        $response->assertRedirect('/owner/login');
    }

    public function test_admin_rental_cannot_access_owner_notifications(): void
    {
        $client = Client::create([
            'business_name' => 'Toko Rental Satu',
            'subdomain' => 'tokosatu',
            'status' => 'aktif',
        ]);

        $admin = User::factory()->create([
            'client_id' => $client->id,
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($admin)->get('/owner/notifications');
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_customer_cannot_access_owner_notifications(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($customer)->get('/owner/notifications');
        $response->assertRedirect(route('owner.login'));
    }

    public function test_owner_can_view_notifications_page(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'status' => 'aktif',
        ]);

        Notification::create([
            'user_id' => $owner->id,
            'title' => 'Pendaftaran Tenant Baru: KameraPro',
            'message' => 'Tenant KameraPro mendaftar di RentalBase.',
            'type' => 'registration',
            'is_read' => false,
        ]);

        $response = $this->actingAs($owner)->get('/owner/notifications');

        $response->assertStatus(200);
        $response->assertSee('Notifikasi Platform');
        $response->assertSee('Pendaftaran Tenant Baru: KameraPro');
    }

    public function test_unread_count_only_counts_logged_in_owner_notifications(): void
    {
        $owner1 = User::factory()->create(['role' => 'owner', 'status' => 'aktif']);
        $owner2 = User::factory()->create(['role' => 'owner', 'status' => 'aktif']);

        Notification::create([
            'user_id' => $owner1->id,
            'title' => 'Notif Owner 1',
            'message' => 'Pesan 1',
            'type' => 'registration',
            'is_read' => false,
        ]);

        Notification::create([
            'user_id' => $owner2->id,
            'title' => 'Notif Owner 2',
            'message' => 'Pesan 2',
            'type' => 'subscription',
            'is_read' => false,
        ]);

        $response = $this->actingAs($owner1)->get('/owner/notifications');

        $response->assertStatus(200);
        $response->assertSee('Notif Owner 1');
        $response->assertDontSee('Notif Owner 2');
    }

    public function test_tabs_filtering_works_correctly(): void
    {
        $owner = User::factory()->create(['role' => 'owner', 'status' => 'aktif']);

        $notifUnread = Notification::create([
            'user_id' => $owner->id,
            'title' => 'Notif Belum Dibaca',
            'message' => 'Pesan unread',
            'type' => 'system',
            'is_read' => false,
        ]);

        $notifRead = Notification::create([
            'user_id' => $owner->id,
            'title' => 'Notif Sudah Dibaca',
            'message' => 'Pesan read',
            'type' => 'system',
            'is_read' => true,
        ]);

        $notifAlert = Notification::create([
            'user_id' => $owner->id,
            'title' => 'Peringatan Kuota Tenant',
            'message' => 'Kuota hampir habis',
            'type' => 'quota',
            'is_read' => false,
        ]);

        // Tab: unread
        $responseUnread = $this->actingAs($owner)->get('/owner/notifications?tab=unread');
        $responseUnread->assertSee('Notif Belum Dibaca');
        $responseUnread->assertDontSee('Notif Sudah Dibaca');

        // Tab: alert
        $responseAlert = $this->actingAs($owner)->get('/owner/notifications?tab=alert');
        $responseAlert->assertSee('Peringatan Kuota Tenant');
        $responseAlert->assertDontSee('Notif Belum Dibaca');
    }

    public function test_owner_can_mark_single_notification_as_read(): void
    {
        $owner = User::factory()->create(['role' => 'owner', 'status' => 'aktif']);

        $notif = Notification::create([
            'user_id' => $owner->id,
            'title' => 'Test Notif',
            'message' => 'Test',
            'type' => 'registration',
            'is_read' => false,
        ]);

        $response = $this->actingAs($owner)->post("/owner/notifications/{$notif->id}/read");

        $response->assertRedirect();
        $this->assertTrue($notif->fresh()->is_read);
    }

    public function test_owner_cannot_mark_other_user_notification_as_read(): void
    {
        $owner1 = User::factory()->create(['role' => 'owner', 'status' => 'aktif']);
        $owner2 = User::factory()->create(['role' => 'owner', 'status' => 'aktif']);

        $notif = Notification::create([
            'user_id' => $owner2->id,
            'title' => 'Notif Milik Owner 2',
            'message' => 'Test',
            'type' => 'registration',
            'is_read' => false,
        ]);

        $response = $this->actingAs($owner1)->post("/owner/notifications/{$notif->id}/read");

        $response->assertStatus(403);
        $this->assertFalse($notif->fresh()->is_read);
    }

    public function test_owner_can_mark_all_notifications_as_read(): void
    {
        $owner = User::factory()->create(['role' => 'owner', 'status' => 'aktif']);
        $otherUser = User::factory()->create(['role' => 'customer', 'status' => 'aktif']);

        $n1 = Notification::create([
            'user_id' => $owner->id,
            'title' => 'Notif 1',
            'message' => 'Test',
            'type' => 'registration',
            'is_read' => false,
        ]);

        $n2 = Notification::create([
            'user_id' => $owner->id,
            'title' => 'Notif 2',
            'message' => 'Test',
            'type' => 'subscription',
            'is_read' => false,
        ]);

        $otherNotif = Notification::create([
            'user_id' => $otherUser->id,
            'title' => 'Notif Other',
            'message' => 'Test',
            'type' => 'order',
            'is_read' => false,
        ]);

        $response = $this->actingAs($owner)->post('/owner/notifications/mark-all-read');

        $response->assertRedirect();
        $this->assertTrue($n1->fresh()->is_read);
        $this->assertTrue($n2->fresh()->is_read);
        $this->assertFalse($otherNotif->fresh()->is_read);
    }
}
