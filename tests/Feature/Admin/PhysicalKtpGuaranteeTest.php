<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Client;
use App\Models\EquipmentUnit;
use App\Models\IdentityGuarantee;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemUnit;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhysicalKtpGuaranteeTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;
    private User $admin;
    private User $customer;
    private Product $product;
    private EquipmentUnit $unit;
    private Order $order;
    private IdentityGuarantee $guarantee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Client::create([
            'business_name' => 'Kamera Malang Rental',
            'subdomain' => 'kamerarental',
            'status' => 'aktif',
        ]);

        $this->admin = User::create([
            'client_id' => $this->client->id,
            'name' => 'Admin Rental',
            'email' => 'admin@kamerarental.com',
            'password' => bcrypt('password'),
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        $this->customer = User::create([
            'name' => 'Pelanggan Setia',
            'email' => 'pelanggan@test.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        $category = Category::create([
            'client_id' => $this->client->id,
            'name' => 'Kamera',
            'slug' => 'kamera',
        ]);

        $this->product = Product::create([
            'client_id' => $this->client->id,
            'category_id' => $category->id,
            'name' => 'Sony A7 IV',
            'slug' => 'sony-a7-iv',
            'rental_price_per_day' => 200000,
            'late_fee_per_hour' => 20000,
            'status' => 'aktif',
        ]);

        $this->unit = EquipmentUnit::create([
            'client_id' => $this->client->id,
            'product_id' => $this->product->id,
            'unit_code' => 'CAM-001',
            'serial_number' => 'SN-123456',
            'condition' => 'baik',
            'status' => 'tersedia',
        ]);

        $this->order = Order::create([
            'client_id' => $this->client->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-TEST-001',
            'total_amount' => 400000,
            'rental_start_date' => Carbon::now()->subDays(2),
            'rental_end_date' => Carbon::now()->addDays(2),
            'duration_days' => 4,
            'shipping_address' => 'Jl. Ijen No. 10 Kota Malang',
            'shipping_method' => 'ambil_toko',
            'status' => 'siap_kirim',
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price_per_day' => 200000,
            'subtotal' => 200000,
        ]);

        OrderItemUnit::create([
            'order_item_id' => $orderItem->id,
            'equipment_unit_id' => $this->unit->id,
        ]);

        $this->guarantee = IdentityGuarantee::create([
            'order_id' => $this->order->id,
            'customer_id' => $this->customer->id,
            'full_name' => 'Pelanggan Setia',
            'identity_number' => '3573012345670001',
            'identity_document_image' => 'identity_guarantees/ktp-sample.jpg',
            'face_image' => 'identity_guarantees/selfie-sample.jpg',
            'identity_address' => 'Jl. Ijen No. 10 Kota Malang',
            'status' => 'diverifikasi',
        ]);
    }

    public function test_identity_guarantee_model_accessors_return_expected_labels_and_colors(): void
    {
        $this->guarantee->update(['status' => 'menunggu']);
        $this->assertEquals('Menunggu Verifikasi', $this->guarantee->status_label);
        $this->assertStringContainsString('amber', $this->guarantee->status_color);

        $this->guarantee->update(['status' => 'diverifikasi']);
        $this->assertEquals('Data Terverifikasi (Wajib Bawa KTP Fisik)', $this->guarantee->status_label);
        $this->assertStringContainsString('blue', $this->guarantee->status_color);

        $this->guarantee->update(['status' => 'dititipkan']);
        $this->assertEquals('KTP Fisik Sedang Dititipkan', $this->guarantee->status_label);
        $this->assertStringContainsString('purple', $this->guarantee->status_color);

        $this->guarantee->update(['status' => 'dikembalikan']);
        $this->assertEquals('KTP Fisik Telah Dikembalikan', $this->guarantee->status_label);
        $this->assertStringContainsString('emerald', $this->guarantee->status_color);

        $this->guarantee->update(['status' => 'ditolak']);
        $this->assertEquals('Verifikasi Ditolak', $this->guarantee->status_label);
        $this->assertStringContainsString('red', $this->guarantee->status_color);
    }

    public function test_shipment_requires_ktp_deposited_checkbox_when_processing_handover(): void
    {
        $this->actingAs($this->admin);

        // 1. Gagal jika tidak mencentang ktp_deposited
        $response = $this->post(route('admin.shipments.process', $this->order->id), [
            // tanpa ktp_deposited
        ]);

        $response->assertSessionHasErrors('ktp_deposited');
        $this->assertEquals('diverifikasi', $this->guarantee->fresh()->status);

        // 2. Berhasil jika mencentang ktp_deposited
        $successResponse = $this->post(route('admin.shipments.process', $this->order->id), [
            'ktp_deposited' => '1',
        ]);

        $successResponse->assertRedirect(route('admin.shipments.index'));
        $successResponse->assertSessionHas('success');

        // Status pesanan menjadi dikirim & jaminan KTP menjadi dititipkan beserta timestamp fisik
        $this->assertEquals('dikirim', $this->order->fresh()->status);
        $this->assertEquals('dititipkan', $this->guarantee->fresh()->status);
        $this->assertNotNull($this->guarantee->fresh()->deposited_at);
    }

    public function test_return_completion_updates_identity_guarantee_status_to_dikembalikan(): void
    {
        $this->actingAs($this->admin);

        // Set status order menjadi dikirim dan KTP dititipkan
        $this->order->update(['status' => 'dikirim']);
        $this->guarantee->update(['status' => 'dititipkan']);

        $response = $this->post("/admin/returns/{$this->order->id}/process", [
            'action_type' => 'normal',
            'late_fee_amount' => 0,
        ]);

        $response->assertRedirect(route('admin.returns.index'));
        $this->assertEquals('selesai', $this->order->fresh()->status);
        $this->assertEquals('dikembalikan', $this->guarantee->fresh()->status);
        $this->assertNotNull($this->guarantee->fresh()->returned_at);
    }

    public function test_return_with_unpaid_late_fee_keeps_identity_guarantee_status_as_dititipkan(): void
    {
        $this->actingAs($this->admin);

        // Set order terlambat sewa
        $this->order->update([
            'status' => 'dikirim',
            'rental_end_date' => Carbon::now()->subHours(5),
        ]);
        $this->guarantee->update(['status' => 'dititipkan']);

        $response = $this->post("/admin/returns/{$this->order->id}/process", [
            'action_type' => 'denda',
            'late_fee_amount' => 100000,
        ]);

        $response->assertRedirect(route('admin.returns.index'));
        $this->assertEquals('menunggu_pembayaran_denda', $this->order->fresh()->status);
        $this->assertEquals('dititipkan', $this->guarantee->fresh()->status);
    }

    public function test_admin_and_customer_order_detail_renders_physical_ktp_status_badges(): void
    {
        // 1. Pengujian saat status KTP dititipkan
        $this->guarantee->update(['status' => 'dititipkan']);
        $this->order->update(['status' => 'sedang_disewa']);

        // Sisi Admin
        $this->actingAs($this->admin);
        $adminView = $this->get(route('admin.orders.show', $this->order->id));
        $adminView->assertStatus(200);
        $adminView->assertSee('KTP Fisik Sedang Dititipkan');
        $adminView->assertSee('KTP Fisik Dititipkan di Toko');

        // Sisi Customer
        $this->actingAs($this->customer);
        $customerView = $this->get(route('customer.orders.show', ['order' => $this->order->id, 'subdomain' => $this->client->subdomain]));
        $customerView->assertStatus(200);
        $customerView->assertSee('KTP Fisik Sedang Dititipkan di Toko');

        // 2. Pengujian saat status KTP dikembalikan
        $this->guarantee->update(['status' => 'dikembalikan']);
        $this->order->update(['status' => 'selesai']);

        // Sisi Admin
        $this->actingAs($this->admin);
        $adminViewCompleted = $this->get(route('admin.orders.show', $this->order->id));
        $adminViewCompleted->assertStatus(200);
        $adminViewCompleted->assertSee('KTP Fisik Telah Dikembalikan');

        // Sisi Customer
        $this->actingAs($this->customer);
        $customerViewCompleted = $this->get(route('customer.orders.show', ['order' => $this->order->id, 'subdomain' => $this->client->subdomain]));
        $customerViewCompleted->assertStatus(200);
        $customerViewCompleted->assertSee('KTP Fisik Telah Dikembalikan');
    }
}
