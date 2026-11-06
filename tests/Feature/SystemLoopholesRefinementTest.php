<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientPaymentMethod;
use App\Models\DamageCase;
use App\Models\DamageReport;
use App\Models\EquipmentUnit;
use App\Models\IdentityGuarantee;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemUnit;
use App\Models\Product;
use App\Models\ReturnModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SystemLoopholesRefinementTest extends TestCase
{
    use RefreshDatabase;

    protected Client $client;
    protected User $admin;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->client = Client::create([
            'business_name' => 'Baby Rental Test',
            'subdomain' => 'babytest',
            'status' => 'aktif',
        ]);

        $this->category = \App\Models\Category::create([
            'client_id' => $this->client->id,
            'name' => 'Perlengkapan Bayi',
            'slug' => 'perlengkapan-bayi',
        ]);

        $this->admin = User::create([
            'name' => 'Admin Toko',
            'email' => 'admin@babytest.com',
            'password' => bcrypt('password'),
            'role' => 'admin_rental',
            'client_id' => $this->client->id,
            'status' => 'aktif',
        ]);

        $this->customer = User::create([
            'name' => 'Customer Test',
            'email' => 'customer@gmail.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'client_id' => null,
            'status' => 'aktif',
        ]);
    }

    /**
     * Paket 1: Customer dapat mengunggah bukti bayar denda, status badge & note tampil benar,
     * dan admin dapat memverifikasi denda hingga KTP fisik ditandai dikembalikan.
     */
    public function test_customer_can_upload_late_fee_payment_and_admin_can_verify(): void
    {
        $product = Product::create([
            'client_id' => $this->client->id,
            'category_id' => $this->category->id,
            'name' => 'Stroller Test',
            'slug' => 'stroller-test',
            'rental_price_per_day' => 50000,
            'late_fee_per_hour' => 10000,
            'status' => 'aktif',
        ]);

        $order = Order::create([
            'client_id' => $this->client->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-DENDA-001',
            'rental_start_date' => now()->subDays(5),
            'rental_end_date' => now()->subDays(2),
            'duration_days' => 3,
            'total_amount' => 150000,
            'status' => 'menunggu_pembayaran_denda',
            'shipping_method' => 'kurir',
            'shipping_address' => 'Jl. Mawar No 1',
        ]);

        $return = ReturnModel::create([
            'order_id' => $order->id,
            'return_method' => 'kurir',
            'return_date' => now(),
            'return_status' => 'diterima',
            'late_hours' => 5,
            'late_fee_amount' => 50000,
            'late_fee_status' => 'menunggu_pembayaran',
        ]);

        $ktp = IdentityGuarantee::create([
            'order_id' => $order->id,
            'customer_id' => $this->customer->id,
            'full_name' => 'Customer Test',
            'identity_number' => '3578012345678901',
            'identity_address' => 'Jl. Mawar No 1',
            'identity_document_image' => 'ktp.jpg',
            'face_image' => 'selfie.jpg',
            'status' => 'dititipkan',
            'deposited_at' => now()->subDays(5),
        ]);

        // Validasi Accessor Model
        $order->refresh();
        $this->assertTrue($order->can_pay_late_fee);
        $this->assertStringContainsString('amber', $order->status_badge_color);
        $this->assertStringContainsString('50.000', $order->status_note);

        // Customer mengunggah bukti bayar denda
        $file = UploadedFile::fake()->image('bukti_denda.jpg');
        $response = $this->actingAs($this->customer)
            ->post(route('customer.orders.pay-late-fee', $order->id), [
                'late_fee_proof' => $file,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $return->refresh();
        $this->assertEquals('menunggu_verifikasi', $return->late_fee_status);
        $this->assertNotNull($return->late_fee_proof);

        // Admin memverifikasi bukti bayar denda dan menyelesaikan retur
        $adminResponse = $this->actingAs($this->admin)
            ->post("/admin/returns/{$order->id}/process", [
                'late_fee_amount' => 50000,
                'action_type' => 'normal',
            ]);

        $adminResponse->assertRedirect();
        $adminResponse->assertSessionHas('success');

        $order->refresh();
        $return->refresh();
        $ktp->refresh();

        $this->assertEquals('selesai', $order->status);
        $this->assertEquals('selesai', $return->return_status);
        $this->assertEquals('lunas', $return->late_fee_status);
        $this->assertEquals('dikembalikan', $ktp->status);
        $this->assertNotNull($ktp->returned_at);
    }

    /**
     * Paket 2: Command orders:auto-complete mengupdate status jaminan KTP fisik menjadi dikembalikan.
     */
    public function test_auto_complete_orders_command_updates_physical_ktp_guarantee_status(): void
    {
        $order = Order::create([
            'client_id' => $this->client->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-AUTO-001',
            'rental_start_date' => now()->subDays(4),
            'rental_end_date' => now()->subDays(2),
            'duration_days' => 2,
            'total_amount' => 100000,
            'status' => 'dikembalikan',
            'shipping_method' => 'ambil_toko',
            'shipping_address' => 'Ambil di toko',
            'updated_at' => now()->subHours(25),
        ]);

        $return = ReturnModel::create([
            'order_id' => $order->id,
            'return_method' => 'ambil_toko',
            'return_date' => now()->subHours(25),
            'return_status' => 'diterima',
            'late_fee_status' => 'tidak_ada',
            'updated_at' => now()->subHours(25),
        ]);

        $ktp = IdentityGuarantee::create([
            'order_id' => $order->id,
            'customer_id' => $this->customer->id,
            'full_name' => 'Customer Test',
            'identity_number' => '3578012345678901',
            'identity_address' => 'Jl. Mawar No 1',
            'identity_document_image' => 'ktp.jpg',
            'face_image' => 'selfie.jpg',
            'status' => 'dititipkan',
            'deposited_at' => now()->subDays(4),
        ]);

        $this->artisan('orders:auto-complete')->assertSuccessful();

        $order->refresh();
        $ktp->refresh();
        $return->refresh();

        $this->assertEquals('selesai', $order->status);
        $this->assertEquals('selesai', $return->return_status);
        $this->assertEquals('dikembalikan', $ktp->status);
        $this->assertNotNull($ktp->returned_at);
    }

    /**
     * Paket 3: Toko yang berstatus suspended menampilkan view errors.tenant-suspended dengan HTTP status 503.
     */
    public function test_suspended_tenant_renders_tenant_suspended_view_with_503(): void
    {
        $suspendedClient = Client::create([
            'business_name' => 'Toko Suspended',
            'subdomain' => 'tokosuspend',
            'status' => 'suspended',
        ]);

        $response = $this->get("/client/{$suspendedClient->subdomain}");
        $response->assertStatus(503);
        $response->assertSee('Toko Sewa Sedang Ditangguhkan');
        $response->assertSee('Toko Suspended');
    }

    /**
     * Paket 4: Validasi registrasi toko menolak reserved subdomains (admin, api, owner, dll).
     */
    public function test_registration_rejects_reserved_subdomains(): void
    {
        $response = $this->post('/register-tenant', [
            'username' => 'tenantadmin',
            'email' => 'admin@reserved.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'business_name' => 'Toko Admin',
            'subdomain' => 'admin', // Reserved keyword
            'description' => 'Deskripsi usaha',
            'phone' => '081234567890',
            'address' => 'Jl. Protokol No 1',
            'plan_name' => 'starter',
            'terms' => '1',
        ]);

        $response->assertSessionHasErrors('subdomain');
    }

    /**
     * Paket 5: Pesanan menggantung (menunggu_konfirmasi) lebih dari 48 jam otomatis dibatalkan oleh scheduler.
     */
    public function test_stale_menunggu_konfirmasi_orders_auto_cancelled_after_48_hours(): void
    {
        $staleOrder = Order::create([
            'client_id' => $this->client->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-STALE-48H',
            'rental_start_date' => now()->addDays(5),
            'rental_end_date' => now()->addDays(7),
            'duration_days' => 2,
            'total_amount' => 100000,
            'status' => 'menunggu_konfirmasi',
            'shipping_method' => 'kurir',
            'shipping_address' => 'Jl. Mawar No 1',
            'created_at' => Carbon::now()->subHours(49),
            'updated_at' => Carbon::now()->subHours(49),
        ]);

        $this->artisan('orders:auto-cancel-unpaid')->assertSuccessful();

        $staleOrder->refresh();
        $this->assertEquals('dibatalkan', $staleOrder->status);
        $this->assertStringContainsString('48 jam', $staleOrder->cancellation_reason);
    }

    /**
     * Paket 6: Pembatalan pesanan mandiri oleh customer membersihkan alokasi unit fisik dan me-reset statusnya.
     */
    public function test_customer_canceling_order_cleans_up_equipment_units_allocation(): void
    {
        $product = Product::create([
            'client_id' => $this->client->id,
            'category_id' => $this->category->id,
            'name' => 'Box Bayi Test',
            'slug' => 'box-bayi-test',
            'rental_price_per_day' => 40000,
            'status' => 'aktif',
        ]);

        $unit = EquipmentUnit::create([
            'client_id' => $this->client->id,
            'product_id' => $product->id,
            'unit_code' => 'UNIT-BOX-001',
            'serial_number' => 'SN-BOX-001',
            'status' => 'disewa',
        ]);

        $order = Order::create([
            'client_id' => $this->client->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-CANCEL-UNIT',
            'rental_start_date' => now()->addDays(1),
            'rental_end_date' => now()->addDays(3),
            'duration_days' => 2,
            'total_amount' => 80000,
            'status' => 'menunggu_pembayaran',
            'shipping_method' => 'kurir',
            'shipping_address' => 'Jl. Mawar No 1',
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price_per_day' => 40000,
            'subtotal' => 80000,
        ]);

        $oiu = OrderItemUnit::create([
            'order_item_id' => $orderItem->id,
            'equipment_unit_id' => $unit->id,
        ]);

        $this->assertDatabaseHas('order_item_units', ['id' => $oiu->id]);

        // Customer membatalkan pesanan
        $response = $this->actingAs($this->customer)
            ->post(route('customer.orders.cancel', $order->id), [
                'reason' => 'Ingin ganti tanggal sewa',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $order->refresh();
        $unit->refresh();

        $this->assertEquals('batal', $order->status);
        $this->assertDatabaseMissing('order_item_units', ['id' => $oiu->id]);
        $this->assertEquals('tersedia', $unit->status);
    }

    public function test_admin_rental_cannot_access_checkout_page(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('customer.checkout', $this->client->subdomain));

        $response->assertRedirect(route('customer.home', $this->client->subdomain));
        $response->assertSessionHas('error', 'Akun Pengelola (Admin/Owner) tidak dapat melakukan transaksi sewa. Silakan keluar dan gunakan Akun Customer untuk menyewa alat.');
    }

    public function test_admin_rental_cannot_submit_checkout_order(): void
    {
        $initialOrderCount = Order::count();

        $response = $this->actingAs($this->admin)
            ->post(route('customer.checkout.store', $this->client->subdomain), [
                'shipping_address' => 'Jl. Pengelola No 123',
                'shipping_method' => 'kurir',
                'full_name' => 'Admin Pengelola',
                'identity_number' => '3578000000000001',
                'identity_address' => 'Jl. Pengelola No 123',
                'identity_document_image' => UploadedFile::fake()->image('ktp.jpg'),
                'face_image' => UploadedFile::fake()->image('selfie.jpg'),
                'terms' => '1',
            ]);

        $response->assertRedirect(route('customer.home', $this->client->subdomain));
        $response->assertSessionHas('error', 'Akun Pengelola (Admin/Owner) tidak dapat melakukan transaksi sewa. Silakan keluar dan gunakan Akun Customer untuk menyewa alat.');

        $this->assertEquals($initialOrderCount, Order::count());
    }

    public function test_owner_cannot_access_or_submit_checkout(): void
    {
        $owner = User::create([
            'name' => 'Platform Owner',
            'email' => 'owner@rentalbase.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'client_id' => null,
            'status' => 'aktif',
        ]);

        // Test GET checkout
        $responseGet = $this->actingAs($owner)
            ->get(route('customer.checkout', $this->client->subdomain));

        $responseGet->assertRedirect(route('customer.home', $this->client->subdomain));
        $responseGet->assertSessionHas('error', 'Akun Pengelola (Admin/Owner) tidak dapat melakukan transaksi sewa. Silakan keluar dan gunakan Akun Customer untuk menyewa alat.');

        // Test POST checkout
        $initialOrderCount = Order::count();

        $responsePost = $this->actingAs($owner)
            ->post(route('customer.checkout.store', $this->client->subdomain), [
                'shipping_address' => 'Jl. Platform Owner No 1',
            ]);

        $responsePost->assertRedirect(route('customer.home', $this->client->subdomain));
        $responsePost->assertSessionHas('error', 'Akun Pengelola (Admin/Owner) tidak dapat melakukan transaksi sewa. Silakan keluar dan gunakan Akun Customer untuk menyewa alat.');

        $this->assertEquals($initialOrderCount, Order::count());
    }

    public function test_admin_rental_cannot_use_direct_checkout(): void
    {
        $product = Product::create([
            'client_id' => $this->client->id,
            'category_id' => $this->category->id,
            'name' => 'Stroller Bayi Premium Direct',
            'slug' => 'stroller-bayi-premium-direct',
            'rental_price_per_day' => 50000,
            'deposit_fee' => 100000,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('customer.checkout.direct', ['subdomain' => $this->client->subdomain, 'product' => $product->id]));

        $response->assertRedirect(route('customer.home', $this->client->subdomain));
        $response->assertSessionHas('error', 'Akun Pengelola (Admin/Owner) tidak dapat melakukan transaksi sewa. Silakan keluar dan gunakan Akun Customer untuk menyewa alat.');
    }

    public function test_admin_can_request_ktp_reupload_without_cancelling_order(): void
    {
        $order = Order::create([
            'client_id' => $this->client->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-REUPLOAD-01',
            'rental_start_date' => now()->addDays(2),
            'rental_end_date' => now()->addDays(4),
            'duration_days' => 2,
            'total_amount' => 100000,
            'status' => 'menunggu_konfirmasi',
            'shipping_method' => 'kurir',
            'shipping_address' => 'Jl. Kenanga No 5',
        ]);

        $identity = IdentityGuarantee::create([
            'order_id' => $order->id,
            'customer_id' => $this->customer->id,
            'full_name' => 'Customer Test',
            'identity_number' => '3578000000000002',
            'identity_address' => 'Jl. Kenanga No 5',
            'identity_document_image' => 'ktp_buram.jpg',
            'face_image' => 'selfie_buram.jpg',
            'status' => 'menunggu',
        ]);

        // Admin request reupload
        $response = $this->actingAs($this->admin)
            ->post(route('admin.orders.reject', $order->id), [
                'action_type' => 'request_reupload',
                'cancellation_reason' => 'Foto KTP buram dan silau.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $order->refresh();
        $identity->refresh();

        $this->assertEquals('menunggu_konfirmasi', $order->status);
        $this->assertEquals('ditolak', $identity->status);
        $this->assertTrue($order->can_reupload_identity);

        // Customer reuploads clearer KTP
        $reuploadResponse = $this->actingAs($this->customer)
            ->post(route('customer.orders.identity', $order->id), [
                'identity_document_image' => UploadedFile::fake()->image('ktp_jelas.jpg'),
                'face_image' => UploadedFile::fake()->image('selfie_jelas.jpg'),
            ]);

        $reuploadResponse->assertRedirect();
        $reuploadResponse->assertSessionHas('success');

        $order->refresh();
        $identity->refresh();

        $this->assertEquals('menunggu', $identity->status);
        $this->assertEquals('menunggu_konfirmasi', $order->status);
    }

    public function test_cross_tenant_cart_items_are_isolated_per_shop(): void
    {
        $tenantB = Client::create([
            'business_name' => 'Kamera Rental Test',
            'subdomain' => 'kameratest',
            'status' => 'aktif',
        ]);

        $productA = Product::create([
            'client_id' => $this->client->id,
            'category_id' => $this->category->id,
            'name' => 'Stroller Toko A',
            'slug' => 'stroller-toko-a',
            'rental_price_per_day' => 50000,
            'deposit_fee' => 100000,
            'status' => 'aktif',
        ]);

        // Masukkan item dari Toko A ke session cart
        $hashA = md5($productA->id . '2026-12-012026-12-03');
        $cart = [
            $hashA => [
                'hash' => $hashA,
                'client_id' => $this->client->id,
                'product_id' => $productA->id,
                'name' => $productA->name,
                'price' => 50000,
                'deposit_fee' => 100000,
                'main_image' => null,
                'start_date' => '2026-12-01',
                'end_date' => '2026-12-03',
                'duration_days' => 2,
                'quantity' => 1,
                'subtotal' => 100000,
            ],
        ];

        // Customer buka checkout di Toko B
        $response = $this->actingAs($this->customer)
            ->withSession(['cart' => $cart])
            ->get(route('customer.checkout', $tenantB->subdomain));

        // Harus di-redirect ke katalog Toko B dengan flash error keranjang kosong di toko ini
        $response->assertRedirect(route('customer.home', $tenantB->subdomain));
        $response->assertSessionHas('error', 'Keranjang belanja kamu di toko ini masih kosong.');
    }

    public function test_customer_cannot_pay_using_other_tenant_payment_method(): void
    {
        $tenantB = Client::create([
            'business_name' => 'Toko B Beda Tenant',
            'subdomain' => 'tokobeda',
            'status' => 'aktif',
        ]);

        $paymentMethodB = ClientPaymentMethod::create([
            'client_id' => $tenantB->id,
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_holder' => 'Toko B Rekening',
            'is_active' => true,
        ]);

        $order = Order::create([
            'client_id' => $this->client->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-SPOOF-PAY',
            'rental_start_date' => now()->addDays(2),
            'rental_end_date' => now()->addDays(4),
            'duration_days' => 2,
            'total_amount' => 100000,
            'status' => 'menunggu_pembayaran',
            'shipping_method' => 'kurir',
            'shipping_address' => 'Jl. Kenanga No 5',
        ]);

        IdentityGuarantee::create([
            'order_id' => $order->id,
            'customer_id' => $this->customer->id,
            'full_name' => 'Customer Test',
            'identity_number' => '3578000000000003',
            'identity_address' => 'Jl. Kenanga No 5',
            'identity_document_image' => 'ktp.jpg',
            'face_image' => 'selfie.jpg',
            'status' => 'diverifikasi',
        ]);

        $response = $this->actingAs($this->customer)
            ->post(route('customer.orders.payment', $order->id), [
                'payment_proof' => UploadedFile::fake()->image('transfer.jpg'),
                'client_payment_method_id' => $paymentMethodB->id,
            ]);

        $response->assertSessionHasErrors(['client_payment_method_id']);
    }

    public function test_closing_damage_case_completes_order_and_returns_ktp(): void
    {
        $product = Product::create([
            'client_id' => $this->client->id,
            'category_id' => $this->category->id,
            'name' => 'Stroller Rusak Ringan',
            'slug' => 'stroller-rusak-ringan',
            'rental_price_per_day' => 50000,
            'deposit_fee' => 100000,
            'status' => 'aktif',
        ]);

        $unit = EquipmentUnit::create([
            'client_id' => $this->client->id,
            'product_id' => $product->id,
            'unit_code' => 'UNIT-DMG-001',
            'serial_number' => 'SN-DMG-001',
            'status' => 'disewa',
            'condition' => 'rusak',
        ]);

        $order = Order::create([
            'client_id' => $this->client->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-DAMAGE-RESOLVE',
            'rental_start_date' => now()->subDays(3),
            'rental_end_date' => now()->subDays(1),
            'duration_days' => 2,
            'total_amount' => 100000,
            'status' => 'sedang_disewa',
            'shipping_method' => 'kurir',
            'shipping_address' => 'Jl. Melati No 10',
        ]);

        $identity = IdentityGuarantee::create([
            'order_id' => $order->id,
            'customer_id' => $this->customer->id,
            'full_name' => 'Customer Test',
            'identity_number' => '3578000000000004',
            'identity_address' => 'Jl. Melati No 10',
            'identity_document_image' => 'ktp.jpg',
            'face_image' => 'selfie.jpg',
            'status' => 'dititipkan',
            'deposited_at' => now()->subDays(3),
        ]);

        $return = ReturnModel::create([
            'order_id' => $order->id,
            'return_date' => now()->subHours(5),
            'return_method' => 'kurir',
            'return_status' => 'diterima',
            'late_hours' => 0,
            'calculated_late_fee' => 0,
            'late_fee_amount' => 0,
            'late_fee_status' => 'tidak_ada',
            'processed_by' => $this->admin->id,
        ]);

        $damageReport = DamageReport::create([
            'order_id' => $order->id,
            'equipment_unit_id' => $unit->id,
            'description' => 'Roda depan patah',
            'reported_by' => $this->admin->id,
        ]);

        $damageCase = DamageCase::create([
            'damage_report_id' => $damageReport->id,
            'repair_cost' => 50000,
            'fine_amount' => 0,
            'payment_status' => 'lunas',
            'case_status' => 'kompensasi_dibayar',
        ]);

        // Admin menutup kasus kerusakan
        $response = $this->actingAs($this->admin)
            ->post(route('admin.damage-cases.close', $damageCase->id), [
                'closing_notes' => 'Unit selesai diperbaiki dan customer telah melunasi biaya.',
                'unit_status_after' => 'tersedia',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $order->refresh();
        $return->refresh();
        $identity->refresh();
        $unit->refresh();

        $this->assertEquals('selesai', $order->status);
        $this->assertEquals('selesai', $return->return_status);
        $this->assertEquals('dikembalikan', $identity->status);
        $this->assertNotNull($identity->returned_at);
        $this->assertEquals('tersedia', $unit->status);
    }

    public function test_checkout_rejects_past_rental_time_for_today(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 11, 2, 15, 0, 0));

        try {
            $product = Product::create([
                'client_id' => $this->client->id,
                'category_id' => $this->category->id,
                'name' => 'Stroller Bayi Today',
                'slug' => 'stroller-bayi-today',
                'rental_price_per_day' => 50000,
                'deposit_fee' => 100000,
                'status' => 'aktif',
            ]);

            EquipmentUnit::create([
                'client_id' => $this->client->id,
                'product_id' => $product->id,
                'unit_code' => 'UNIT-TODAY-001',
                'serial_number' => 'SN-TODAY-001',
                'status' => 'tersedia',
            ]);

            $today = '2026-11-02';
            $hash = md5($product->id . $today . $today);

            $cart = [
                $hash => [
                    'hash' => $hash,
                    'client_id' => $this->client->id,
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => 50000,
                    'deposit_fee' => 100000,
                    'main_image' => null,
                    'start_date' => $today,
                    'end_date' => $today,
                    'duration_days' => 1,
                    'quantity' => 1,
                    'subtotal' => 50000,
                ],
            ];

            // Kirim jam yang sudah lewat di hari yang sama (10:00 padahal jam sekarang 15:00)
            $response = $this->actingAs($this->customer)
                ->withSession([
                    'cart' => $cart,
                    'checkout_items' => [$hash],
                ])
                ->post(route('customer.checkout.store', $this->client->subdomain), [
                    'selected_hashes' => [$hash],
                    'start_date' => $today,
                    'end_date' => $today,
                    'rental_time' => '10:00',
                    'shipping_address' => 'Jl. Percobaan No 100',
                    'shipping_method' => 'kurir',
                    'full_name' => 'Customer Test',
                    'identity_number' => '3578000000000005',
                    'identity_address' => 'Jl. Percobaan No 100',
                    'identity_document_image' => UploadedFile::fake()->image('ktp.jpg'),
                    'face_image' => UploadedFile::fake()->image('selfie.jpg'),
                    'terms' => '1',
                ]);

            $response->assertSessionHasErrors(['rental_time']);
        } finally {
            Carbon::setTestNow();
        }
    }
}
