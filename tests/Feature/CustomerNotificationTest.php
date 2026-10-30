<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_guest_cannot_access_notifications(): void
    {
        $response = $this->get('/notifications');

        $response->assertRedirect();
    }

    public function test_authenticated_customer_can_view_notifications_page(): void
    {
        $client = Client::create([
            'business_name' => 'Kamera Rental',
            'subdomain' => 'kamerarental',
            'status' => 'aktif',
        ]);

        $user = User::factory()->create([
            'client_id' => $client->id,
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        Notification::create([
            'client_id' => $client->id,
            'user_id' => $user->id,
            'title' => 'Pesanan Dikonfirmasi',
            'message' => 'Pesanan sewa kamera kamu telah dikonfirmasi.',
            'type' => 'order',
            'is_read' => false,
        ]);

        $response = $this->actingAs($user)->get('/notifications');

        $response->assertStatus(200);
        $response->assertSee('Pusat Pemberitahuan');
        $response->assertSee('Pesanan Dikonfirmasi');
        $response->assertSee('Pesanan sewa kamera kamu telah dikonfirmasi.');
    }

    public function test_customer_can_only_see_their_own_notifications(): void
    {
        $client = Client::create([
            'business_name' => 'Kamera Rental',
            'subdomain' => 'kamerarental',
            'status' => 'aktif',
        ]);

        $customerA = User::factory()->create([
            'client_id' => $client->id,
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        $customerB = User::factory()->create([
            'client_id' => $client->id,
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        Notification::create([
            'client_id' => $client->id,
            'user_id' => $customerA->id,
            'title' => 'Notif Milik Customer A',
            'message' => 'Pesan rahasia untuk Customer A',
            'type' => 'order',
            'is_read' => false,
        ]);

        Notification::create([
            'client_id' => $client->id,
            'user_id' => $customerB->id,
            'title' => 'Notif Milik Customer B',
            'message' => 'Pesan rahasia untuk Customer B',
            'type' => 'order',
            'is_read' => false,
        ]);

        $response = $this->actingAs($customerA)->get('/notifications');

        $response->assertStatus(200);
        $response->assertSee('Notif Milik Customer A');
        $response->assertDontSee('Notif Milik Customer B');
    }

    public function test_customer_can_mark_single_notification_as_read(): void
    {
        $client = Client::create([
            'business_name' => 'Kamera Rental',
            'subdomain' => 'kamerarental',
            'status' => 'aktif',
        ]);

        $user = User::factory()->create([
            'client_id' => $client->id,
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        $notification = Notification::create([
            'client_id' => $client->id,
            'user_id' => $user->id,
            'title' => 'Resi Pengiriman Tersedia',
            'message' => 'Barang dikirim via JNE nomor resi 123456.',
            'type' => 'shipment',
            'is_read' => false,
        ]);

        $response = $this->actingAs($user)->post("/notifications/{$notification->id}/read");

        $response->assertRedirect(route('customer.notifications.index'));
        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'is_read' => true,
        ]);
    }

    public function test_customer_cannot_mark_other_customer_notification_as_read(): void
    {
        $client = Client::create([
            'business_name' => 'Kamera Rental',
            'subdomain' => 'kamerarental',
            'status' => 'aktif',
        ]);

        $customerA = User::factory()->create([
            'client_id' => $client->id,
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        $customerB = User::factory()->create([
            'client_id' => $client->id,
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        $notificationB = Notification::create([
            'client_id' => $client->id,
            'user_id' => $customerB->id,
            'title' => 'Pemberitahuan Khusus Customer B',
            'message' => 'Jangan baca ini',
            'type' => 'order',
            'is_read' => false,
        ]);

        $response = $this->actingAs($customerA)->post("/notifications/{$notificationB->id}/read");

        $response->assertStatus(403);
        $this->assertDatabaseHas('notifications', [
            'id' => $notificationB->id,
            'is_read' => false,
        ]);
    }

    public function test_customer_can_mark_all_notifications_as_read(): void
    {
        $client = Client::create([
            'business_name' => 'Kamera Rental',
            'subdomain' => 'kamerarental',
            'status' => 'aktif',
        ]);

        $user = User::factory()->create([
            'client_id' => $client->id,
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        Notification::create([
            'client_id' => $client->id,
            'user_id' => $user->id,
            'title' => 'Notif 1',
            'message' => 'Pesan 1',
            'type' => 'order',
            'is_read' => false,
        ]);

        Notification::create([
            'client_id' => $client->id,
            'user_id' => $user->id,
            'title' => 'Notif 2',
            'message' => 'Pesan 2',
            'type' => 'payment',
            'is_read' => false,
        ]);

        $response = $this->actingAs($user)->post('/notifications/mark-all-read');

        $response->assertRedirect();
        $this->assertEquals(0, Notification::where('user_id', $user->id)->where('is_read', false)->count());
    }
}
