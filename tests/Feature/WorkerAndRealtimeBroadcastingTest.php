<?php

namespace Tests\Feature;

use App\Events\NewOrderPlaced;
use App\Jobs\AutoCompleteOrderJob;
use App\Jobs\CancelUnpaidOrderJob;
use App\Listeners\SendInAppNotification;
use App\Models\Category;
use App\Models\Client;
use App\Models\ClientPaymentMethod;
use App\Models\EquipmentUnit;
use App\Models\IdentityGuarantee;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemUnit;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ReturnModel;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkerAndRealtimeBroadcastingTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Client::create([
            'business_name' => 'Baby Studio Rental',
            'subdomain' => 'babystudio',
            'status' => 'aktif',
        ]);

        $this->customer = User::create([
            'name' => 'Budi Customer',
            'email' => 'budi@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'client_id' => null,
            'status' => 'aktif',
        ]);
    }

    /**
     * Test Worker: CancelUnpaidOrderJob otomatis membatalkan pesanan belum dibayar dan melepaskan unit fisik.
     */
    public function test_cancel_unpaid_order_job_cancels_order_and_releases_units(): void
    {
        $category = Category::create([
            'client_id' => $this->client->id,
            'name' => 'Stroller',
            'slug' => 'stroller',
        ]);

        $product = Product::create([
            'client_id' => $this->client->id,
            'category_id' => $category->id,
            'name' => 'Babyzen YOYO2',
            'slug' => 'babyzen-yoyo2',
            'rental_price_per_day' => 50000,
            'status' => 'tersedia',
        ]);

        $unit = EquipmentUnit::create([
            'client_id' => $this->client->id,
            'product_id' => $product->id,
            'serial_number' => 'SN-YOYO-001',
            'unit_code' => 'U-001',
            'status' => 'disewa',
            'condition' => 'baik',
        ]);

        $order = Order::create([
            'client_id' => $this->client->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-TEST-001',
            'rental_start_date' => now()->addDay()->toDateString(),
            'rental_end_date' => now()->addDays(3)->toDateString(),
            'duration_days' => 2,
            'total_amount' => 100000,
            'shipping_address' => 'Jl. Mawar No. 123, Malang',
            'shipping_method' => 'pickup',
            'status' => 'menunggu_pembayaran',
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price_per_day' => 50000,
            'subtotal' => 50000,
        ]);

        OrderItemUnit::create([
            'order_item_id' => $orderItem->id,
            'equipment_unit_id' => $unit->id,
        ]);

        // Jalankan Job
        (new CancelUnpaidOrderJob($order->id))->handle();

        $order->refresh();
        $unit->refresh();

        $this->assertEquals('dibatalkan', $order->status);
        $this->assertEquals('tersedia', $unit->status);
    }

    /**
     * Test Worker: CancelUnpaidOrderJob mengabaikan pesanan yang sudah memiliki pembayaran terverifikasi.
     */
    public function test_cancel_unpaid_order_job_does_not_cancel_verified_paid_orders(): void
    {
        $order = Order::create([
            'client_id' => $this->client->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-TEST-002',
            'rental_start_date' => now()->addDay()->toDateString(),
            'rental_end_date' => now()->addDays(3)->toDateString(),
            'duration_days' => 2,
            'total_amount' => 100000,
            'shipping_address' => 'Jl. Mawar No. 123, Malang',
            'shipping_method' => 'pickup',
            'status' => 'menunggu_pembayaran',
        ]);

        $method = ClientPaymentMethod::create([
            'client_id' => $this->client->id,
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_holder' => 'Baby Studio',
            'is_active' => true,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'client_payment_method_id' => $method->id,
            'amount' => 100000,
            'payment_proof' => 'proof.jpg',
            'status' => 'terverifikasi',
        ]);

        // Jalankan Job
        (new CancelUnpaidOrderJob($order->id))->handle();

        $order->refresh();
        $this->assertEquals('menunggu_pembayaran', $order->status);
    }

    /**
     * Test Worker: AutoCompleteOrderJob otomatis menyelesaikan pesanan dan mengembalikan jaminan KTP.
     */
    public function test_auto_complete_order_job_completes_order_and_releases_identity(): void
    {
        $order = Order::create([
            'client_id' => $this->client->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-TEST-003',
            'rental_start_date' => now()->subDays(5)->toDateString(),
            'rental_end_date' => now()->subDays(2)->toDateString(),
            'duration_days' => 3,
            'total_amount' => 150000,
            'shipping_address' => 'Jl. Mawar No. 123, Malang',
            'shipping_method' => 'pickup',
            'status' => 'dikembalikan',
        ]);

        $identity = IdentityGuarantee::create([
            'order_id' => $order->id,
            'customer_id' => $this->customer->id,
            'full_name' => 'Budi Santoso',
            'identity_number' => '3501234567890001',
            'identity_document_image' => 'ktp.jpg',
            'face_image' => 'face.jpg',
            'identity_address' => 'Jl. Mawar No 1',
            'status' => 'diverifikasi',
        ]);

        ReturnModel::create([
            'order_id' => $order->id,
            'return_method' => 'langsung',
            'return_date' => now(),
            'notes' => 'Barang kembali mulus dan lengkap',
            'return_status' => 'selesai',
            'late_fee_status' => 'tidak_ada',
            'late_fee_amount' => 0,
        ]);

        // Jalankan Job
        (new AutoCompleteOrderJob($order->id))->handle();

        $order->refresh();
        $identity->refresh();

        $this->assertEquals('selesai', $order->status);
        $this->assertEquals('diverifikasi', $identity->status);
    }

    /**
     * Test WebSocket Pusher: NewOrderPlaced menyiarkan event pada channel private toko rental.
     */
    public function test_new_order_placed_event_broadcasts_on_tenant_private_channel(): void
    {
        $order = Order::create([
            'client_id' => $this->client->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-TEST-999',
            'rental_start_date' => now()->addDay()->toDateString(),
            'rental_end_date' => now()->addDays(2)->toDateString(),
            'duration_days' => 1,
            'total_amount' => 150000,
            'shipping_address' => 'Jl. Mawar No. 123, Malang',
            'shipping_method' => 'pickup',
            'status' => 'menunggu_konfirmasi',
        ]);

        $event = new NewOrderPlaced($order);

        $channels = $event->broadcastOn();
        $this->assertCount(1, $channels);
        $this->assertEquals('private-tenant.' . $this->client->id . '.orders', $channels[0]->name);
        $this->assertEquals('order.placed', $event->broadcastAs());

        $payload = $event->broadcastWith();
        $this->assertEquals('ORD-TEST-999', $payload['order_number']);
        $this->assertEquals(150000, $payload['total_amount']);
    }

    /**
     * Test Listener: SendInAppNotification harus mengimplementasikan ShouldQueue agar asynchronous.
     */
    public function test_send_in_app_notification_listener_is_queued(): void
    {
        $listener = new SendInAppNotification();
        $this->assertInstanceOf(ShouldQueue::class, $listener);
    }

    /**
     * Test WebSocket Pusher: TenantRegistrationSubmitted menyiarkan ke private-owner.platform channel.
     */
    public function test_tenant_registration_submitted_broadcasts_on_owner_channel(): void
    {
        $reg = \App\Models\ClientRegistration::create([
            'business_name' => 'Kamera Malang',
            'subdomain' => 'kameramalang',
            'admin_name' => 'Budi Owner',
            'admin_email' => 'budi@kameramalang.com',
            'admin_phone' => '081234567899',
            'admin_password' => bcrypt('password123'),
            'plan_name' => 'business',
            'status' => 'menunggu_verifikasi',
        ]);

        $event = new \App\Events\TenantRegistrationSubmitted($reg);
        $channels = $event->broadcastOn();
        $this->assertCount(1, $channels);
        $this->assertEquals('private-owner.platform', $channels[0]->name);
        $this->assertEquals('tenant.registered', $event->broadcastAs());
    }

    /**
     * Test WebSocket Pusher: Event customer (IdentityApproved, PaymentVerified, OrderShipped) menyiarkan ke private-customer channel.
     */
    public function test_customer_facing_events_broadcast_on_customer_private_channel(): void
    {
        $order = Order::create([
            'client_id' => $this->client->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-CUST-111',
            'rental_start_date' => now()->addDay()->toDateString(),
            'rental_end_date' => now()->addDays(2)->toDateString(),
            'duration_days' => 1,
            'total_amount' => 50000,
            'shipping_address' => 'Jl. Mawar No. 123, Malang',
            'shipping_method' => 'pickup',
            'status' => 'menunggu_pembayaran',
        ]);

        $identityEvent = new \App\Events\IdentityApproved($order);
        $this->assertEquals('private-customer.' . $this->customer->id . '.orders', $identityEvent->broadcastOn()[0]->name);
        $this->assertEquals('identity.approved', $identityEvent->broadcastAs());

        $paymentEvent = new \App\Events\PaymentVerified($order);
        $this->assertEquals('private-customer.' . $this->customer->id . '.orders', $paymentEvent->broadcastOn()[0]->name);
        $this->assertEquals('payment.verified', $paymentEvent->broadcastAs());

        $shippedEvent = new \App\Events\OrderShipped($order);
        $this->assertEquals('private-customer.' . $this->customer->id . '.orders', $shippedEvent->broadcastOn()[0]->name);
        $this->assertEquals('order.shipped', $shippedEvent->broadcastAs());
    }

    /**
     * Test WebSocket Pusher: TenantQuotaAlert menyiarkan ke private-tenant channel.
     */
    public function test_tenant_quota_alert_broadcasts_on_tenant_private_channel(): void
    {
        $event = new \App\Events\TenantQuotaAlert($this->client, 8, 10, 80.0);
        $this->assertEquals('private-tenant.' . $this->client->id . '.orders', $event->broadcastOn()[0]->name);
        $this->assertEquals('tenant.quota_alert', $event->broadcastAs());
    }

    /**
     * Test WebSocket Pusher: Tenant operational events (IdentityProofUploaded, ReturnRequested).
     */
    public function test_tenant_operational_events_broadcast_on_tenant_private_channel(): void
    {
        $order = Order::create([
            'client_id' => $this->client->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-TEN-222',
            'rental_start_date' => now()->addDay()->toDateString(),
            'rental_end_date' => now()->addDays(2)->toDateString(),
            'duration_days' => 1,
            'total_amount' => 50000,
            'shipping_address' => 'Jl. Mawar No. 123, Malang',
            'shipping_method' => 'pickup',
            'status' => 'menunggu_verifikasi',
        ]);

        $identityProofEvent = new \App\Events\IdentityProofUploaded($order);
        $this->assertEquals('private-tenant.' . $this->client->id . '.orders', $identityProofEvent->broadcastOn()[0]->name);
        $this->assertEquals('identity.uploaded', $identityProofEvent->broadcastAs());

        $returnReqEvent = new \App\Events\ReturnRequested($order);
        $this->assertEquals('private-tenant.' . $this->client->id . '.orders', $returnReqEvent->broadcastOn()[0]->name);
        $this->assertEquals('return.requested', $returnReqEvent->broadcastAs());
    }

    /**
     * Test WebSocket Pusher: Extended customer lifecycle events (IdentityRejected, PaymentRejected, OrderConfirmed, ReturnAccepted, FineIssued).
     */
    public function test_extended_customer_events_broadcast_on_customer_private_channel(): void
    {
        $order = Order::create([
            'client_id' => $this->client->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-CUST-333',
            'rental_start_date' => now()->addDay()->toDateString(),
            'rental_end_date' => now()->addDays(2)->toDateString(),
            'duration_days' => 1,
            'total_amount' => 75000,
            'shipping_address' => 'Jl. Mawar No. 123, Malang',
            'shipping_method' => 'pickup',
            'status' => 'menunggu_pembayaran',
        ]);

        $idReject = new \App\Events\IdentityRejected($order);
        $this->assertEquals('private-customer.' . $this->customer->id . '.orders', $idReject->broadcastOn()[0]->name);
        $this->assertEquals('identity.rejected', $idReject->broadcastAs());

        $payReject = new \App\Events\PaymentRejected($order);
        $this->assertEquals('private-customer.' . $this->customer->id . '.orders', $payReject->broadcastOn()[0]->name);
        $this->assertEquals('payment.rejected', $payReject->broadcastAs());

        $orderConf = new \App\Events\OrderConfirmed($order);
        $this->assertEquals('private-customer.' . $this->customer->id . '.orders', $orderConf->broadcastOn()[0]->name);
        $this->assertEquals('order.confirmed', $orderConf->broadcastAs());

        $retAccept = new \App\Events\ReturnAccepted($order);
        $this->assertEquals('private-customer.' . $this->customer->id . '.orders', $retAccept->broadcastOn()[0]->name);
        $this->assertEquals('return.accepted', $retAccept->broadcastAs());

        $fine = new \App\Events\FineIssued($order, 25000);
        $this->assertEquals('private-customer.' . $this->customer->id . '.orders', $fine->broadcastOn()[0]->name);
        $this->assertEquals('fine.issued', $fine->broadcastAs());
    }

    /**
     * Test WebSocket Pusher: SubscriptionRenewalUploaded broadcasts on owner channel.
     */
    public function test_subscription_renewal_uploaded_broadcasts_on_owner_channel(): void
    {
        $sub = \App\Models\Subscription::create([
            'client_id' => $this->client->id,
            'plan_name' => 'Business',
            'price' => 199000,
            'billing_cycle' => 'monthly',
            'start_date' => now(),
            'end_date' => now()->addMonths(6),
            'status' => 'menunggu_konfirmasi',
            'payment_status' => 'menunggu',
        ]);

        $event = new \App\Events\SubscriptionRenewalUploaded($sub);
        $this->assertEquals('private-owner.platform', $event->broadcastOn()[0]->name);
        $this->assertEquals('subscription.renewal_uploaded', $event->broadcastAs());
    }
}
