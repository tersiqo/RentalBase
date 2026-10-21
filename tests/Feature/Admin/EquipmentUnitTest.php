<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Client;
use App\Models\EquipmentUnit;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemUnit;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EquipmentUnitTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;

    private User $admin;

    private Product $product;

    private Client $otherClient;

    private User $otherAdmin;

    private Product $otherProduct;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Tenant A
        $this->client = Client::create([
            'business_name' => 'BabyRent Surabaya',
            'subdomain' => 'babyrent-sby',
            'status' => 'aktif',
        ]);

        $this->admin = User::create([
            'client_id' => $this->client->id,
            'name' => 'Admin Surabaya',
            'email' => 'admin@babyrentsby.com',
            'password' => bcrypt('password'),
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        $this->client->update(['primary_admin_id' => $this->admin->id]);
        $this->makeSubscription($this->client, 'starter');

        $category = Category::create([
            'client_id' => $this->client->id,
            'name' => 'Stroller',
            'slug' => 'stroller',
        ]);

        $this->product = Product::create([
            'client_id' => $this->client->id,
            'category_id' => $category->id,
            'name' => 'Stroller Yoyo Plus',
            'slug' => 'stroller-yoyo-plus',
            'rental_price_per_day' => 120000,
            'late_fee_per_hour' => 10000,
            'status' => 'aktif',
        ]);

        // 2. Setup Tenant B (Multi-tenant check)
        $this->otherClient = Client::create([
            'business_name' => 'Outdoor Malang',
            'subdomain' => 'outdoor-mlg',
            'status' => 'aktif',
        ]);

        $this->otherAdmin = User::create([
            'client_id' => $this->otherClient->id,
            'name' => 'Admin Malang',
            'email' => 'admin@outdoormlg.com',
            'password' => bcrypt('password'),
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        $this->otherClient->update(['primary_admin_id' => $this->otherAdmin->id]);
        $this->makeSubscription($this->otherClient, 'starter');

        $otherCat = Category::create([
            'client_id' => $this->otherClient->id,
            'name' => 'Tenda',
            'slug' => 'tenda',
        ]);

        $this->otherProduct = Product::create([
            'client_id' => $this->otherClient->id,
            'category_id' => $otherCat->id,
            'name' => 'Tenda Dome 4P',
            'slug' => 'tenda-dome-4p',
            'rental_price_per_day' => 80000,
            'late_fee_per_hour' => 8000,
            'status' => 'aktif',
        ]);
    }

    private function makeSubscription(Client $client, string $plan): Subscription
    {
        return Subscription::create([
            'client_id' => $client->id,
            'plan_name' => $plan,
            'price' => 0,
            'billing_cycle' => 'monthly',
            'start_date' => now()->subDay(),
            'end_date' => now()->addMonths(3),
            'status' => 'aktif',
            'payment_status' => 'diverifikasi',
        ]);
    }

    public function test_admin_can_view_units_index(): void
    {
        $unit = EquipmentUnit::create([
            'client_id' => $this->client->id,
            'product_id' => $this->product->id,
            'unit_code' => 'YOYO-001',
            'serial_number' => 'SN-12345',
            'condition' => 'baik',
            'status' => 'tersedia',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.units.index'));

        $response->assertStatus(200);
        $response->assertSee('Unit Fisik Perlengkapan');
        $response->assertSee('YOYO-001');
        $response->assertSee('SN-12345');
        $response->assertSee('Kapasitas Unit Paket');
    }

    public function test_admin_can_filter_units_by_search_code_or_serial(): void
    {
        EquipmentUnit::create([
            'client_id' => $this->client->id,
            'product_id' => $this->product->id,
            'unit_code' => 'YOYO-TARGET',
            'serial_number' => 'SN-TARGET-99',
            'condition' => 'baik',
            'status' => 'tersedia',
        ]);

        EquipmentUnit::create([
            'client_id' => $this->client->id,
            'product_id' => $this->product->id,
            'unit_code' => 'YOYO-OTHER',
            'serial_number' => 'SN-OTHER-11',
            'condition' => 'baik',
            'status' => 'tersedia',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.units.index', ['search' => 'TARGET']));

        $response->assertStatus(200);
        $response->assertSee('YOYO-TARGET');
        $response->assertDontSee('YOYO-OTHER');
    }

    public function test_admin_can_filter_units_by_product_status_and_condition(): void
    {
        EquipmentUnit::create([
            'client_id' => $this->client->id,
            'product_id' => $this->product->id,
            'unit_code' => 'YOYO-MAINT',
            'condition' => 'rusak_ringan',
            'status' => 'maintenance',
        ]);

        EquipmentUnit::create([
            'client_id' => $this->client->id,
            'product_id' => $this->product->id,
            'unit_code' => 'YOYO-GOOD',
            'condition' => 'baik',
            'status' => 'tersedia',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.units.index', [
            'status' => 'maintenance',
            'condition' => 'rusak_ringan',
        ]));

        $response->assertStatus(200);
        $response->assertSee('YOYO-MAINT');
        $response->assertDontSee('YOYO-GOOD');
    }

    public function test_admin_can_store_new_unit_within_quota(): void
    {
        $payload = [
            'product_id' => $this->product->id,
            'unit_code' => 'yoyo-new-01',
            'serial_number' => 'SN-NEW-9988',
            'condition' => 'baik',
            'status' => 'tersedia',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.units.store'), $payload);

        $response->assertRedirect(route('admin.units.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('equipment_units', [
            'client_id' => $this->client->id,
            'product_id' => $this->product->id,
            'unit_code' => 'YOYO-NEW-01', // Otomatis uppercase
            'serial_number' => 'SN-NEW-9988',
            'condition' => 'baik',
            'status' => 'tersedia',
        ]);
    }

    public function test_admin_cannot_store_duplicate_unit_code(): void
    {
        EquipmentUnit::create([
            'client_id' => $this->client->id,
            'product_id' => $this->product->id,
            'unit_code' => 'YOYO-DUPLICATE',
            'condition' => 'baik',
            'status' => 'tersedia',
        ]);

        $payload = [
            'product_id' => $this->product->id,
            'unit_code' => 'YOYO-DUPLICATE',
            'condition' => 'baik',
            'status' => 'tersedia',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.units.store'), $payload);

        $response->assertSessionHasErrors('unit_code');
    }

    public function test_admin_cannot_assign_unit_to_other_tenant_product(): void
    {
        $payload = [
            'product_id' => $this->otherProduct->id, // Produk milik tenant lain
            'unit_code' => 'HACK-001',
            'condition' => 'baik',
            'status' => 'tersedia',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.units.store'), $payload);

        $response->assertSessionHasErrors('product_id');
        $this->assertDatabaseMissing('equipment_units', [
            'unit_code' => 'HACK-001',
        ]);
    }

    public function test_admin_cannot_store_unit_exceeding_plan_quota(): void
    {
        // Paket Starter: maks 50 unit. Buat 50 unit dummy
        for ($i = 1; $i <= 50; $i++) {
            EquipmentUnit::create([
                'client_id' => $this->client->id,
                'product_id' => $this->product->id,
                'unit_code' => "QUOTA-UNIT-{$i}",
                'condition' => 'baik',
                'status' => 'tersedia',
            ]);
        }

        $payload = [
            'product_id' => $this->product->id,
            'unit_code' => 'UNIT-KE-51',
            'condition' => 'baik',
            'status' => 'tersedia',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.units.store'), $payload);

        $response->assertSessionHasErrors('unit_code');
        $this->assertDatabaseMissing('equipment_units', [
            'unit_code' => 'UNIT-KE-51',
        ]);
    }

    public function test_admin_can_update_unit(): void
    {
        $unit = EquipmentUnit::create([
            'client_id' => $this->client->id,
            'product_id' => $this->product->id,
            'unit_code' => 'YOYO-UPDATE-OLD',
            'serial_number' => 'SN-OLD',
            'condition' => 'baik',
            'status' => 'tersedia',
        ]);

        $payload = [
            'product_id' => $this->product->id,
            'unit_code' => 'YOYO-UPDATE-NEW',
            'serial_number' => 'SN-UPDATED',
            'condition' => 'rusak_ringan',
            'status' => 'maintenance',
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.units.update', $unit), $payload);

        $response->assertRedirect(route('admin.units.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('equipment_units', [
            'id' => $unit->id,
            'unit_code' => 'YOYO-UPDATE-NEW',
            'serial_number' => 'SN-UPDATED',
            'condition' => 'rusak_ringan',
            'status' => 'maintenance',
        ]);
    }

    public function test_admin_cannot_update_or_delete_other_tenant_unit(): void
    {
        $otherUnit = EquipmentUnit::create([
            'client_id' => $this->otherClient->id,
            'product_id' => $this->otherProduct->id,
            'unit_code' => 'OTHER-TENANT-UNIT',
            'condition' => 'baik',
            'status' => 'tersedia',
        ]);

        // Coba Update
        $responseUpdate = $this->actingAs($this->admin)->put(route('admin.units.update', $otherUnit), [
            'product_id' => $this->product->id,
            'unit_code' => 'HACKED-CODE',
            'condition' => 'baik',
            'status' => 'tersedia',
        ]);
        $responseUpdate->assertStatus(403);

        // Coba Delete
        $responseDelete = $this->actingAs($this->admin)->delete(route('admin.units.destroy', $otherUnit));
        $responseDelete->assertStatus(403);

        $this->assertDatabaseHas('equipment_units', [
            'id' => $otherUnit->id,
            'unit_code' => 'OTHER-TENANT-UNIT',
        ]);
    }

    public function test_actively_rented_unit_cannot_be_deleted(): void
    {
        $unit = EquipmentUnit::create([
            'client_id' => $this->client->id,
            'product_id' => $this->product->id,
            'unit_code' => 'YOYO-RENTED',
            'condition' => 'baik',
            'status' => 'disewa',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.units.destroy', $unit));

        $response->assertRedirect(route('admin.units.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('equipment_units', [
            'id' => $unit->id,
            'unit_code' => 'YOYO-RENTED',
        ]);
    }

    public function test_unit_with_active_order_cannot_be_deleted(): void
    {
        $unit = EquipmentUnit::create([
            'client_id' => $this->client->id,
            'product_id' => $this->product->id,
            'unit_code' => 'YOYO-WITH-ORDER',
            'condition' => 'baik',
            'status' => 'tersedia',
        ]);

        $customer = User::create([
            'name' => 'Budi Customer',
            'email' => 'budi.customer@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        $order = Order::create([
            'client_id' => $this->client->id,
            'user_id' => $customer->id,
            'order_number' => 'ORD-TEST-001',
            'status' => 'sedang_disewa',
            'total_amount' => 240000,
            'duration_days' => 2,
            'shipping_address' => 'Jl. Test No. 123, Surabaya',
            'rental_start_date' => now(),
            'rental_end_date' => now()->addDays(2),
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price_per_day' => 120000,
            'subtotal' => 240000,
        ]);

        OrderItemUnit::create([
            'order_item_id' => $orderItem->id,
            'equipment_unit_id' => $unit->id,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.units.destroy', $unit));

        $response->assertRedirect(route('admin.units.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('equipment_units', [
            'id' => $unit->id,
        ]);
    }

    public function test_actively_rented_unit_cannot_be_set_to_tersedia(): void
    {
        $unit = EquipmentUnit::create([
            'client_id' => $this->client->id,
            'product_id' => $this->product->id,
            'unit_code' => 'YOYO-DIS-TO-AVAIL',
            'condition' => 'baik',
            'status' => 'disewa',
        ]);

        $payload = [
            'product_id' => $this->product->id,
            'unit_code' => 'YOYO-DIS-TO-AVAIL',
            'condition' => 'baik',
            'status' => 'tersedia', // Coba ubah paksa dari disewa ke tersedia
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.units.update', $unit), $payload);

        $response->assertSessionHasErrors('status');
        $this->assertDatabaseHas('equipment_units', [
            'id' => $unit->id,
            'status' => 'disewa',
        ]);
    }

    public function test_admin_can_delete_available_unit(): void
    {
        $unit = EquipmentUnit::create([
            'client_id' => $this->client->id,
            'product_id' => $this->product->id,
            'unit_code' => 'YOYO-DELETABLE',
            'condition' => 'baik',
            'status' => 'tersedia',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.units.destroy', $unit));

        $response->assertRedirect(route('admin.units.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('equipment_units', [
            'id' => $unit->id,
        ]);
    }
}
