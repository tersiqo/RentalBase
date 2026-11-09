<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Client;
use App\Models\ConditionCheck;
use App\Models\EquipmentUnit;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemUnit;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * MOD-10 — Pengembalian, Kondisi dan Damage Case
 * Fitur: Condition Checks / Pemeriksaan Kondisi Fisik Alat
 *
 * TINGKAT PENGUJIAN: INTEGRATION TESTING (Pengujian Integrasi Komponen)
 * Runner: Laravel HTTP Testing ($this->get(), $this->post(), $this->actingAs())
 *
 * Menguji:
 * 1. Halaman Index: Akses HTTP 200 dan isolasi data multi-tenant.
 * 2. API AJAX Unit Fisik: Endpoint /admin/condition-checks/units pengembalian JSON unit pesanan.
 * 3. Form Inspeksi: Penyimpanan fase sebelum_serah (single foto) & setelah_kembali (multi-foto JSON).
 * 4. Aturan Validasi: Penolakan input wajib (order_id, equipment_unit_id).
 *
 * Total: 6 Test Case Ringkas (IT-CHK-001 s/d IT-CHK-006)
 */
class AdminConditionCheckTest extends TestCase
{
    use RefreshDatabase;

    private Client $clientA;
    private Client $clientB;
    private User $adminA;
    private User $customer;
    private Product $productA;
    private EquipmentUnit $unitA;
    private EquipmentUnit $unitB;
    private Order $orderA;
    private Order $orderB;

    /**
     * Menyiapkan data awal sebelum setiap test dijalankan.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->travelTo(Carbon::parse('2026-11-10 10:00:00'));

        $this->clientA = Client::create([
            'business_name' => 'Rental Kamera Studio A',
            'subdomain'     => 'kamera-a',
            'status'        => 'aktif',
        ]);

        $this->clientB = Client::create([
            'business_name' => 'Rental Outdoor Camp B',
            'subdomain'     => 'outdoor-b',
            'status'        => 'aktif',
        ]);

        $this->adminA = User::factory()->create([
            'client_id' => $this->clientA->id,
            'name'      => 'Admin Pemeriksa A',
            'role'      => 'admin_rental',
            'status'    => 'aktif',
        ]);

        $this->customer = User::factory()->create([
            'client_id' => $this->clientA->id,
            'name'      => 'Pelanggan Uji',
            'role'      => 'customer',
            'status'    => 'aktif',
        ]);

        $categoryA = Category::create([
            'client_id' => $this->clientA->id,
            'name'      => 'Kamera DSLR',
            'slug'      => 'kamera-dslr',
        ]);

        $this->productA = Product::create([
            'client_id'            => $this->clientA->id,
            'category_id'          => $categoryA->id,
            'name'                 => 'Sony Alpha A7 IV',
            'slug'                 => 'sony-a7-iv',
            'rental_price_per_day' => 250000,
            'late_fee_per_hour'    => 25000,
            'status'               => 'aktif',
        ]);

        $this->unitA = EquipmentUnit::create([
            'client_id'  => $this->clientA->id,
            'product_id' => $this->productA->id,
            'unit_code'  => 'CAM-CHK-01',
            'condition'  => 'baik',
            'status'     => 'siap_disewa',
        ]);

        $this->unitB = EquipmentUnit::create([
            'client_id'  => $this->clientB->id,
            'product_id' => $this->productA->id,
            'unit_code'  => 'OUT-CHK-02',
            'condition'  => 'baik',
            'status'     => 'siap_disewa',
        ]);

        // Order Toko A
        $this->orderA = Order::create([
            'client_id'         => $this->clientA->id,
            'user_id'           => $this->customer->id,
            'order_number'      => 'ORD-CHK-001',
            'rental_start_date' => now(),
            'rental_end_date'   => now()->addDays(2),
            'duration_days'     => 2,
            'total_amount'      => 500000,
            'shipping_address'  => 'Jl. Embong Malang No. 10',
            'shipping_method'   => 'ambil_toko',
            'status'            => 'siap_kirim',
        ]);

        $itemA = OrderItem::create([
            'order_id'      => $this->orderA->id,
            'product_id'    => $this->productA->id,
            'quantity'      => 1,
            'price_per_day' => 250000,
            'subtotal'      => 500000,
        ]);

        OrderItemUnit::create([
            'order_item_id'     => $itemA->id,
            'equipment_unit_id' => $this->unitA->id,
        ]);

        // Order Toko B
        $this->orderB = Order::create([
            'client_id'         => $this->clientB->id,
            'user_id'           => $this->customer->id,
            'order_number'      => 'ORD-CHK-002',
            'rental_start_date' => now(),
            'rental_end_date'   => now()->addDays(2),
            'duration_days'     => 2,
            'total_amount'      => 500000,
            'shipping_address'  => 'Jl. Basuki Rahmat No. 5',
            'shipping_method'   => 'ambil_toko',
            'status'            => 'siap_kirim',
        ]);
    }

    // =====================================================================
    // 6 SKENARIO INTEGRATION TESTING (LARAVEL HTTP)
    // =====================================================================

    /**
     * IT-CHK-001: Admin aktif sukses mengakses halaman rekaman kondisi fisik (HTTP 200).
     */
    public function test_it_chk_001_admin_aktif_sukses_akses_halaman_rekaman_kondisi(): void
    {
        $response = $this->actingAs($this->adminA)->get(route('admin.condition-checks.index'));

        $response->assertOk();
        $response->assertViewIs('admin.condition-checks.index');
        $response->assertViewHas('conditionChecks');
        $response->assertViewHas('orders');
    }

    /**
     * IT-CHK-002: Multi-Tenant pada tabel: Admin Toko A tidak melihat rekaman inspeksi milik Toko B.
     */
    public function test_it_chk_002_multi_tenant_admin_toko_a_tidak_melihat_rekaman_toko_b(): void
    {
        ConditionCheck::create([
            'equipment_unit_id' => $this->unitA->id,
            'check_type'        => 'sebelum_serah',
            'condition'         => 'baik',
            'notes'             => 'CATATAN_INSPEKSI_TOKO_A',
            'checked_by'        => $this->adminA->id,
        ]);

        ConditionCheck::create([
            'equipment_unit_id' => $this->unitB->id,
            'check_type'        => 'sebelum_serah',
            'condition'         => 'baik',
            'notes'             => 'CATATAN_INSPEKSI_TOKO_B',
            'checked_by'        => $this->adminA->id,
        ]);

        $response = $this->actingAs($this->adminA)->get(route('admin.condition-checks.index'));

        $response->assertOk();
        $response->assertSee('CATATAN_INSPEKSI_TOKO_A');
        $response->assertDontSee('CATATAN_INSPEKSI_TOKO_B');
    }

    /**
     * IT-CHK-003: Endpoint AJAX unit fisik mengembalikan respon JSON berisi daftar unit dari pesanan terkait.
     */
    public function test_it_chk_003_endpoint_ajax_unit_fisik_mengembalikan_json_daftar_unit(): void
    {
        $response = $this->actingAs($this->adminA)
            ->get(route('admin.condition-checks.units', ['order_id' => $this->orderA->id]));

        $response->assertOk();
        $response->assertJsonFragment([
            'unit_code'    => 'CAM-CHK-01',
            'product_name' => 'Sony Alpha A7 IV',
            'condition'    => 'baik',
        ]);
    }

    /**
     * IT-CHK-004: Form inspeksi sukses mencatat fase sebelum_serah dengan kondisi baik dan single foto.
     */
    public function test_it_chk_004_form_inspeksi_sukses_catat_sebelum_serah_dengan_single_foto(): void
    {
        Storage::fake('public');
        $fakePhoto = UploadedFile::fake()->create('kondisi_awal.jpg', 300, 'image/jpeg');

        $response = $this->actingAs($this->adminA)->post(route('admin.condition-checks.store'), [
            'order_id'          => $this->orderA->id,
            'equipment_unit_id' => $this->unitA->id,
            'check_type'        => 'sebelum_serah',
            'condition'         => 'baik',
            'notes'             => 'Kamera mulus, lensa jernih tanpa goresan.',
            'image'             => $fakePhoto,
        ]);

        $response->assertRedirect(route('admin.condition-checks.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('condition_checks', [
            'equipment_unit_id' => $this->unitA->id,
            'check_type'        => 'sebelum_serah',
            'condition'         => 'baik',
            'checked_by'        => $this->adminA->id,
        ]);
    }

    /**
     * IT-CHK-005: Form inspeksi sukses mencatat fase setelah_kembali dengan kondisi rusak_ringan dan multi-foto.
     */
    public function test_it_chk_005_form_inspeksi_sukses_catat_setelah_kembali_dengan_multi_foto(): void
    {
        Storage::fake('public');
        $photos = [
            UploadedFile::fake()->create('bodi_samping.jpg', 200, 'image/jpeg'),
            UploadedFile::fake()->create('tutup_baterai.jpg', 200, 'image/jpeg'),
        ];

        $response = $this->actingAs($this->adminA)->post(route('admin.condition-checks.store'), [
            'order_id'          => $this->orderA->id,
            'equipment_unit_id' => $this->unitA->id,
            'check_type'        => 'setelah_kembali',
            'condition'         => 'rusak_ringan',
            'notes'             => 'Terdapat goresan pada bodi samping kanan saat retur.',
            'images'            => $photos,
        ]);

        $response->assertRedirect(route('admin.condition-checks.index'));
        $response->assertSessionHas('success');

        $check = ConditionCheck::where('equipment_unit_id', $this->unitA->id)
            ->where('check_type', 'setelah_kembali')
            ->first();

        $this->assertNotNull($check);
        $this->assertSame('rusak_ringan', $check->condition);
        $this->assertCount(2, $check->images_list);
    }

    /**
     * IT-CHK-006: Validasi menolak proses jika order_id atau equipment_unit_id tidak diisi (Negative Test).
     */
    public function test_it_chk_006_validasi_menolak_jika_order_id_atau_equipment_unit_id_kosong(): void
    {
        $response = $this->actingAs($this->adminA)
            ->from(route('admin.condition-checks.index'))
            ->post(route('admin.condition-checks.store'), [
                'check_type' => 'sebelum_serah',
                'condition'  => 'baik',
            ]);

        $response->assertRedirect(route('admin.condition-checks.index'));
        $response->assertSessionHasErrors(['order_id', 'equipment_unit_id']);
        $this->assertDatabaseCount('condition_checks', 0);
    }
}
