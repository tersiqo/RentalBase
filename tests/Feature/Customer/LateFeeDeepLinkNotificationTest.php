<?php

namespace Tests\Feature\Customer;

use App\Events\FineIssued;
use App\Listeners\SendInAppNotification;
use App\Models\Client;
use App\Models\Notification;
use App\Models\Order;
use App\Models\ReturnModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LateFeeDeepLinkNotificationTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;
    private User $customer;
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->client = Client::create([
            'business_name' => 'Rental Kamera Jogja',
            'subdomain' => 'kamera-jogja',
            'status' => 'aktif',
        ]);

        $this->customer = User::factory()->create([
            'client_id' => $this->client->id,
            'name' => 'Budi Santoso',
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        $this->order = Order::create([
            'client_id' => $this->client->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-TEST-20261122',
            'rental_start_date' => now()->subDays(5),
            'rental_end_date' => now()->subDay(),
            'duration_days' => 4,
            'total_amount' => 250000,
            'shipping_address' => 'Jl. Malioboro No. 12',
            'status' => 'menunggu_pembayaran_denda',
        ]);

        ReturnModel::create([
            'order_id' => $this->order->id,
            'return_date' => now(),
            'return_method' => 'ambil_toko',
            'late_hours' => 5,
            'late_fee_amount' => 50000,
            'late_fee_status' => 'menunggu_pembayaran',
            'notes' => 'Terlambat 5 jam',
        ]);
    }

    public function test_customer_target_url_accessor_generates_deep_link_for_late_fee(): void
    {
        $notif = Notification::create([
            'client_id' => $this->client->id,
            'user_id' => $this->customer->id,
            'title' => 'Tagihan Denda Terbit #' . $this->order->order_number,
            'message' => 'Terdapat denda keterlambatan sebesar Rp 50.000 untuk pesanan #' . $this->order->order_number . '. Silakan selesaikan pembayaran denda.',
            'type' => 'late_fee',
            'is_read' => false,
        ]);

        $expectedUrl = route('customer.orders.show', $this->order->id) . '?action=pay_late_fee';
        $this->assertEquals($expectedUrl, $notif->customer_target_url);
    }

    public function test_customer_target_url_accessor_generates_standard_link_for_regular_order(): void
    {
        $notif = Notification::create([
            'client_id' => $this->client->id,
            'user_id' => $this->customer->id,
            'title' => 'Pesanan Dikonfirmasi #' . $this->order->order_number,
            'message' => 'Pesanan #' . $this->order->order_number . ' Anda telah dikonfirmasi oleh toko.',
            'type' => 'order',
            'is_read' => false,
        ]);

        $expectedUrl = route('customer.orders.show', $this->order->id);
        $this->assertEquals($expectedUrl, $notif->customer_target_url);
    }

    public function test_customer_target_url_fallback_when_no_order_matched(): void
    {
        $notif = Notification::create([
            'client_id' => $this->client->id,
            'user_id' => $this->customer->id,
            'title' => 'Pengumuman Toko',
            'message' => 'Toko libur pada hari Minggu.',
            'type' => 'system',
            'is_read' => false,
        ]);

        $this->assertEquals(route('customer.orders'), $notif->customer_target_url);
    }

    public function test_customer_target_url_handles_non_numeric_order_number_without_casting_error(): void
    {
        $nonNumericOrder = Order::create([
            'client_id' => $this->client->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-ODEX1N-20261116',
            'rental_start_date' => now()->subDays(5),
            'rental_end_date' => now()->subDay(),
            'duration_days' => 4,
            'total_amount' => 150000,
            'shipping_address' => 'Jl. Gejayan No. 45',
            'status' => 'menunggu_pembayaran_denda',
        ]);

        $notif = Notification::create([
            'client_id' => $this->client->id,
            'user_id' => $this->customer->id,
            'title' => 'Tagihan Denda Terbit #' . $nonNumericOrder->order_number,
            'message' => 'Terdapat denda keterlambatan untuk pesanan #' . $nonNumericOrder->order_number . '.',
            'type' => 'late_fee',
            'is_read' => false,
        ]);

        $this->assertEquals(
            route('customer.orders.show', $nonNumericOrder->id) . '?action=pay_late_fee',
            $notif->customer_target_url
        );
        $this->assertEquals(
            route('admin.orders.show', $nonNumericOrder->id),
            $notif->admin_target_url
        );
    }

    public function test_listener_fine_issued_creates_notification_with_late_fee_type_and_order_number(): void
    {
        $listener = new SendInAppNotification();
        $event = new FineIssued($this->order, 50000);

        $listener->handle($event);

        $notif = Notification::where('user_id', $this->customer->id)
            ->where('type', 'late_fee')
            ->first();

        $this->assertNotNull($notif);
        $this->assertStringContainsString('#' . $this->order->order_number, $notif->title);
        $this->assertStringContainsString('#' . $this->order->order_number, $notif->message);
        $this->assertEquals('late_fee', $notif->type);
        $this->assertEquals(route('customer.orders.show', $this->order->id) . '?action=pay_late_fee', $notif->customer_target_url);
    }

    public function test_customer_notifications_view_renders_cta_button_and_deep_link(): void
    {
        $notif = Notification::create([
            'client_id' => $this->client->id,
            'user_id' => $this->customer->id,
            'title' => 'Tagihan Denda Terbit #' . $this->order->order_number,
            'message' => 'Terdapat denda keterlambatan sebesar Rp 50.000 untuk pesanan #' . $this->order->order_number . '.',
            'type' => 'late_fee',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->customer)
            ->get(route('customer.notifications.index'));

        $response->assertStatus(200);
        $response->assertSee(e($notif->customer_target_url), false);
        $response->assertSee('Bayar Denda Sekarang');
    }

    public function test_customer_order_detail_view_initializes_modal_when_action_is_pay_late_fee(): void
    {
        $this->assertTrue($this->order->can_pay_late_fee);

        $response = $this->actingAs($this->customer)
            ->get(route('customer.orders.show', ['order' => $this->order->id, 'action' => 'pay_late_fee']));

        $response->assertStatus(200);
        $response->assertSee('lateFeeModalOpen: true', false);
        $response->assertSee("urlParams.get('action') === 'pay_late_fee'", false);
    }

    public function test_customer_order_detail_view_renders_red_banner_and_payment_methods(): void
    {
        \App\Models\ClientPaymentMethod::create([
            'client_id' => $this->client->id,
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_holder' => 'PT Rental Jogja Sukses',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->customer)
            ->get(route('customer.orders.show', ['order' => $this->order->id]));

        $response->assertStatus(200);
        $response->assertSee('bg-red-50 border border-red-200', false);
        $response->assertSee('Tagihan Denda Keterlambatan:');
        $response->assertSee('Rp 50.000');
        $response->assertSee('1234567890');
        $response->assertSee('a.n. PT Rental Jogja Sukses');
    }
}
