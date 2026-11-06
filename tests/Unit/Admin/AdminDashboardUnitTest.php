<?php

namespace Tests\Unit\Admin;

use App\Http\Middleware\AdminMiddleware;
use App\Models\Client;
use App\Models\ClientPaymentMethod;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminDashboardUnitTest extends TestCase
{
    use RefreshDatabase;

    private Client $clientA;
    private Client $clientB;
    private User $customer;
    private AdminMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();

        $this->middleware = new AdminMiddleware();

        $this->clientA = Client::create([
            'business_name' => 'Toko Kamera Alpha',
            'subdomain' => 'kameraalpha',
            'status' => 'aktif',
        ]);

        $this->clientB = Client::create([
            'business_name' => 'Toko Outdoor Beta',
            'subdomain' => 'outdoorbeta',
            'status' => 'aktif',
        ]);

        $this->customer = User::create([
            'name' => 'Customer Budi',
            'email' => 'customer.test@rentalbase.test',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'status' => 'aktif',
        ]);
    }

    private function createOrder(array $attributes = []): Order
    {
        $today = Carbon::today();

        return Order::create(array_merge([
            'client_id' => $this->clientA->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-' . uniqid(),
            'rental_start_date' => $today,
            'rental_end_date' => $today->copy()->addDays(2),
            'duration_days' => 2,
            'shipping_address' => 'Jl. Merdeka No. 10 Jakarta',
            'total_amount' => 100000,
            'status' => 'menunggu_pembayaran',
        ], $attributes));
    }

    public function test_new_booking_count_only_aggregates_orders_with_menunggu_pembayaran_status(): void
    {
        $this->createOrder([
            'client_id' => $this->clientA->id,
            'order_number' => 'ORD-NEW-01',
            'status' => 'menunggu_pembayaran',
            'total_amount' => 100000,
        ]);

        $this->createOrder([
            'client_id' => $this->clientA->id,
            'order_number' => 'ORD-NEW-02',
            'status' => 'menunggu_pembayaran',
            'total_amount' => 150000,
        ]);

        $this->createOrder([
            'client_id' => $this->clientA->id,
            'order_number' => 'ORD-DONE-01',
            'status' => 'selesai',
            'total_amount' => 200000,
        ]);

        $this->createOrder([
            'client_id' => $this->clientB->id,
            'order_number' => 'ORD-BETA-01',
            'status' => 'menunggu_pembayaran',
            'total_amount' => 500000,
        ]);

        $countA = Order::where('client_id', $this->clientA->id)
            ->where('status', 'menunggu_pembayaran')
            ->count();

        $this->assertSame(2, $countA);
    }

    public function test_monthly_revenue_calculation_only_sums_active_and_completed_statuses(): void
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $this->createOrder([
            'client_id' => $this->clientA->id,
            'order_number' => 'REV-01',
            'status' => 'selesai',
            'total_amount' => 300000,
            'created_at' => Carbon::now(),
        ]);

        $this->createOrder([
            'client_id' => $this->clientA->id,
            'order_number' => 'REV-02',
            'status' => 'sedang_disewa',
            'total_amount' => 200000,
            'created_at' => Carbon::now(),
        ]);

        $this->createOrder([
            'client_id' => $this->clientA->id,
            'order_number' => 'REV-BATAL',
            'status' => 'dibatalkan',
            'total_amount' => 999000,
            'created_at' => Carbon::now(),
        ]);

        $this->createOrder([
            'client_id' => $this->clientA->id,
            'order_number' => 'REV-UNPAID',
            'status' => 'menunggu_pembayaran',
            'total_amount' => 450000,
            'created_at' => Carbon::now(),
        ]);

        $revenue = Order::where('client_id', $this->clientA->id)
            ->whereIn('status', ['selesai', 'sedang_disewa', 'dikonfirmasi', 'siap_kirim', 'pembayaran_terverifikasi'])
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->sum('total_amount');

        $this->assertEquals(500000.0, (float) $revenue);
    }

    public function test_payment_awaiting_confirmation_isolated_by_client_id(): void
    {
        $methodA = ClientPaymentMethod::create([
            'client_id' => $this->clientA->id,
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_holder' => 'Tenant A',
            'is_active' => true,
        ]);

        $orderA = $this->createOrder([
            'client_id' => $this->clientA->id,
            'order_number' => 'ORD-PAY-A',
            'status' => 'menunggu_verifikasi',
            'total_amount' => 250000,
        ]);

        Payment::create([
            'order_id' => $orderA->id,
            'client_payment_method_id' => $methodA->id,
            'amount' => 250000,
            'payment_proof' => 'proofs/sample_a.jpg',
            'status' => 'menunggu_konfirmasi',
        ]);

        $methodB = ClientPaymentMethod::create([
            'client_id' => $this->clientB->id,
            'bank_name' => 'Mandiri',
            'account_number' => '0987654321',
            'account_holder' => 'Tenant B',
            'is_active' => true,
        ]);

        $orderB = $this->createOrder([
            'client_id' => $this->clientB->id,
            'order_number' => 'ORD-PAY-B',
            'status' => 'menunggu_verifikasi',
            'total_amount' => 400000,
        ]);

        Payment::create([
            'order_id' => $orderB->id,
            'client_payment_method_id' => $methodB->id,
            'amount' => 400000,
            'payment_proof' => 'proofs/sample_b.jpg',
            'status' => 'menunggu_konfirmasi',
        ]);

        $menungguVerifikasiA = Payment::whereHas('order', function ($q) {
            $q->where('client_id', $this->clientA->id);
        })->where('status', 'menunggu_konfirmasi')->count();

        $this->assertSame(1, $menungguVerifikasiA);
    }

    public function test_admin_dashboard_quota_percentage_and_remaining_calculation(): void
    {
        $maxAdmins = 3;
        $activeAdminsCount = 2;

        $remainingQuota = max(0, $maxAdmins - $activeAdminsCount);
        $isQuotaFull = $activeAdminsCount >= $maxAdmins;
        $usagePercentage = $maxAdmins > 0 ? min(100, round(($activeAdminsCount / $maxAdmins) * 100)) : 100;

        $this->assertSame(1, $remainingQuota);
        $this->assertFalse($isQuotaFull);
        $this->assertEquals(67.0, $usagePercentage);
    }

    public function test_unauthenticated_user_redirects_to_home(): void
    {
        $request = Request::create('/admin/dashboard', 'GET');
        $next = fn ($req) => new Response('Passed');

        $response = $this->middleware->handle($request, $next);

        $this->assertTrue($response->isRedirection());
        $this->assertEquals(url('/'), $response->headers->get('Location'));
    }

    public function test_customer_role_is_denied_and_redirects_to_home(): void
    {
        Auth::login($this->customer);

        $request = Request::create('/admin/dashboard', 'GET');
        $next = fn ($req) => new Response('Passed');

        $response = $this->middleware->handle($request, $next);

        $this->assertTrue($response->isRedirection());
        $this->assertEquals(url('/'), $response->headers->get('Location'));
    }

    public function test_active_admin_rental_passes_through(): void
    {
        $admin = User::create([
            'client_id' => $this->clientA->id,
            'name' => 'Admin Aktif',
            'email' => 'admin.dash@test.com',
            'password' => Hash::make('password123'),
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        Auth::login($admin);

        $request = Request::create('/admin/dashboard', 'GET');
        $next = fn ($req) => new Response('Passed');

        $response = $this->middleware->handle($request, $next);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Passed', $response->getContent());
    }
}
