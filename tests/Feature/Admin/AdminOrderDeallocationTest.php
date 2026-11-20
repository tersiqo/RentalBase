<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Client;
use App\Models\EquipmentUnit;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemUnit;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * IT-MOD-07 — Manajemen Pesanan & Alokasi Unit Admin
 * 
 * Pengujian Integrasi:
 * Admin Melepas Alokasi Unit Fisik / Deallocate (DELETE /admin/orders/{order}/allocate/{orderItemUnit})
 */
class AdminOrderDeallocationTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;
    private User $admin;
    private User $customer;
    private Product $product;
    private EquipmentUnit $unit;
    private Order $order;
    private OrderItem $orderItem;
    private OrderItemUnit $orderItemUnit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Client::create([
            'business_name' => 'Baby Rental Studio',
            'subdomain' => 'babyrental',
            'status' => 'aktif',
        ]);

        $this->admin = User::create([
            'client_id' => $this->client->id,
            'name' => 'Admin Budi',
            'email' => 'admin.budi@babyrental.com',
            'password' => bcrypt('password123'),
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        $this->customer = User::create([
            'name' => 'Customer Siti',
            'email' => 'siti@customer.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        $category = Category::create([
            'client_id' => $this->client->id,
            'name' => 'Stroller & Poussette',
            'slug' => 'stroller-poussette',
        ]);

        $this->product = Product::create([
            'client_id' => $this->client->id,
            'category_id' => $category->id,
            'name' => 'Stroller Nuna TRVL',
            'slug' => 'stroller-nuna-trvl',
            'rental_price_per_day' => 85000,
            'status' => 'aktif',
        ]);

        $this->unit = EquipmentUnit::create([
            'client_id' => $this->client->id,
            'product_id' => $this->product->id,
            'unit_code' => 'NUNA-001',
            'status' => 'disewa',
            'condition' => 'baik',
        ]);

        $this->order = Order::create([
            'client_id' => $this->client->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-DEALLOC-001',
            'status' => 'verifikasi',
            'rental_start_date' => now()->addDays(1)->setTime(10, 0, 0),
            'rental_end_date' => now()->addDays(3)->setTime(10, 0, 0),
            'duration_days' => 2,
            'total_amount' => 170000,
            'shipping_address' => 'Jl. Soekarno Hatta No. 9, Malang',
            'shipping_method' => 'kurir',
        ]);

        $this->orderItem = OrderItem::create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price_per_day' => 85000,
            'subtotal' => 170000,
        ]);

        $this->orderItemUnit = OrderItemUnit::create([
            'order_item_id' => $this->orderItem->id,
            'equipment_unit_id' => $this->unit->id,
        ]);
    }

    /**
     * IT-07-016: Memastikan admin toko dapat berhasil melepas / membatalkan alokasi unit fisik dari pesanan.
     */
    public function test_it_07_016_admin_can_successfully_deallocate_assigned_unit(): void
    {
        $response = $this->actingAs($this->admin)
            ->from(route('admin.orders.show', $this->order))
            ->delete(route('admin.orders.deallocate', [$this->order, $this->orderItemUnit]));

        $response->assertRedirect(route('admin.orders.show', $this->order));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('order_item_units', [
            'id' => $this->orderItemUnit->id,
        ]);

        $this->unit->refresh();
        $this->assertEquals('tersedia', $this->unit->status);
    }

    /**
     * IT-07-017: Memastikan admin tidak dapat melepas alokasi unit jika status pesanan sudah dikirim atau sedang disewa.
     */
    public function test_it_07_017_cannot_deallocate_when_order_already_shipped_or_active(): void
    {
        $this->order->update(['status' => 'dikirim']);

        $response = $this->actingAs($this->admin)
            ->from(route('admin.orders.show', $this->order))
            ->delete(route('admin.orders.deallocate', [$this->order, $this->orderItemUnit]));

        $response->assertRedirect(route('admin.orders.show', $this->order));
        $response->assertSessionHas('error', 'Alokasi unit tidak dapat dibatalkan pada status pesanan saat ini.');

        $this->assertDatabaseHas('order_item_units', [
            'id' => $this->orderItemUnit->id,
        ]);
    }

    /**
     * IT-07-018: Memastikan admin dari toko (tenant) lain tidak dapat membatalkan alokasi pesanan toko ini (Multi-Tenant Isolation).
     */
    public function test_it_07_018_admin_from_different_tenant_cannot_deallocate_unit(): void
    {
        $otherClient = Client::create([
            'business_name' => 'EduPlay Rental',
            'subdomain' => 'eduplay',
            'status' => 'aktif',
        ]);

        $otherAdmin = User::create([
            'client_id' => $otherClient->id,
            'name' => 'Admin Eduplay',
            'email' => 'admin@eduplay.id',
            'password' => bcrypt('password123'),
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($otherAdmin)
            ->delete(route('admin.orders.deallocate', [$this->order, $this->orderItemUnit]));

        $response->assertStatus(403);

        $this->assertDatabaseHas('order_item_units', [
            'id' => $this->orderItemUnit->id,
        ]);
    }

    /**
     * IT-07-019: Memastikan pengguna tanpa otentikasi (guest) tidak dapat mengakses endpoint dealokasi unit.
     */
    public function test_it_07_019_unauthenticated_user_cannot_deallocate_unit(): void
    {
        $response = $this->delete(route('admin.orders.deallocate', [$this->order, $this->orderItemUnit]));

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('order_item_units', [
            'id' => $this->orderItemUnit->id,
        ]);
    }
}
