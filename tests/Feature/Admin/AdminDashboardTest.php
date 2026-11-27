<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Client;
use App\Models\ClientPaymentMethod;
use App\Models\EquipmentUnit;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private Client $clientA;
    private Client $clientB;
    private Category $categoryA;
    private Category $categoryB;
    private User $adminA;
    private User $adminB;
    private User $customer;
    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        // 1. Setup Tenant Client A & Primary Admin
        $this->clientA = Client::create([
            'business_name' => 'Kamera Jaya Rental',
            'subdomain' => 'kamerajaya',
            'status' => 'aktif',
        ]);

        $this->categoryA = Category::create([
            'client_id' => $this->clientA->id,
            'name' => 'Kamera',
            'slug' => 'kamera',
        ]);

        $this->adminA = User::create([
            'client_id' => $this->clientA->id,
            'name' => 'Admin Utama Toko A',
            'email' => 'admin.a@kamerajaya.test',
            'password' => Hash::make('password123'),
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        $this->clientA->update(['primary_admin_id' => $this->adminA->id]);

        Subscription::create([
            'client_id' => $this->clientA->id,
            'plan_name' => 'starter',
            'max_products' => 10,
            'max_users' => 1,
            'price' => 0,
            'billing_cycle' => 'monthly',
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->addDays(25),
            'status' => 'aktif',
        ]);

        // 2. Setup Tenant Client B & Admin (untuk isolasi data)
        $this->clientB = Client::create([
            'business_name' => 'Outdoor Malang Rental',
            'subdomain' => 'outdoormalang',
            'status' => 'aktif',
        ]);

        $this->categoryB = Category::create([
            'client_id' => $this->clientB->id,
            'name' => 'Outdoor',
            'slug' => 'outdoor',
        ]);

        $this->adminB = User::create([
            'client_id' => $this->clientB->id,
            'name' => 'Admin Toko B',
            'email' => 'admin.b@outdoormalang.test',
            'password' => Hash::make('password123'),
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        // 3. Setup Customer
        $this->customer = User::create([
            'name' => 'Pelanggan Biasa',
            'email' => 'customer@gmail.test',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        // 4. Setup Owner Platform
        $this->owner = User::create([
            'name' => 'Owner Platform',
            'email' => 'owner@rentalbase.id',
            'password' => Hash::make('password123'),
            'role' => 'owner',
            'status' => 'aktif',
        ]);
    }

    // ==========================================
    // A. OTORISASI & PROTEKSI ROLE (401 / 403 / REDIRECT)
    // ==========================================

    public function test_unauthenticated_user_cannot_access_admin_dashboard(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect('/');
        $response->assertSessionHas('error', 'Silakan login terlebih dahulu.');
    }

    public function test_customer_cannot_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->customer)->get(route('admin.dashboard'));

        $response->assertRedirect('/');
        $response->assertSessionHas('error', 'Akses ditolak. Halaman tersebut hanya dapat diakses oleh pengelola toko.');
    }

    public function test_owner_cannot_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->owner)->get(route('admin.dashboard'));

        $response->assertRedirect(route('owner.dashboard'));
        $response->assertSessionHas('error', 'Akses ditolak. Halaman tersebut khusus untuk pengelola toko (Admin Rental).');
    }

    public function test_inactive_admin_rental_cannot_access_admin_dashboard(): void
    {
        $inactiveAdmin = User::create([
            'client_id' => $this->clientA->id,
            'name' => 'Admin Nonaktif',
            'email' => 'inactive.admin@kamerajaya.test',
            'password' => Hash::make('password123'),
            'role' => 'admin_rental',
            'status' => 'nonaktif',
        ]);

        $response = $this->actingAs($inactiveAdmin)->get(route('admin.dashboard'));

        $response->assertRedirect('/');
        $response->assertSessionHas('error', 'Akun tidak aktif.');
        $this->assertGuest();
    }

    public function test_pending_setup_admin_is_redirected_to_registration_setup(): void
    {
        $pendingUser = User::create([
            'name' => 'Admin Belum Setup',
            'email' => 'pending@kamerajaya.test',
            'password' => Hash::make('password123'),
            'role' => 'admin_rental',
            'status' => 'pending_setup',
        ]);

        $response = $this->actingAs($pendingUser)->get(route('admin.dashboard'));

        $response->assertRedirect(route('tenant.register'));
    }

    // ==========================================
    // B. SKENARIO POSITIF & METRIK OPERASIONAL
    // ==========================================

    public function test_admin_rental_can_view_dashboard_with_operational_metrics(): void
    {
        Cache::flush();

        // 1. Buat data Produk & Unit untuk Toko A
        $product = Product::create([
            'client_id' => $this->clientA->id,
            'category_id' => $this->categoryA->id,
            'name' => 'Sony Alpha A7 IV',
            'slug' => 'sony-alpha-a7-iv',
            'rental_price' => 250000,
            'status' => 'aktif',
        ]);

        EquipmentUnit::create([
            'client_id' => $this->clientA->id,
            'product_id' => $product->id,
            'serial_number' => 'SN-SNY-001',
            'unit_code' => 'CAM-01',
            'condition' => 'baik',
            'status' => 'tersedia',
        ]);

        // 2. Buat Order Baru (menunggu_pembayaran)
        Order::create([
            'client_id' => $this->clientA->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-TEST-001',
            'rental_start_date' => Carbon::today(),
            'rental_end_date' => Carbon::today()->addDays(2),
            'duration_days' => 2,
            'total_amount' => 500000,
            'shipping_address' => 'Jl. Kamera No. 1 Malang',
            'shipping_method' => 'kurir',
            'status' => 'menunggu_pembayaran',
        ]);

        // 3. Buat Order Sedang Disewa
        Order::create([
            'client_id' => $this->clientA->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-TEST-002',
            'rental_start_date' => Carbon::yesterday(),
            'rental_end_date' => Carbon::tomorrow(),
            'duration_days' => 2,
            'total_amount' => 300000,
            'shipping_address' => 'Jl. Kamera No. 2 Malang',
            'shipping_method' => 'kurir',
            'status' => 'sedang_disewa',
        ]);

        // 4. Buat Order dengan Pembayaran Menunggu Konfirmasi
        $order3 = Order::create([
            'client_id' => $this->clientA->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-TEST-003',
            'rental_start_date' => Carbon::today(),
            'rental_end_date' => Carbon::today()->addDays(1),
            'duration_days' => 1,
            'total_amount' => 250000,
            'shipping_address' => 'Jl. Kamera No. 3 Malang',
            'shipping_method' => 'kurir',
            'status' => 'menunggu_verifikasi',
        ]);

        $paymentMethod = ClientPaymentMethod::create([
            'client_id' => $this->clientA->id,
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_holder' => 'Toko Kamera',
            'is_active' => true,
        ]);

        Payment::create([
            'order_id' => $order3->id,
            'client_payment_method_id' => $paymentMethod->id,
            'amount' => 250000,
            'payment_proof' => 'proof.jpg',
            'status' => 'menunggu_konfirmasi',
        ]);

        $response = $this->actingAs($this->adminA)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
        $response->assertViewHas('totalBookingBaru', 1);
        $response->assertViewHas('totalDisewa', 1);
        $response->assertViewHas('menungguVerifikasi', 1);
        $response->assertViewHas('totalProduk', 1);
        $response->assertViewHas('totalUnit', 1);
        $response->assertViewHas('pendapatanBulanIni', 300000.0);
        $response->assertViewHas('maxProduk', 10);
        $response->assertViewHas('maxKategori', 5);
        $response->assertViewHas('maxAdmin', 1);
        $response->assertViewHas('daysRemaining');
        $response->assertViewHas('isExpired', false);
        $response->assertSee('Paket Starter');
        $response->assertSee('Maks. 10 jenis produk');
        $response->assertSee('Maks. 50 unit peralatan total');
        $response->assertSee('Maks. 5 kategori');
        $response->assertSee('Maks. 1 Admin Rental');
        $response->assertSee('Hari Tersisa');
        $response->assertSee('ORD-TEST-001');
    }

    // ==========================================
    // C. MULTI-TENANT ISOLATION (ISOLASI DATA TOKO)
    // ==========================================

    public function test_admin_dashboard_metrics_and_orders_are_isolated_by_tenant(): void
    {
        Cache::flush();

        // Data Toko A
        Product::create([
            'client_id' => $this->clientA->id,
            'category_id' => $this->categoryA->id,
            'name' => 'Lensa Canon Toko A',
            'slug' => 'lensa-canon-toko-a',
            'rental_price' => 100000,
            'status' => 'aktif',
        ]);

        Order::create([
            'client_id' => $this->clientA->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-TOKO-A-999',
            'rental_start_date' => Carbon::today(),
            'rental_end_date' => Carbon::today()->addDays(2),
            'duration_days' => 2,
            'total_amount' => 200000,
            'shipping_address' => 'Jl. Toko A No. 1',
            'shipping_method' => 'kurir',
            'status' => 'sedang_disewa',
        ]);

        // Data Toko B (Harus tidak terlihat oleh Admin Toko A)
        Product::create([
            'client_id' => $this->clientB->id,
            'category_id' => $this->categoryB->id,
            'name' => 'Tenda Dome Toko B',
            'slug' => 'tenda-dome-toko-b',
            'rental_price' => 75000,
            'status' => 'aktif',
        ]);

        Order::create([
            'client_id' => $this->clientB->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-TOKO-B-888',
            'rental_start_date' => Carbon::today(),
            'rental_end_date' => Carbon::today()->addDays(2),
            'duration_days' => 2,
            'total_amount' => 500000,
            'shipping_address' => 'Jl. Toko B No. 1',
            'shipping_method' => 'kurir',
            'status' => 'sedang_disewa',
        ]);

        $responseA = $this->actingAs($this->adminA)->get(route('admin.dashboard'));

        $responseA->assertStatus(200);
        $responseA->assertViewHas('totalProduk', 1);
        $responseA->assertViewHas('totalDisewa', 1);
        $responseA->assertViewHas('pendapatanBulanIni', 200000.0);
        $responseA->assertSee('ORD-TOKO-A-999');
        $responseA->assertDontSee('ORD-TOKO-B-888');
    }

    // ==========================================
    // D. LOGOUT ADMIN
    // ==========================================

    public function test_admin_rental_can_logout_via_admin_route(): void
    {
        $response = $this->actingAs($this->adminA)->post(route('admin.logout'));

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_tc_int_mod04_001_authorized_admin_can_access_dashboard_with_metrics(): void
    {
        $response = $this->actingAs($this->adminA)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
        $response->assertSee('Kamera Jaya Rental');
    }

    public function test_tc_int_mod01_009_guest_is_redirected_when_accessing_admin_dashboard(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/');
        $response->assertSessionHas('error');
    }

    public function test_tc_int_mod01_010_customer_cannot_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->customer)->get('/admin/dashboard');

        $response->assertRedirect('/');
        $response->assertSessionHas('error');
    }

    public function test_tc_int_mod02_001_tenant_product_data_isolation_on_dashboard(): void
    {
        Product::create([
            'client_id' => $this->clientA->id,
            'category_id' => $this->categoryA->id,
            'name' => 'Sony A7 IV Toko A',
            'slug' => 'sony-a7-iv-a',
            'rental_price_per_day' => 250000,
            'status' => 'aktif',
        ]);

        Product::create([
            'client_id' => $this->clientB->id,
            'category_id' => $this->categoryB->id,
            'name' => 'Tenda Dome Toko B',
            'slug' => 'tenda-dome-b',
            'rental_price_per_day' => 100000,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->adminA)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('totalProduk', 1);
    }

    public function test_admin_dashboard_provides_revenue_chart_data_and_unit_status_data(): void
    {
        Cache::flush();

        $prodA = Product::create([
            'client_id' => $this->clientA->id,
            'category_id' => $this->categoryA->id,
            'name' => 'Produk Tes Toko A',
            'slug' => 'produk-tes-a',
            'rental_price_per_day' => 100000,
            'status' => 'aktif',
        ]);

        $prodB = Product::create([
            'client_id' => $this->clientB->id,
            'category_id' => $this->categoryB->id,
            'name' => 'Produk Tes Toko B',
            'slug' => 'produk-tes-b',
            'rental_price_per_day' => 100000,
            'status' => 'aktif',
        ]);

        // 1. Buat unit dengan status berbeda untuk Toko A
        EquipmentUnit::create([
            'client_id' => $this->clientA->id,
            'product_id' => $prodA->id,
            'unit_code' => 'UNIT-A-01',
            'condition' => 'baik',
            'status' => 'tersedia',
        ]);
        EquipmentUnit::create([
            'client_id' => $this->clientA->id,
            'product_id' => $prodA->id,
            'unit_code' => 'UNIT-A-02',
            'condition' => 'baik',
            'status' => 'disewa',
        ]);
        EquipmentUnit::create([
            'client_id' => $this->clientA->id,
            'product_id' => $prodA->id,
            'unit_code' => 'UNIT-A-03',
            'condition' => 'perlu_dibersihkan',
            'status' => 'maintenance',
        ]);
        EquipmentUnit::create([
            'client_id' => $this->clientA->id,
            'product_id' => $prodA->id,
            'unit_code' => 'UNIT-A-04',
            'condition' => 'rusak_ringan',
            'status' => 'rusak',
        ]);

        // Unit untuk Toko B (harus tidak terhitung di Toko A)
        EquipmentUnit::create([
            'client_id' => $this->clientB->id,
            'product_id' => $prodB->id,
            'unit_code' => 'UNIT-B-01',
            'condition' => 'baik',
            'status' => 'tersedia',
        ]);

        // 2. Buat order untuk 30 hari terakhir
        Order::create([
            'client_id' => $this->clientA->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-CHART-01',
            'rental_start_date' => Carbon::today(),
            'rental_end_date' => Carbon::today()->addDays(2),
            'duration_days' => 2,
            'total_amount' => 175000,
            'shipping_address' => 'Jl. Mawar No. 1',
            'shipping_method' => 'kurir',
            'status' => 'sedang_disewa',
            'created_at' => Carbon::today(),
        ]);

        // Order batal (tidak boleh masuk revenue chart)
        Order::create([
            'client_id' => $this->clientA->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-CHART-BATAL',
            'rental_start_date' => Carbon::today(),
            'rental_end_date' => Carbon::today()->addDays(2),
            'duration_days' => 2,
            'total_amount' => 999000,
            'shipping_address' => 'Jl. Mawar No. 2',
            'shipping_method' => 'kurir',
            'status' => 'dibatalkan',
            'created_at' => Carbon::today(),
        ]);

        $response = $this->actingAs($this->adminA)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('revenueChartData');
        $response->assertViewHas('unitStatusData');

        $revenueChartData = $response->viewData('revenueChartData');
        $this->assertCount(30, $revenueChartData['labels']);
        $this->assertCount(30, $revenueChartData['data']);
        $this->assertEquals(175000, $revenueChartData['total_30_days']);

        $unitStatusData = $response->viewData('unitStatusData');
        $this->assertEquals(1, $unitStatusData['tersedia']);
        $this->assertEquals(1, $unitStatusData['disewa']);
        $this->assertEquals(1, $unitStatusData['maintenance']);
        $this->assertEquals(1, $unitStatusData['rusak']);
        $this->assertEquals(4, $unitStatusData['total']);
        $this->assertEquals(25, $unitStatusData['utilization_rate']); // 1 dari 4 = 25%

        // Cek elemen Chart.js di HTML
        $response->assertSee('https://cdn.jsdelivr.net/npm/chart.js', false);
        $response->assertSee('revenueChart');
        $response->assertSee('unitStatusChart');
    }

    public function test_admin_dashboard_provides_top_products_and_today_schedules(): void
    {
        Cache::flush();

        $product1 = Product::create([
            'client_id' => $this->clientA->id,
            'category_id' => $this->categoryA->id,
            'name' => 'Kamera Canon R5',
            'slug' => 'canon-r5',
            'rental_price_per_day' => 300000,
            'status' => 'aktif',
        ]);

        $product2 = Product::create([
            'client_id' => $this->clientA->id,
            'category_id' => $this->categoryA->id,
            'name' => 'Lensa 24-70mm',
            'slug' => 'lensa-24-70',
            'rental_price_per_day' => 150000,
            'status' => 'aktif',
        ]);

        // Order kirim hari ini (siap_kirim)
        $orderKirim = Order::create([
            'client_id' => $this->clientA->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-SHIP-01',
            'rental_start_date' => Carbon::today(),
            'rental_end_date' => Carbon::today()->addDays(2),
            'duration_days' => 2,
            'total_amount' => 600000,
            'shipping_address' => 'Jl. Melati No. 5',
            'shipping_method' => 'kurir',
            'status' => 'siap_kirim',
            'created_at' => Carbon::now(),
        ]);

        \App\Models\OrderItem::create([
            'order_id' => $orderKirim->id,
            'product_id' => $product1->id,
            'quantity' => 2,
            'price_per_day' => 300000,
            'subtotal' => 600000,
        ]);

        // Order kembali hari ini (sedang_disewa, rental_end_date = hari ini)
        $orderKembali = Order::create([
            'client_id' => $this->clientA->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-RET-01',
            'rental_start_date' => Carbon::yesterday(),
            'rental_end_date' => Carbon::today(),
            'duration_days' => 1,
            'total_amount' => 150000,
            'shipping_address' => 'Ambil langsung di toko',
            'shipping_method' => 'ambil_toko',
            'status' => 'sedang_disewa',
            'created_at' => Carbon::now(),
        ]);

        \App\Models\OrderItem::create([
            'order_id' => $orderKembali->id,
            'product_id' => $product2->id,
            'quantity' => 1,
            'price_per_day' => 150000,
            'subtotal' => 150000,
        ]);

        $response = $this->actingAs($this->adminA)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('topProducts');
        $response->assertViewHas('todayShipments');
        $response->assertViewHas('todayReturns');

        $topProducts = $response->viewData('topProducts');
        $this->assertNotEmpty($topProducts);
        $this->assertEquals('Kamera Canon R5', $topProducts->first()->name);
        $this->assertEquals(2, $topProducts->first()->total_rented);

        $todayShipments = $response->viewData('todayShipments');
        $this->assertCount(1, $todayShipments);
        $this->assertEquals('ORD-SHIP-01', $todayShipments->first()->order_number);

        $todayReturns = $response->viewData('todayReturns');
        $this->assertCount(1, $todayReturns);
        $this->assertEquals('ORD-RET-01', $todayReturns->first()->order_number);

        $response->assertSee('Kamera Canon R5');
        $response->assertSee('ORD-SHIP-01');
        $response->assertSee('ORD-RET-01');
    }
}

