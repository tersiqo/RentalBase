<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Client;
use App\Models\ClientPaymentMethod;
use App\Models\DamageCase;
use App\Models\DamageReport;
use App\Models\EquipmentUnit;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemUnit;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DamageCaseManagementTest extends TestCase
{
    use RefreshDatabase;

    private Client $clientA;
    private Client $clientB;
    private User $adminA;
    private User $adminB;
    private User $customer;
    private Product $productA;
    private EquipmentUnit $unitA;
    private Order $orderA;
    private DamageReport $damageReportA;
    private DamageCase $damageCaseA;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        // Setup Client A (Toko Utama)
        $this->clientA = Client::create([
            'business_name' => 'Rental Base Baby Care',
            'subdomain' => 'babycare',
            'status' => 'aktif',
        ]);

        $this->adminA = User::create([
            'client_id' => $this->clientA->id,
            'name' => 'Iqo Admin',
            'email' => 'admin@babycare.test',
            'password' => bcrypt('password123'),
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        // Setup Client B (Toko Lain untuk uji isolasi)
        $this->clientB = Client::create([
            'business_name' => 'Toko Sebelah Rental',
            'subdomain' => 'tokosebelah',
            'status' => 'aktif',
        ]);

        $this->adminB = User::create([
            'client_id' => $this->clientB->id,
            'name' => 'Admin Sebelah',
            'email' => 'admin@sebelah.test',
            'password' => bcrypt('password123'),
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        // Setup Customer
        $this->customer = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@gmail.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        // Setup Kategori & Produk Client A
        $categoryA = Category::create([
            'client_id' => $this->clientA->id,
            'name' => 'Stroller & Poussette',
            'slug' => 'stroller-poussette',
        ]);

        $this->productA = Product::create([
            'client_id' => $this->clientA->id,
            'category_id' => $categoryA->id,
            'name' => 'Bugaboo Butterfly Ultra Compact',
            'slug' => 'bugaboo-butterfly',
            'rental_price_per_day' => 75000,
            'late_fee_per_hour' => 10000,
            'status' => 'aktif',
        ]);

        $this->unitA = EquipmentUnit::create([
            'client_id' => $this->clientA->id,
            'product_id' => $this->productA->id,
            'unit_code' => 'BGB-001',
            'serial_number' => 'SN-BGB-98124',
            'status' => 'tersedia',
            'condition' => 'baik',
        ]);

        // Setup Order Client A
        $this->orderA = Order::create([
            'client_id' => $this->clientA->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-202610-991',
            'duration_days' => 2,
            'total_amount' => 150000,
            'shipping_address' => 'Jl. Ijen No. 10, Klojen, Malang',
            'status' => 'dikembalikan',
            'rental_start_date' => now()->subDays(4),
            'rental_end_date' => now()->subDays(2),
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $this->orderA->id,
            'product_id' => $this->productA->id,
            'quantity' => 1,
            'price_per_day' => 75000,
            'subtotal' => 150000,
        ]);

        OrderItemUnit::create([
            'order_item_id' => $orderItem->id,
            'equipment_unit_id' => $this->unitA->id,
        ]);

        // Setup Damage Report & Damage Case awal
        $this->damageReportA = DamageReport::create([
            'order_id' => $this->orderA->id,
            'equipment_unit_id' => $this->unitA->id,
            'description' => 'Tuas pelipat stroller patah di sisi kanan.',
            'image' => json_encode(['damages/foto1.jpg', 'damages/foto2.jpg']),
            'reported_by' => $this->adminA->id,
        ]);

        $this->damageCaseA = DamageCase::create([
            'damage_report_id' => $this->damageReportA->id,
            'repair_cost' => 120000.00,
            'fine_amount' => 30000.00,
            'payment_status' => 'belum_dibayar',
            'case_status' => 'terbuka',
            'resolution_notes' => '[Tiket Dibuat oleh Iqo Admin - 21 Okt 2026]\nTuas pelipat stroller patah di sisi kanan.',
        ]);
    }

    public function test_guest_cannot_access_admin_damage_cases(): void
    {
        $response = $this->get(route('admin.damage-cases.index'));
        $response->assertRedirect('/');
    }

    public function test_admin_can_view_damage_cases_index_with_metrics(): void
    {
        $response = $this->actingAs($this->adminA)
            ->get(route('admin.damage-cases.index'));

        $response->assertStatus(200);
        $response->assertSee('Kasus Kerusakan');
        $response->assertSee('#' . $this->damageCaseA->id);
        $response->assertSee('ORD-202610-991');
        $response->assertSee('Budi Santoso');
        $response->assertSee('BGB-001');
        $response->assertSee('Rp 150.000'); // total amount
        $response->assertSee('Menunggu Tanggapan');
    }

    public function test_admin_can_filter_damage_cases(): void
    {
        // Filter by keyword
        $response = $this->actingAs($this->adminA)
            ->get(route('admin.damage-cases.index', ['search' => 'BGB-001']));
        $response->assertStatus(200);
        $response->assertSee('BGB-001');

        // Filter search tidak ada
        $responseMiss = $this->actingAs($this->adminA)
            ->get(route('admin.damage-cases.index', ['search' => 'TIDAK_ADA_XYZ']));
        $responseMiss->assertStatus(200);
        $responseMiss->assertSee('Belum Ada Kasus Kerusakan');
    }

    public function test_admin_can_view_create_page(): void
    {
        $response = $this->actingAs($this->adminA)
            ->get(route('admin.damage-cases.create'));

        $response->assertStatus(200);
        $response->assertSee('Buat Tiket Kasus Kerusakan');
        $response->assertSee('ORD-202610-991');
    }

    public function test_admin_can_fetch_units_for_order_via_json_api(): void
    {
        $response = $this->actingAs($this->adminA)
            ->getJson(route('admin.damage-cases.units', ['order_id' => $this->orderA->id]));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $this->unitA->id,
            'unit_code' => 'BGB-001',
        ]);
    }

    public function test_admin_can_store_new_damage_case_with_photos_and_updates_unit_status(): void
    {
        $unit2 = EquipmentUnit::create([
            'client_id' => $this->clientA->id,
            'product_id' => $this->productA->id,
            'unit_code' => 'BGB-002',
            'serial_number' => 'SN-BGB-98125',
            'status' => 'tersedia',
            'condition' => 'baik',
        ]);

        $photo1 = UploadedFile::fake()->image('bukti1.jpg');
        $photo2 = UploadedFile::fake()->image('bukti2.jpg');

        $response = $this->actingAs($this->adminA)
            ->post(route('admin.damage-cases.store'), [
                'order_id' => $this->orderA->id,
                'equipment_unit_id' => $unit2->id,
                'description' => 'Roda depan macet total dan bearing pecah setelah pemakaian pasir pantai.',
                'repair_cost' => 85000,
                'fine_amount' => 15000,
                'unit_status' => 'rusak',
                'photos' => [$photo1, $photo2],
            ]);

        $damageCase = DamageCase::latest('id')->first();
        $this->assertNotNull($damageCase);
        $response->assertRedirect(route('admin.damage-cases.show', $damageCase->id));

        $this->assertEquals(85000, (float) $damageCase->repair_cost);
        $this->assertEquals(15000, (float) $damageCase->fine_amount);
        $this->assertEquals(100000, $damageCase->total_amount);
        $this->assertEquals('belum_dibayar', $damageCase->payment_status);
        $this->assertEquals('terbuka', $damageCase->case_status);

        // Pastikan status unit fisik diperbarui menjadi rusak
        $unit2->refresh();
        $this->assertEquals('rusak', $unit2->status);
        $this->assertEquals('rusak', $unit2->condition);

        // Pastikan foto tersimpan di storage
        $this->assertCount(2, $damageCase->damageReport->images_list);
    }

    public function test_admin_can_view_damage_case_detail_page(): void
    {
        $response = $this->actingAs($this->adminA)
            ->get(route('admin.damage-cases.show', $this->damageCaseA->id));

        $response->assertStatus(200);
        $response->assertSee('Tiket Kasus Kerusakan');
        $response->assertSee('#' . $this->damageCaseA->id);
        $response->assertSee('Bugaboo Butterfly Ultra Compact');
        $response->assertSee('BGB-001');
        $response->assertSee('Tuas pelipat stroller patah di sisi kanan.');
        $response->assertSee('Rp 120.000');
        $response->assertSee('Rp 30.000');
        $response->assertSee('Rp 150.000');
    }

    public function test_admin_can_respond_to_damage_case(): void
    {
        $response = $this->actingAs($this->adminA)
            ->post(route('admin.damage-cases.respond', $this->damageCaseA->id), [
                'notes' => 'Toko telah memesan suku cadang pengganti. Penyewa dapat membayar via rekening BCA toko.',
                'case_status' => 'menunggu_respons',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->damageCaseA->refresh();
        $this->assertStringContainsString('Toko telah memesan suku cadang pengganti', $this->damageCaseA->resolution_notes);
        $this->assertEquals('menunggu_respons', $this->damageCaseA->case_status);
    }

    public function test_admin_can_verify_compensation_payment(): void
    {
        $payMethod = ClientPaymentMethod::create([
            'client_id' => $this->clientA->id,
            'bank_name' => 'BCA',
            'account_number' => '12345678',
            'account_holder' => 'Rental Base Baby Care',
            'is_active' => true,
        ]);

        // Simulasikan customer sudah mengunggah payment
        Payment::create([
            'order_id' => $this->orderA->id,
            'client_payment_method_id' => $payMethod->id,
            'amount' => 150000,
            'payment_proof' => 'payments/proof_kompensasi.jpg',
            'status' => 'menunggu_konfirmasi',
            'notes' => 'Pembayaran Kompensasi Kerusakan Tiket #' . $this->damageCaseA->id,
            'paid_at' => now(),
        ]);

        $this->damageCaseA->update([
            'payment_status' => 'menunggu_verifikasi',
            'case_status' => 'kompensasi_dibayar',
        ]);

        $response = $this->actingAs($this->adminA)
            ->post(route('admin.damage-cases.verify-payment', $this->damageCaseA->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->damageCaseA->refresh();
        $this->assertEquals('lunas', $this->damageCaseA->payment_status);
        $this->assertEquals('lunas', $this->damageCaseA->case_status);
        $this->assertStringContainsString('Pembayaran Kompensasi Terverifikasi', $this->damageCaseA->resolution_notes);
    }

    public function test_admin_can_close_damage_case_and_update_unit_status(): void
    {
        $response = $this->actingAs($this->adminA)
            ->post(route('admin.damage-cases.close', $this->damageCaseA->id), [
                'closing_notes' => 'Stroller telah diperbaiki dengan suku cadang original dan lulus uji keamanan.',
                'unit_status_after' => 'tersedia',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->damageCaseA->refresh();
        $this->assertEquals('selesai', $this->damageCaseA->case_status);

        // Unit fisik harus kembali 'tersedia' dan 'baik'
        $this->unitA->refresh();
        $this->assertEquals('tersedia', $this->unitA->status);
        $this->assertEquals('baik', $this->unitA->condition);
    }

    public function test_tenant_isolation_admin_cannot_access_other_store_damage_case(): void
    {
        // Admin B (Toko Sebelah) mencoba membuka kasus Toko A
        $response = $this->actingAs($this->adminB)
            ->get(route('admin.damage-cases.show', $this->damageCaseA->id));

        $response->assertStatus(403);
    }
}
