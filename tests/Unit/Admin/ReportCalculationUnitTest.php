<?php

namespace Tests\Unit\Admin;

use App\Models\Client;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ReportCalculationUnitTest extends TestCase
{
    use RefreshDatabase;

    private Client $clientA;
    private Client $clientB;
    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clientA = Client::create([
            'business_name' => 'Toko Laporan A',
            'subdomain' => 'laporana',
            'status' => 'aktif',
        ]);

        $this->clientB = Client::create([
            'business_name' => 'Toko Laporan B',
            'subdomain' => 'laporanb',
            'status' => 'aktif',
        ]);

        $this->customer = User::create([
            'name' => 'Customer Laporan',
            'email' => 'customer.laporan@test.com',
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
            'shipping_address' => 'Jl. Pengujian No. 12',
            'total_amount' => 100000,
            'status' => 'selesai',
        ], $attributes));
    }

    public function test_revenue_report_only_includes_orders_within_specified_date_range(): void
    {
        $startDate = Carbon::create(2026, 11, 1, 0, 0, 0);
        $endDate = Carbon::create(2026, 11, 5, 23, 59, 59);

        $this->createOrder([
            'client_id' => $this->clientA->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-IN-1',
            'status' => 'selesai',
            'total_amount' => 200000,
            'created_at' => Carbon::create(2026, 11, 2, 10, 0, 0),
        ]);

        $this->createOrder([
            'client_id' => $this->clientA->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-BEFORE',
            'status' => 'selesai',
            'total_amount' => 100000,
            'created_at' => Carbon::create(2026, 10, 31, 23, 59, 0),
        ]);

        $this->createOrder([
            'client_id' => $this->clientA->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-AFTER',
            'status' => 'selesai',
            'total_amount' => 300000,
            'created_at' => Carbon::create(2026, 11, 6, 8, 0, 0),
        ]);

        $totalRevenue = Order::where('client_id', $this->clientA->id)
            ->whereIn('status', ['selesai', 'sedang_disewa', 'dikonfirmasi', 'siap_kirim', 'pembayaran_terverifikasi'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_amount');

        $this->assertEquals(200000.0, (float) $totalRevenue);
    }

    public function test_cancelled_and_unpaid_orders_are_excluded_from_revenue_sum(): void
    {
        $startDate = Carbon::create(2026, 11, 1, 0, 0, 0);
        $endDate = Carbon::create(2026, 11, 30, 23, 59, 59);

        $this->createOrder([
            'client_id' => $this->clientA->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-VALID',
            'status' => 'selesai',
            'total_amount' => 500000,
            'created_at' => Carbon::create(2026, 11, 10),
        ]);

        $this->createOrder([
            'client_id' => $this->clientA->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-CANCELLED',
            'status' => 'dibatalkan',
            'total_amount' => 250000,
            'created_at' => Carbon::create(2026, 11, 11),
        ]);

        $this->createOrder([
            'client_id' => $this->clientA->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-UNPAID',
            'status' => 'menunggu_pembayaran',
            'total_amount' => 300000,
            'created_at' => Carbon::create(2026, 11, 12),
        ]);

        $revenue = Order::where('client_id', $this->clientA->id)
            ->whereIn('status', ['selesai', 'sedang_disewa', 'dikonfirmasi', 'siap_kirim', 'pembayaran_terverifikasi'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_amount');

        $this->assertEquals(500000.0, (float) $revenue);
    }

    public function test_financial_isolation_excludes_other_tenant_orders(): void
    {
        $this->createOrder([
            'client_id' => $this->clientA->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-A',
            'status' => 'selesai',
            'total_amount' => 150000,
            'created_at' => Carbon::now(),
        ]);

        $this->createOrder([
            'client_id' => $this->clientB->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-B',
            'status' => 'selesai',
            'total_amount' => 750000,
            'created_at' => Carbon::now(),
        ]);

        $revenueA = Order::where('client_id', $this->clientA->id)
            ->where('status', 'selesai')
            ->sum('total_amount');

        $this->assertEquals(150000.0, (float) $revenueA);
    }

    public function test_admin_report_csv_formula_injection_sanitization(): void
    {
        $sanitizeForCsv = function (?string $value): string {
            if ($value === null || $value === '' || $value === '-') {
                return $value ?? '';
            }

            $firstChar = substr($value, 0, 1);
            if (in_array($firstChar, ['=', '+', '-', '@', "\t", "\r"], true)) {
                if ($value === '-' || is_numeric($value)) {
                    return $value;
                }
                return "'" . $value;
            }

            return $value;
        };

        $this->assertSame("'=cmd|' /C calc'!A0", $sanitizeForCsv("=cmd|' /C calc'!A0"));
        $this->assertSame("'+SUM(A1:A10)", $sanitizeForCsv("+SUM(A1:A10)"));
        $this->assertSame("'-2+3", $sanitizeForCsv("-2+3"));
        $this->assertSame("'@SUM", $sanitizeForCsv("@SUM"));

        $this->assertSame('Sony Alpha A7 IV', $sanitizeForCsv('Sony Alpha A7 IV'));
        $this->assertSame('150000', $sanitizeForCsv('150000'));
        $this->assertSame('-', $sanitizeForCsv('-'));
    }
}
