<?php

namespace Tests\Unit;

use App\Models\Client;
use App\Models\ClientPaymentMethod;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * UT-MOD-08 — Pembayaran dan Refund (Unit Testing)
 * 
 * Menguji:
 * 1. Accessor payment_proof_url pada model Payment
 * 2. Relasi model Payment (order, verifiedBy, clientPaymentMethod)
 * 3. Attribute casting (amount, paid_at, verified_at)
 * 4. Cache busting dashboard admin pada event saved dan deleted
 */
class PaymentLogicTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;
    private User $customer;
    private User $admin;
    private Order $order;
    private ClientPaymentMethod $paymentMethod;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Client::create([
            'business_name' => 'Baby Rental Studio',
            'subdomain' => 'babyrental',
            'status' => 'aktif',
        ]);

        $this->customer = User::create([
            'name' => 'Zaskia Customer',
            'email' => 'customer.zaskia@example.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        $this->admin = User::create([
            'client_id' => $this->client->id,
            'name' => 'Admin Toko',
            'email' => 'admin@babyrental.com',
            'password' => bcrypt('password123'),
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        $this->order = Order::create([
            'client_id' => $this->client->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-PAY-TEST-001',
            'status' => 'menunggu_pembayaran',
            'rental_start_date' => now()->addDays(1),
            'rental_end_date' => now()->addDays(3),
            'duration_days' => 2,
            'total_amount' => 200000,
            'shipping_address' => 'Jl. Ijen No. 10, Malang',
            'shipping_method' => 'kurir',
        ]);

        $this->paymentMethod = ClientPaymentMethod::create([
            'client_id' => $this->client->id,
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_holder' => 'Baby Rental Studio',
            'is_active' => true,
        ]);
    }

    /**
     * UT-PAY-001: Memastikan accessor payment_proof_url menghasilkan URL storage saat field payment_proof terisi.
     */
    public function test_ut_pay_001_payment_proof_url_accessor_with_payment_proof(): void
    {
        $payment = new Payment([
            'payment_proof' => 'payments/bukti_bayar_01.jpg',
        ]);

        $this->assertNotNull($payment->payment_proof_url);
        $this->assertStringContainsString('payments/bukti_bayar_01.jpg', $payment->payment_proof_url);
    }

    /**
     * UT-PAY-002: Memastikan accessor payment_proof_url mendukung fallback ke legacy field proof_of_payment.
     */
    public function test_ut_pay_002_payment_proof_url_accessor_with_legacy_proof_of_payment(): void
    {
        $payment = new Payment([
            'payment_proof' => null,
            'proof_of_payment' => 'payments/legacy_proof.png',
        ]);

        $this->assertNotNull($payment->payment_proof_url);
        $this->assertStringContainsString('payments/legacy_proof.png', $payment->payment_proof_url);
    }

    /**
     * UT-PAY-003: Memastikan accessor payment_proof_url mengembalikan null saat kedua field bukti bayar kosong.
     */
    public function test_ut_pay_003_payment_proof_url_accessor_returns_null_when_empty(): void
    {
        $payment = new Payment([
            'payment_proof' => null,
            'proof_of_payment' => null,
        ]);

        $this->assertNull($payment->payment_proof_url);
    }

    /**
     * UT-PAY-004: Memastikan relasi model Payment terhubung valid ke Order, User (verifiedBy), dan ClientPaymentMethod.
     */
    public function test_ut_pay_004_payment_relationships(): void
    {
        $payment = Payment::create([
            'order_id' => $this->order->id,
            'client_payment_method_id' => $this->paymentMethod->id,
            'amount' => 200000,
            'payment_proof' => 'payments/bukti_relasi.jpg',
            'status' => 'terverifikasi',
            'verified_by' => $this->admin->id,
            'paid_at' => now(),
            'verified_at' => now(),
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $payment->order());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $payment->verifiedBy());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $payment->clientPaymentMethod());

        $this->assertEquals($this->order->id, $payment->order->id);
        $this->assertEquals($this->admin->id, $payment->verifiedBy->id);
        $this->assertEquals($this->paymentMethod->id, $payment->clientPaymentMethod->id);
    }

    /**
     * UT-PAY-005: Memastikan atribut amount, paid_at, dan verified_at dikonversi (casting) dengan presisi.
     */
    public function test_ut_pay_005_payment_casts_attributes(): void
    {
        $payment = Payment::create([
            'order_id' => $this->order->id,
            'client_payment_method_id' => $this->paymentMethod->id,
            'amount' => '175000.50',
            'payment_proof' => 'payments/bukti_cast.jpg',
            'status' => 'menunggu_konfirmasi',
            'paid_at' => '2026-11-13 10:15:00',
            'verified_at' => '2026-11-13 11:30:00',
        ]);

        $this->assertIsString($payment->amount);
        $this->assertEquals('175000.50', $payment->amount);
        $this->assertInstanceOf(Carbon::class, $payment->paid_at);
        $this->assertInstanceOf(Carbon::class, $payment->verified_at);
    }

    /**
     * UT-PAY-006: Memastikan cache ringkasan dashboard admin toko dibersihkan otomatis saat pembayaran disimpan atau dihapus.
     */
    public function test_ut_pay_006_payment_dashboard_cache_cleared_on_save_and_delete(): void
    {
        $cacheKey = 'admin_dashboard_counts_' . $this->client->id;
        Cache::put($cacheKey, ['total_revenue' => 500000], 60);
        $this->assertTrue(Cache::has($cacheKey));

        $payment = Payment::create([
            'order_id' => $this->order->id,
            'client_payment_method_id' => $this->paymentMethod->id,
            'amount' => 200000,
            'payment_proof' => 'payments/bukti_cache.jpg',
            'status' => 'terverifikasi',
        ]);

        $this->assertFalse(Cache::has($cacheKey), 'Cache dashboard toko harus dibersihkan saat pembayaran baru dibuat/disimpan.');

        Cache::put($cacheKey, ['total_revenue' => 700000], 60);
        $payment->delete();
        $this->assertFalse(Cache::has($cacheKey), 'Cache dashboard toko harus dibersihkan saat pembayaran dihapus.');
    }
}
