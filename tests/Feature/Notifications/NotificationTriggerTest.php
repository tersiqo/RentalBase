<?php

namespace Tests\Feature\Notifications;

use App\Events\DamageCaseCreated;
use App\Events\DamageCaseResolved;
use App\Events\FineIssued;
use App\Events\IdentityApproved;
use App\Events\IdentityProofUploaded;
use App\Events\IdentityRejected;
use App\Events\NewOrderPlaced;
use App\Events\OrderConfirmed;
use App\Events\OrderShipped;
use App\Events\PaymentProofUploaded;
use App\Events\PaymentRejected;
use App\Events\PaymentVerified;
use App\Events\ReturnAccepted;
use App\Events\ReturnRequested;
use App\Events\SubscriptionRenewalUploaded;
use App\Events\TenantQuotaAlert;
use App\Events\TenantRegistrationSubmitted;
use App\Models\Client;
use App\Models\ClientRegistration;
use App\Models\DamageCase;
use App\Models\DamageReport;
use App\Models\EquipmentUnit;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTriggerTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_notification_triggers_create_notifications(): void
    {
        $client = Client::create(['business_name' => 'Toko X', 'subdomain' => 'tokox']);
        $customer = User::factory()->create(['role' => 'customer']);

        $order = Order::create([
            'client_id' => $client->id,
            'user_id' => $customer->id,
            'order_number' => 'ORD-CUST-001',
            'rental_start_date' => now()->addDay(),
            'rental_end_date' => now()->addDays(2),
            'duration_days' => 1,
            'total_amount' => 100000,
            'shipping_address' => 'Bandung',
            'status' => 'menunggu_konfirmasi',
        ]);

        // 1. OrderConfirmed
        event(new OrderConfirmed($order));
        $this->assertDatabaseHas('notifications', [
            'user_id' => $customer->id,
            'type' => 'order',
            'title' => 'Pesanan Dikonfirmasi #ORD-CUST-001',
        ]);

        // 2. IdentityApproved
        event(new IdentityApproved($order));
        $this->assertDatabaseHas('notifications', [
            'user_id' => $customer->id,
            'type' => 'identity',
            'title' => 'Identitas Disetujui #ORD-CUST-001',
        ]);

        // 3. PaymentVerified
        event(new PaymentVerified($order));
        $this->assertDatabaseHas('notifications', [
            'user_id' => $customer->id,
            'type' => 'payment',
            'title' => 'Pembayaran Diverifikasi #ORD-CUST-001',
        ]);

        // 4. OrderShipped
        event(new OrderShipped($order));
        $this->assertDatabaseHas('notifications', [
            'user_id' => $customer->id,
            'type' => 'shipment',
            'title' => 'Barang Dikirim #ORD-CUST-001',
        ]);

        // 5. ReturnAccepted
        event(new ReturnAccepted($order));
        $this->assertDatabaseHas('notifications', [
            'user_id' => $customer->id,
            'type' => 'return',
            'title' => 'Pengembalian Diterima #ORD-CUST-001',
        ]);
    }

    public function test_admin_notification_triggers_create_notifications(): void
    {
        $client = Client::create(['business_name' => 'Toko Y', 'subdomain' => 'tokoy']);
        $admin = User::factory()->create([
            'client_id' => $client->id,
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);
        $customer = User::factory()->create(['role' => 'customer']);

        $order = Order::create([
            'client_id' => $client->id,
            'user_id' => $customer->id,
            'order_number' => 'ORD-ADM-001',
            'rental_start_date' => now()->addDay(),
            'rental_end_date' => now()->addDays(2),
            'duration_days' => 1,
            'total_amount' => 100000,
            'shipping_address' => 'Jakarta',
            'status' => 'menunggu_konfirmasi',
        ]);

        // 1. NewOrderPlaced
        event(new NewOrderPlaced($order));
        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'client_id' => $client->id,
            'type' => 'order',
            'title' => 'Pesanan Baru Masuk #ORD-ADM-001',
        ]);

        // 2. IdentityProofUploaded
        event(new IdentityProofUploaded($order));
        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'client_id' => $client->id,
            'type' => 'identity',
            'title' => 'Bukti Identitas Baru Diunggah #ORD-ADM-001',
        ]);

        // 3. PaymentProofUploaded
        event(new PaymentProofUploaded($order));
        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'client_id' => $client->id,
            'type' => 'payment',
            'title' => 'Bukti Transfer Baru Diunggah #ORD-ADM-001',
        ]);

        // 4. ReturnRequested
        event(new ReturnRequested($order));
        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'client_id' => $client->id,
            'type' => 'return',
            'title' => 'Pengajuan Pengembalian Diajukan #ORD-ADM-001',
        ]);
    }

    public function test_owner_notification_triggers_create_notifications(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'status' => 'aktif',
        ]);

        $client = Client::create(['business_name' => 'Toko Z', 'subdomain' => 'tokoz']);

        // 1. TenantRegistrationSubmitted
        $reg = ClientRegistration::create([
            'business_name' => 'Tenant Baru Sejahtera',
            'subdomain' => 'sejahtera',
            'plan_name' => 'starter',
            'admin_name' => 'Pak Budi',
            'admin_email' => 'budi@sejahtera.com',
            'admin_phone' => '08123456789',
            'admin_password' => 'secret123',
            'status' => 'menunggu_verifikasi',
        ]);

        event(new TenantRegistrationSubmitted($reg));
        $this->assertDatabaseHas('notifications', [
            'user_id' => $owner->id,
            'type' => 'registration',
            'title' => 'Pendaftaran Tenant Baru: Tenant Baru Sejahtera',
        ]);

        // 2. TenantQuotaAlert
        event(new TenantQuotaAlert($client, 42, 50, 84.0));
        $this->assertDatabaseHas('notifications', [
            'user_id' => $owner->id,
            'type' => 'quota',
            'title' => 'Peringatan Kuota Tenant Hampir Penuh: Toko Z',
        ]);
    }

    public function test_notification_listener_is_idempotent_within_short_window(): void
    {
        $client = Client::create(['business_name' => 'Toko W', 'subdomain' => 'tokow']);
        $customer = User::factory()->create(['role' => 'customer']);

        $order = Order::create([
            'client_id' => $client->id,
            'user_id' => $customer->id,
            'order_number' => 'ORD-IDEM-NOTIF-001',
            'rental_start_date' => now()->addDay(),
            'rental_end_date' => now()->addDays(2),
            'duration_days' => 1,
            'total_amount' => 100000,
            'shipping_address' => 'Surabaya',
            'status' => 'menunggu_konfirmasi',
        ]);

        // Trigger dua kali berturut-turut
        event(new OrderConfirmed($order));
        event(new OrderConfirmed($order));

        $count = Notification::where('user_id', $customer->id)
            ->where('title', 'Pesanan Dikonfirmasi #ORD-IDEM-NOTIF-001')
            ->count();

        $this->assertEquals(1, $count);
    }
}
