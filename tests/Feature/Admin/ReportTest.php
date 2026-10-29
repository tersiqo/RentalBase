<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Client;
use App\Models\ClientPaymentMethod;
use App\Models\DamageCase;
use App\Models\DamageReport;
use App\Models\EquipmentUnit;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ReturnModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private Client $clientA;
    private Client $clientB;
    private User $adminA;
    private User $adminB;
    private User $customer;
    private ClientPaymentMethod $paymentMethodA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        // Setup Client A (Tenant Utama)
        $this->clientA = Client::create([
            'business_name' => 'RentalBase Toko A',
            'subdomain' => 'tokoa',
            'status' => 'aktif',
        ]);

        $this->adminA = User::create([
            'client_id' => $this->clientA->id,
            'name' => 'Fiza Admin A',
            'email' => 'admin.a@rentalbase.test',
            'password' => bcrypt('password123'),
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        // Setup Client B (Tenant Lain untuk isolasi data)
        $this->clientB = Client::create([
            'business_name' => 'RentalBase Toko B',
            'subdomain' => 'tokob',
            'status' => 'aktif',
        ]);

        $this->adminB = User::create([
            'client_id' => $this->clientB->id,
            'name' => 'Admin Toko B',
            'email' => 'admin.b@rentalbase.test',
            'password' => bcrypt('password123'),
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        // Setup Customer
        $this->customer = User::create([
            'name' => 'Customer Budi',
            'email' => 'budi@customer.test',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        // Setup Metode Pembayaran
        $this->paymentMethodA = ClientPaymentMethod::create([
            'client_id' => $this->clientA->id,
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_holder' => 'Rental Toko A',
            'is_active' => true,
        ]);
    }

    // ==========================================
    // A. AKSES DAN OTORISASI
    // ==========================================

    public function test_unauthenticated_user_cannot_access_reports_page(): void
    {
        $response = $this->get(route('admin.reports.index'));
        $response->assertRedirect('/');
    }

    public function test_customer_cannot_access_reports_page(): void
    {
        $response = $this->actingAs($this->customer)->get(route('admin.reports.index'));
        $response->assertRedirect('/');
    }

    public function test_owner_role_cannot_access_admin_reports_page(): void
    {
        $owner = User::create([
            'name' => 'Owner Utama',
            'email' => 'owner@rentalbase.test',
            'password' => bcrypt('password123'),
            'role' => 'owner',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($owner)->get(route('admin.reports.index'));
        $response->assertRedirect(route('owner.dashboard'));
    }

    public function test_authorized_admin_can_access_reports_page(): void
    {
        $response = $this->actingAs($this->adminA)->get(route('admin.reports.index'));
        $response->assertStatus(200);
        $response->assertViewIs('admin.reports.index');
        $response->assertSee('Laporan Transaksi Toko');
    }

    public function test_unauthenticated_user_cannot_access_csv_export(): void
    {
        $response = $this->get(route('admin.reports.export'));
        $response->assertRedirect('/');
    }

    public function test_authorized_admin_can_access_csv_export(): void
    {
        $response = $this->actingAs($this->adminA)->get(route('admin.reports.export'));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    // ==========================================
    // B. FILTER TANGGAL
    // ==========================================

    public function test_orders_on_start_and_end_dates_are_included(): void
    {
        $start = Carbon::create(2026, 10, 10, 8, 0, 0);
        $end = Carbon::create(2026, 10, 20, 20, 0, 0);

        // Order tepat di tanggal awal
        $orderStart = Order::create([
            'client_id' => $this->clientA->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-START-10',
            'rental_start_date' => $start,
            'rental_end_date' => $start->copy()->addDays(2),
            'duration_days' => 2,
            'total_amount' => 100000,
            'shipping_address' => 'Alamat A',
            'status' => 'selesai',
            'created_at' => $start,
        ]);

        // Order tepat di tanggal akhir
        $orderEnd = Order::create([
            'client_id' => $this->clientA->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-END-20',
            'rental_start_date' => $end,
            'rental_end_date' => $end->copy()->addDays(2),
            'duration_days' => 2,
            'total_amount' => 200000,
            'shipping_address' => 'Alamat B',
            'status' => 'selesai',
            'created_at' => $end,
        ]);

        // Order sebelum periode
        $orderBefore = Order::create([
            'client_id' => $this->clientA->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-BEFORE-05',
            'rental_start_date' => $start->copy()->subDays(5),
            'rental_end_date' => $start->copy()->subDays(3),
            'duration_days' => 2,
            'total_amount' => 300000,
            'shipping_address' => 'Alamat C',
            'status' => 'selesai',
            'created_at' => Carbon::create(2026, 10, 5, 12, 0, 0),
        ]);

        // Order setelah periode
        $orderAfter = Order::create([
            'client_id' => $this->clientA->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-AFTER-25',
            'rental_start_date' => $end->copy()->addDays(5),
            'rental_end_date' => $end->copy()->addDays(7),
            'duration_days' => 2,
            'total_amount' => 400000,
            'shipping_address' => 'Alamat D',
            'status' => 'selesai',
            'created_at' => Carbon::create(2026, 10, 25, 12, 0, 0),
        ]);

        $response = $this->actingAs($this->adminA)->get(route('admin.reports.index', [
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-20',
        ]));

        $response->assertStatus(200);
        $response->assertSee('ORD-START-10');
        $response->assertSee('ORD-END-20');
        $response->assertDontSee('ORD-BEFORE-05');
        $response->assertDontSee('ORD-AFTER-25');
    }

    public function test_preset_shortcut_filters_produce_valid_date_ranges(): void
    {
        $today = Carbon::today();

        // Buat pesanan hari ini
        Order::create([
            'client_id' => $this->clientA->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-TODAY-01',
            'rental_start_date' => $today,
            'rental_end_date' => $today->copy()->addDays(1),
            'duration_days' => 1,
            'total_amount' => 75000,
            'shipping_address' => 'Alamat',
            'status' => 'selesai',
            'created_at' => $today->copy()->addHours(2),
        ]);

        $response = $this->actingAs($this->adminA)->get(route('admin.reports.index', [
            'preset' => 'hari_ini',
        ]));

        $response->assertStatus(200);
        $response->assertSee('ORD-TODAY-01');
    }

    public function test_inverted_date_range_is_rejected_by_validation(): void
    {
        $response = $this->actingAs($this->adminA)->get(route('admin.reports.index', [
            'start_date' => '2026-10-20',
            'end_date' => '2026-10-10',
        ]));

        $response->assertRedirect(route('admin.reports.index'));
        $response->assertSessionHasErrors(['end_date']);
    }

    public function test_invalid_date_format_is_handled_safely(): void
    {
        $response = $this->actingAs($this->adminA)->get(route('admin.reports.index', [
            'start_date' => 'bukan-tanggal',
            'end_date' => '2026-10-20',
        ]));

        $response->assertRedirect(route('admin.reports.index'));
        $response->assertSessionHasErrors(['start_date']);
    }

    public function test_csv_export_follows_active_date_filter(): void
    {
        $targetDate = Carbon::create(2026, 10, 15, 10, 0, 0);

        Order::create([
            'client_id' => $this->clientA->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-CSV-IN',
            'rental_start_date' => $targetDate,
            'rental_end_date' => $targetDate->copy()->addDays(1),
            'duration_days' => 1,
            'total_amount' => 125000,
            'shipping_address' => 'Alamat In',
            'status' => 'selesai',
            'created_at' => $targetDate,
        ]);

        Order::create([
            'client_id' => $this->clientA->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-CSV-OUT',
            'rental_start_date' => $targetDate->copy()->subMonth(),
            'rental_end_date' => $targetDate->copy()->subMonth()->addDays(1),
            'duration_days' => 1,
            'total_amount' => 90000,
            'shipping_address' => 'Alamat Out',
            'status' => 'selesai',
            'created_at' => $targetDate->copy()->subMonth(),
        ]);

        $response = $this->actingAs($this->adminA)->get(route('admin.reports.export', [
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-20',
        ]));

        $response->assertStatus(200);
        $content = $response->streamedContent();

        $this->assertStringContainsString('ORD-CSV-IN', $content);
        $this->assertStringNotContainsString('ORD-CSV-OUT', $content);
    }

    // ==========================================
    // C. ISOLASI MULTI-TENANT
    // ==========================================

    public function test_admin_tenant_a_only_sees_tenant_a_data(): void
    {
        $date = Carbon::create(2026, 10, 15, 12, 0, 0);

        $orderA = Order::create([
            'client_id' => $this->clientA->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-TOKO-A-111',
            'rental_start_date' => $date,
            'rental_end_date' => $date->copy()->addDays(2),
            'duration_days' => 2,
            'total_amount' => 500000,
            'shipping_address' => 'Alamat A',
            'status' => 'selesai',
            'created_at' => $date,
        ]);

        $customerB = User::create([
            'name' => 'Customer Rahasia B',
            'email' => 'rahasia@customer.test',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        $orderB = Order::create([
            'client_id' => $this->clientB->id,
            'user_id' => $customerB->id,
            'order_number' => 'ORD-TOKO-B-999',
            'rental_start_date' => $date,
            'rental_end_date' => $date->copy()->addDays(2),
            'duration_days' => 2,
            'total_amount' => 999999,
            'shipping_address' => 'Alamat B',
            'status' => 'selesai',
            'created_at' => $date,
        ]);

        $response = $this->actingAs($this->adminA)->get(route('admin.reports.index', [
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
        ]));

        $response->assertStatus(200);
        $response->assertSee('ORD-TOKO-A-111');
        $response->assertDontSee('ORD-TOKO-B-999');
        $response->assertDontSee('Customer Rahasia B');
        $response->assertDontSee('999.999');
    }

    public function test_financial_aggregations_do_not_count_other_tenant_revenue(): void
    {
        $date = Carbon::create(2026, 10, 15, 12, 0, 0);

        // Toko A: Pendapatan Sewa Rp 100.000
        Order::create([
            'client_id' => $this->clientA->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-A-100K',
            'rental_start_date' => $date,
            'rental_end_date' => $date->copy()->addDays(1),
            'duration_days' => 1,
            'total_amount' => 100000,
            'shipping_address' => 'Alamat A',
            'status' => 'selesai',
            'created_at' => $date,
        ]);

        // Toko B: Pendapatan Sewa Rp 800.000 (tidak boleh masuk ke Toko A)
        Order::create([
            'client_id' => $this->clientB->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-B-800K',
            'rental_start_date' => $date,
            'rental_end_date' => $date->copy()->addDays(1),
            'duration_days' => 1,
            'total_amount' => 800000,
            'shipping_address' => 'Alamat B',
            'status' => 'selesai',
            'created_at' => $date,
        ]);

        $response = $this->actingAs($this->adminA)->get(route('admin.reports.index', [
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Rp 100.000');
        $response->assertDontSee('Rp 900.000');
        $response->assertDontSee('Rp 800.000');
    }

    public function test_tampering_with_client_id_query_param_does_not_leak_other_tenant(): void
    {
        $date = Carbon::create(2026, 10, 15, 12, 0, 0);

        Order::create([
            'client_id' => $this->clientB->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-SECRET-B',
            'rental_start_date' => $date,
            'rental_end_date' => $date->copy()->addDays(1),
            'duration_days' => 1,
            'total_amount' => 777000,
            'shipping_address' => 'Alamat B',
            'status' => 'selesai',
            'created_at' => $date,
        ]);

        // Admin A mencoba menyisipkan client_id Toko B di query string
        $response = $this->actingAs($this->adminA)->get(route('admin.reports.index', [
            'client_id' => $this->clientB->id,
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
        ]));

        $response->assertStatus(200);
        $response->assertDontSee('ORD-SECRET-B');
        $response->assertDontSee('777.000');
    }

    // ==========================================
    // D. INTEGRITAS AGREGASI FINANSIAL
    // ==========================================

    public function test_pending_and_cancelled_orders_are_not_counted_in_revenue(): void
    {
        $date = Carbon::create(2026, 10, 15, 12, 0, 0);

        // Order selesai sah: 200.000
        $ordSelesai = Order::create([
            'client_id' => $this->clientA->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-VALID-SELESAI',
            'rental_start_date' => $date,
            'rental_end_date' => $date->copy()->addDays(2),
            'duration_days' => 2,
            'total_amount' => 200000,
            'shipping_address' => 'Alamat',
            'status' => 'selesai',
            'created_at' => $date,
        ]);
        Payment::create([
            'order_id' => $ordSelesai->id,
            'client_payment_method_id' => $this->paymentMethodA->id,
            'amount' => 200000,
            'payment_proof' => 'payments/proof.jpg',
            'status' => 'terverifikasi',
            'paid_at' => $date,
        ]);

        // Order dibatalkan: 150.000 (tidak boleh dihitung)
        Order::create([
            'client_id' => $this->clientA->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-BATAL',
            'rental_start_date' => $date,
            'rental_end_date' => $date->copy()->addDays(1),
            'duration_days' => 1,
            'total_amount' => 150000,
            'shipping_address' => 'Alamat',
            'status' => 'dibatalkan',
            'created_at' => $date,
        ]);

        // Order menunggu pembayaran / konfirmasi: 80.000 (tidak boleh dihitung)
        $ordPending = Order::create([
            'client_id' => $this->clientA->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-PENDING',
            'rental_start_date' => $date,
            'rental_end_date' => $date->copy()->addDays(1),
            'duration_days' => 1,
            'total_amount' => 80000,
            'shipping_address' => 'Alamat',
            'status' => 'menunggu_pembayaran',
            'created_at' => $date,
        ]);
        Payment::create([
            'order_id' => $ordPending->id,
            'client_payment_method_id' => $this->paymentMethodA->id,
            'amount' => 80000,
            'payment_proof' => 'payments/pending.jpg',
            'status' => 'menunggu_konfirmasi',
            'paid_at' => $date,
        ]);

        $response = $this->actingAs($this->adminA)->get(route('admin.reports.index', [
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
        ]));

        $response->assertStatus(200);
        // Total Pendapatan harus tepat 200.000, bukan 350.000 atau 430.000
        $response->assertSee('Rp 200.000');
        $response->assertDontSee('Rp 430.000');
        $response->assertDontSee('Rp 350.000');
    }

    public function test_late_fees_and_damage_compensation_are_correctly_calculated_without_duplication(): void
    {
        $date = Carbon::create(2026, 10, 15, 12, 0, 0);

        // Setup kategori & unit produk untuk kerusakan
        $category = Category::create([
            'client_id' => $this->clientA->id,
            'name' => 'Perlengkapan Bayi',
            'slug' => 'perlengkapan-bayi',
        ]);
        $product = Product::create([
            'client_id' => $this->clientA->id,
            'category_id' => $category->id,
            'name' => 'Stroller Bayi Ergonomis',
            'slug' => 'stroller-bayi-ergonomis',
            'rental_price_per_day' => 50000,
            'status' => 'aktif',
        ]);
        $unit = EquipmentUnit::create([
            'product_id' => $product->id,
            'client_id' => $this->clientA->id,
            'unit_code' => 'UNIT-STR-01',
            'status' => 'tersedia',
            'condition' => 'baik',
        ]);

        // Order selesai dengan Sewa: Rp 100.000 + Denda: Rp 25.000 + Kerusakan: Rp 50.000
        $order = Order::create([
            'client_id' => $this->clientA->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-KOMPLIT-01',
            'rental_start_date' => $date,
            'rental_end_date' => $date->copy()->addDays(2),
            'duration_days' => 2,
            'total_amount' => 100000,
            'shipping_address' => 'Alamat Lengkap',
            'status' => 'selesai',
            'created_at' => $date,
        ]);

        // Pembayaran sewa
        Payment::create([
            'order_id' => $order->id,
            'client_payment_method_id' => $this->paymentMethodA->id,
            'amount' => 100000,
            'payment_proof' => 'payments/proof.jpg',
            'status' => 'terverifikasi',
            'paid_at' => $date,
        ]);

        // Denda lunas
        ReturnModel::create([
            'order_id' => $order->id,
            'return_method' => 'ambil_toko',
            'return_date' => $date->copy()->addDays(3),
            'late_hours' => 5,
            'calculated_late_fee' => 25000,
            'late_fee_amount' => 25000,
            'late_fee_status' => 'lunas',
            'return_status' => 'selesai',
        ]);

        // Kerusakan lunas
        $damageReport = DamageReport::create([
            'order_id' => $order->id,
            'equipment_unit_id' => $unit->id,
            'description' => 'Goresan kanopi',
            'reported_by' => $this->adminA->id,
        ]);
        DamageCase::create([
            'damage_report_id' => $damageReport->id,
            'repair_cost' => 40000,
            'fine_amount' => 10000,
            'case_status' => 'selesai',
            'payment_status' => 'lunas',
        ]);

        $response = $this->actingAs($this->adminA)->get(route('admin.reports.index', [
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
        ]));

        $response->assertStatus(200);

        // Total Pendapatan = 100.000 + 25.000 + 50.000 = 175.000
        $response->assertSee('Rp 175.000'); // Total Pendapatan
        $response->assertSee('Rp 100.000'); // Sewa Pokok
        $response->assertSee('Rp 25.000');  // Denda
        $response->assertSee('Rp 50.000');  // Kompensasi Kerusakan
    }

    public function test_unpaid_damage_case_is_not_counted_in_compensation(): void
    {
        $date = Carbon::create(2026, 10, 15, 12, 0, 0);

        $category = Category::firstOrCreate([
            'client_id' => $this->clientA->id,
            'name' => 'Perlengkapan Bayi',
            'slug' => 'perlengkapan-bayi',
        ]);
        $product = Product::create([
            'client_id' => $this->clientA->id,
            'category_id' => $category->id,
            'name' => 'Pompa ASI Elektrik',
            'slug' => 'pompa-asi-elektrik',
            'rental_price_per_day' => 40000,
            'status' => 'aktif',
        ]);
        $unit = EquipmentUnit::create([
            'product_id' => $product->id,
            'client_id' => $this->clientA->id,
            'unit_code' => 'UNIT-PMP-01',
            'status' => 'tersedia',
            'condition' => 'baik',
        ]);

        $order = Order::create([
            'client_id' => $this->clientA->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-DMG-UNPAID',
            'rental_start_date' => $date,
            'rental_end_date' => $date->copy()->addDays(2),
            'duration_days' => 2,
            'total_amount' => 80000,
            'shipping_address' => 'Alamat',
            'status' => 'selesai',
            'created_at' => $date,
        ]);

        $damageReport = DamageReport::create([
            'order_id' => $order->id,
            'equipment_unit_id' => $unit->id,
            'description' => 'Tutup retak',
            'reported_by' => $this->adminA->id,
        ]);

        // Kasus belum lunas (status belum_dibayar)
        DamageCase::create([
            'damage_report_id' => $damageReport->id,
            'repair_cost' => 60000,
            'fine_amount' => 0,
            'case_status' => 'menunggu_pembayaran',
            'payment_status' => 'belum_dibayar',
        ]);

        $response = $this->actingAs($this->adminA)->get(route('admin.reports.index', [
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
        ]));

        $response->assertStatus(200);
        // Kompensasi kerusakan harus Rp 0, bukan Rp 60.000
        $response->assertDontSee('Rp 140.000');
    }

    // ==========================================
    // E. TAMPILAN DAN EKSPOR CSV
    // ==========================================

    public function test_empty_state_is_displayed_when_no_transactions_found(): void
    {
        $response = $this->actingAs($this->adminA)->get(route('admin.reports.index', [
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Tidak ada transaksi ditemukan');
    }

    public function test_csv_export_properly_neutralizes_formula_injection_attempts(): void
    {
        $date = Carbon::create(2026, 10, 15, 12, 0, 0);

        // Customer dengan payload formula Excel
        $maliciousUser = User::create([
            'name' => '=CMD|"/C calc"!A0',
            'email' => 'hacker@test.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        Order::create([
            'client_id' => $this->clientA->id,
            'user_id' => $maliciousUser->id,
            'order_number' => 'ORD-MALICIOUS',
            'rental_start_date' => $date,
            'rental_end_date' => $date->copy()->addDays(1),
            'duration_days' => 1,
            'total_amount' => 100000,
            'shipping_address' => 'Alamat',
            'status' => 'selesai',
            'created_at' => $date,
        ]);

        $response = $this->actingAs($this->adminA)->get(route('admin.reports.export', [
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
        ]));

        $response->assertStatus(200);
        $content = $response->streamedContent();

        // Formula injection harus dinetralkan dengan awalan kutip tunggal: '=CMD...
        $this->assertStringContainsString("'=CMD|\"\"/C calc\"\"!A0", $content);
    }

    public function test_reports_page_contains_redesigned_filter_ui_and_active_period_banner(): void
    {
        $response = $this->actingAs($this->adminA)->get(route('admin.reports.index'));

        $response->assertStatus(200);
        // Label periode laporan
        $response->assertSee('PERIODE LAPORAN');
        // Preset options
        $response->assertSee('Hari Ini');
        $response->assertSee('7 Hari Terakhir');
        $response->assertSee('Bulan Ini');
        $response->assertSee('Rentang Kustom');
        // Petunjuk di bawah input
        $response->assertSee('Pilih rentang tanggal untuk melihat transaksi pada periode tersebut.');
        // Tombol aksi
        $response->assertSee('Terapkan Filter');
        $response->assertSee('Reset');
        // Banner ringkasan periode laporan aktif
        $response->assertSee('Periode laporan:');
        $response->assertSee('Status Periode');
        // Dropdown menu Export
        $response->assertSee('Export');
        $response->assertSee('Export CSV');
        $response->assertSee('Export Excel');
        $response->assertSee('Export PDF');
    }

    public function test_authorized_admin_can_download_excel_export_directly(): void
    {
        $response = $this->actingAs($this->adminA)->get(route('admin.reports.export', [
            'format' => 'excel',
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
        ]));

        $response->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml.sheet', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', (string) $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('.xlsx', (string) $response->headers->get('Content-Disposition'));
    }

    public function test_authorized_admin_can_download_pdf_export_directly_without_preview(): void
    {
        $response = $this->actingAs($this->adminA)->get(route('admin.reports.export', [
            'format' => 'pdf',
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
        ]));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('Content-Type'));
        // Pastikan attachment header aktif untuk download langsung tanpa preview
        $this->assertStringContainsString('attachment', (string) $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('.pdf', (string) $response->headers->get('Content-Disposition'));
    }
}
