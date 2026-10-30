<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_guest_cannot_access_admin_notifications(): void
    {
        $response = $this->get('/admin/notifications');

        $response->assertRedirect('/');
    }

    public function test_customer_cannot_access_admin_notifications(): void
    {
        $client = Client::create([
            'business_name' => 'Toko Rental Utama',
            'subdomain' => 'tokoutama',
            'status' => 'aktif',
        ]);

        $customer = User::factory()->create([
            'client_id' => $client->id,
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($customer)->get('/admin/notifications');

        $response->assertRedirect('/');
    }

    public function test_admin_can_view_store_notifications(): void
    {
        $client = Client::create([
            'business_name' => 'Toko Rental Utama',
            'subdomain' => 'tokoutama',
            'status' => 'aktif',
        ]);

        $admin = User::factory()->create([
            'client_id' => $client->id,
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        Notification::create([
            'client_id' => $client->id,
            'user_id' => $admin->id,
            'title' => 'Pesanan Baru #99 Masuk',
            'message' => 'Customer telah membuat pesanan baru menunggu pembayaran.',
            'type' => 'order',
            'is_read' => false,
        ]);

        $response = $this->actingAs($admin)->get('/admin/notifications');

        $response->assertStatus(200);
        $response->assertSee('Pusat Pemberitahuan Toko');
        $response->assertSee('Pesanan Baru #99 Masuk');
        $response->assertSee('Customer telah membuat pesanan baru menunggu pembayaran.');
    }

    public function test_admin_cannot_see_notifications_of_other_stores(): void
    {
        $storeA = Client::create([
            'business_name' => 'Toko Rental A',
            'subdomain' => 'tokurentala',
            'status' => 'aktif',
        ]);

        $storeB = Client::create([
            'business_name' => 'Toko Rental B',
            'subdomain' => 'tokurentalb',
            'status' => 'aktif',
        ]);

        $adminA = User::factory()->create([
            'client_id' => $storeA->id,
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        $adminB = User::factory()->create([
            'client_id' => $storeB->id,
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        Notification::create([
            'client_id' => $storeA->id,
            'user_id' => $adminA->id,
            'title' => 'Notif Rahasia Toko A',
            'message' => 'Hanya untuk Toko A',
            'type' => 'order',
            'is_read' => false,
        ]);

        Notification::create([
            'client_id' => $storeB->id,
            'user_id' => $adminB->id,
            'title' => 'Notif Rahasia Toko B',
            'message' => 'Hanya untuk Toko B',
            'type' => 'order',
            'is_read' => false,
        ]);

        $response = $this->actingAs($adminA)->get('/admin/notifications');

        $response->assertStatus(200);
        $response->assertSee('Notif Rahasia Toko A');
        $response->assertDontSee('Notif Rahasia Toko B');
    }

    public function test_admin_can_mark_notification_as_read(): void
    {
        $client = Client::create([
            'business_name' => 'Toko Rental Utama',
            'subdomain' => 'tokoutama',
            'status' => 'aktif',
        ]);

        $admin = User::factory()->create([
            'client_id' => $client->id,
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        $notification = Notification::create([
            'client_id' => $client->id,
            'user_id' => $admin->id,
            'title' => 'Bukti Pembayaran Diunggah',
            'message' => 'Customer mengunggah struk transfer.',
            'type' => 'payment',
            'is_read' => false,
        ]);

        $response = $this->actingAs($admin)->post('/admin/notifications/' . $notification->id . '/read');

        $response->assertRedirect();
        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'is_read' => true,
        ]);
    }

    public function test_admin_cannot_mark_other_store_notification_as_read(): void
    {
        $storeA = Client::create([
            'business_name' => 'Toko Rental A',
            'subdomain' => 'tokurentala',
            'status' => 'aktif',
        ]);

        $storeB = Client::create([
            'business_name' => 'Toko Rental B',
            'subdomain' => 'tokurentalb',
            'status' => 'aktif',
        ]);

        $adminA = User::factory()->create([
            'client_id' => $storeA->id,
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        $adminB = User::factory()->create([
            'client_id' => $storeB->id,
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        $notifStoreB = Notification::create([
            'client_id' => $storeB->id,
            'user_id' => $adminB->id,
            'title' => 'Notif Milik Toko B',
            'message' => 'Operasional Toko B',
            'type' => 'order',
            'is_read' => false,
        ]);

        $response = $this->actingAs($adminA)->post('/admin/notifications/' . $notifStoreB->id . '/read');

        $response->assertStatus(403);
        $this->assertDatabaseHas('notifications', [
            'id' => $notifStoreB->id,
            'is_read' => false,
        ]);
    }

    public function test_admin_can_mark_all_notifications_as_read(): void
    {
        $client = Client::create([
            'business_name' => 'Toko Rental Utama',
            'subdomain' => 'tokoutama',
            'status' => 'aktif',
        ]);

        $admin = User::factory()->create([
            'client_id' => $client->id,
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        Notification::create([
            'client_id' => $client->id,
            'user_id' => $admin->id,
            'title' => 'Pemberitahuan 1',
            'message' => 'Pesan 1',
            'type' => 'order',
            'is_read' => false,
        ]);

        Notification::create([
            'client_id' => $client->id,
            'user_id' => $admin->id,
            'title' => 'Pemberitahuan 2',
            'message' => 'Pesan 2',
            'type' => 'payment',
            'is_read' => false,
        ]);

        $response = $this->actingAs($admin)->post('/admin/notifications/mark-all-read');

        $response->assertRedirect();
        $this->assertEquals(0, Notification::where('client_id', $client->id)->where('is_read', false)->count());
    }
}
