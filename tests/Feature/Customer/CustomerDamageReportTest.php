<?php

namespace Tests\Feature\Customer;

use App\Models\Category;
use App\Models\Client;
use App\Models\ClientPaymentMethod;
use App\Models\DamageCase;
use App\Models\DamageReport;
use App\Models\EquipmentUnit;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerDamageReportTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;
    private User $customer;
    private User $admin;
    private Product $product;
    private EquipmentUnit $unit;
    private Order $order;
    private DamageReport $damageReport;
    private DamageCase $damageCase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        // Setup Tenant / Client
        $this->client = Client::create([
            'business_name' => 'Stroller Malang Rental',
            'subdomain' => 'strollermalang',
            'status' => 'aktif',
        ]);

        // Setup Admin
        $this->admin = User::create([
            'client_id' => $this->client->id,
            'name' => 'Admin Toko',
            'email' => 'admin@strollermalang.test',
            'password' => bcrypt('password123'),
            'role' => 'admin_rental',
        ]);

        // Setup Customer
        $this->customer = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@gmail.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
        ]);

        // Setup Category & Product
        $category = Category::create([
            'client_id' => $this->client->id,
            'name' => 'Stroller & Kereta Bayi',
            'slug' => 'stroller-kereta-bayi',
        ]);

        $this->product = Product::create([
            'client_id' => $this->client->id,
            'category_id' => $category->id,
            'name' => 'Babyzen YOYO2 6+',
            'slug' => 'babyzen-yoyo2-6-plus',
            'rental_price_per_day' => 85000,
            'late_fee_per_hour' => 15000,
            'status' => 'aktif',
        ]);

        $this->unit = EquipmentUnit::create([
            'client_id' => $this->client->id,
            'product_id' => $this->product->id,
            'unit_code' => 'YOYO-001',
            'status' => 'rusak',
            'condition' => 'rusak',
        ]);

        // Setup Order
        $this->order = Order::create([
            'client_id' => $this->client->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-202610-001',
            'duration_days' => 3,
            'total_amount' => 255000,
            'shipping_address' => 'Jl. Danau Toba No. 45, Sawojajar, Malang',
            'rental_start_date' => now()->subDays(5),
            'rental_end_date' => now()->subDays(2),
            'status' => 'dikembalikan',
        ]);

        OrderItem::create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price_per_day' => 85000,
            'subtotal' => 255000,
        ]);

        // Setup Damage Report & Damage Case
        $this->damageReport = DamageReport::create([
            'order_id' => $this->order->id,
            'equipment_unit_id' => $this->unit->id,
            'description' => 'Sambungan frame kanopi retak patah, perlu diganti sparepart engsel baru.',
            'image' => json_encode(['damages/foto-frame-retak.jpg', 'damages/foto-kanopi.jpg']),
            'reported_by' => $this->admin->id,
        ]);

        $this->damageCase = DamageCase::create([
            'damage_report_id' => $this->damageReport->id,
            'repair_cost' => 125000.00,
            'fine_amount' => 25000.00,
            'payment_status' => 'belum_dibayar',
            'case_status' => 'menunggu_tanggapan',
            'resolution_notes' => '[Toko - Admin] Estimasi biaya penggantian engsel original Rp 125.000 + denda kelalaian Rp 25.000.',
        ]);

        // Setup Rekening Toko
        ClientPaymentMethod::create([
            'client_id' => $this->client->id,
            'bank_name' => 'BCA',
            'account_number' => '8920192831',
            'account_holder' => 'Stroller Malang Rental',
            'is_active' => true,
        ]);
    }

    public function test_guest_cannot_access_damage_report(): void
    {
        $response = $this->get(route('customer.orders.damage', $this->order->id));
        $response->assertRedirect('/');
    }

    public function test_customer_can_view_damage_report_for_their_own_order(): void
    {
        $response = $this->actingAs($this->customer)
            ->get(route('customer.orders.damage', $this->order->id));

        $response->assertStatus(200);
        $response->assertSee('Laporan Kerusakan & Klaim Kompensasi', false);
        $response->assertSee('Babyzen YOYO2 6+');
        $response->assertSee('YOYO-001');
        $response->assertSee('Sambungan frame kanopi retak patah');
        $response->assertSee('Rp 125.000');
        $response->assertSee('Rp 25.000');
        $response->assertSee('Rp 150.000');
        $response->assertSee('Menunggu Tanggapan');
        $response->assertSee('BCA');
        $response->assertSee('8920192831');
    }

    public function test_customer_cannot_view_damage_report_of_another_customer(): void
    {
        $otherCustomer = User::create([
            'name' => 'Siti Nurhaliza',
            'email' => 'siti@gmail.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
        ]);

        $response = $this->actingAs($otherCustomer)
            ->get(route('customer.orders.damage', $this->order->id));

        // Harus 404 agar tidak membocorkan keberadaan order customer lain
        $response->assertStatus(404);
    }

    public function test_customer_can_submit_response_to_damage_case(): void
    {
        $response = $this->actingAs($this->customer)
            ->post(route('customer.orders.damage.respond', $this->order->id), [
                'damage_case_id' => $this->damageCase->id,
                'notes' => 'Mohon maaf atas kelalaian ini, saya bersedia membayar biaya penggantian engsel.',
            ]);

        $response->assertSessionHas('success');
        $response->assertRedirect();

        $this->damageCase->refresh();
        $this->assertEquals('dalam_tinjauan', $this->damageCase->case_status);
        $this->assertStringContainsString('Mohon maaf atas kelalaian ini', $this->damageCase->resolution_notes);
        $this->assertStringContainsString($this->customer->name, $this->damageCase->resolution_notes);
    }

    public function test_response_validation_requires_minimum_length(): void
    {
        $response = $this->actingAs($this->customer)
            ->post(route('customer.orders.damage.respond', $this->order->id), [
                'damage_case_id' => $this->damageCase->id,
                'notes' => 'oke', // Kurang dari 5 karakter
            ]);

        $response->assertSessionHasErrors(['notes']);
    }

    public function test_customer_cannot_respond_to_closed_damage_case(): void
    {
        $this->damageCase->update(['case_status' => 'selesai']);

        $response = $this->actingAs($this->customer)
            ->post(route('customer.orders.damage.respond', $this->order->id), [
                'damage_case_id' => $this->damageCase->id,
                'notes' => 'Saya ingin merespon kasus yang sudah ditutup.',
            ]);

        $response->assertSessionHas('error');
    }

    public function test_customer_can_upload_compensation_payment_proof(): void
    {
        $file = UploadedFile::fake()->image('bukti_transfer_kompensasi.jpg', 600, 800);

        $response = $this->actingAs($this->customer)
            ->post(route('customer.orders.damage.pay', $this->order->id), [
                'damage_case_id' => $this->damageCase->id,
                'payment_proof' => $file,
            ]);

        $response->assertSessionHas('success');
        $response->assertRedirect();

        $this->damageCase->refresh();
        $this->assertEquals('kompensasi_dibayar', $this->damageCase->case_status);
        $this->assertEquals('menunggu_verifikasi', $this->damageCase->payment_status);

        // Pastikan record payment kompensasi tercatat
        $this->assertDatabaseHas('payments', [
            'order_id' => $this->order->id,
            'amount' => 150000.00,
            'status' => 'menunggu_konfirmasi',
        ]);

        $payment = Payment::where('order_id', $this->order->id)->latest()->first();
        Storage::disk('public')->assertExists($payment->payment_proof);
    }

    public function test_payment_proof_rejects_invalid_file_types(): void
    {
        $file = UploadedFile::fake()->create('malicious.exe', 500);

        $response = $this->actingAs($this->customer)
            ->post(route('customer.orders.damage.pay', $this->order->id), [
                'damage_case_id' => $this->damageCase->id,
                'payment_proof' => $file,
            ]);

        $response->assertSessionHasErrors(['payment_proof']);
    }

    public function test_customer_sees_empty_state_when_no_damage_case(): void
    {
        // Order lain tanpa kasus kerusakan
        $cleanOrder = Order::create([
            'client_id' => $this->client->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-202610-002',
            'duration_days' => 2,
            'total_amount' => 100000,
            'shipping_address' => 'Jl. Ijen No. 10, Klojen, Malang',
            'rental_start_date' => now()->subDays(10),
            'rental_end_date' => now()->subDays(8),
            'status' => 'selesai',
        ]);

        $response = $this->actingAs($this->customer)
            ->get(route('customer.orders.damage', $cleanOrder->id));

        $response->assertStatus(200);
        $response->assertSee('Tidak Ada Klaim Kerusakan');
    }
}
