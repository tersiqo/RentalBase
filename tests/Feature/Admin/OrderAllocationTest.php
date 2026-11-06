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

class OrderAllocationTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;
    private User $admin;
    private Product $product;
    private EquipmentUnit $unit;
    private Order $order;
    private OrderItem $orderItem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Client::create([
            'business_name' => 'Baby Rental',
            'subdomain' => 'babyrental',
            'status' => 'aktif',
        ]);

        $this->admin = User::create([
            'client_id' => $this->client->id,
            'name' => 'Admin Baby',
            'email' => 'admin@baby.com',
            'password' => bcrypt('password'),
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        $category = Category::create([
            'client_id' => $this->client->id,
            'name' => 'Stroller',
            'slug' => 'stroller',
        ]);

        $this->product = Product::create([
            'client_id' => $this->client->id,
            'category_id' => $category->id,
            'name' => 'Stroller Nuna',
            'slug' => 'stroller-nuna',
            'rental_price_per_day' => 100000,
            'status' => 'aktif',
        ]);

        $this->unit = EquipmentUnit::create([
            'client_id' => $this->client->id,
            'product_id' => $this->product->id,
            'unit_code' => 'BBR-NUNA-01',
            'status' => 'tersedia',
        ]);

        $customer = User::create([
            'client_id' => $this->client->id,
            'name' => 'Customer Test',
            'email' => 'customer@test.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $this->order = Order::create([
            'client_id' => $this->client->id,
            'user_id' => $customer->id,
            'order_number' => 'ORD-ALOC-001',
            'rental_start_date' => now()->addDay(),
            'rental_end_date' => now()->addDays(3),
            'duration_days' => 2,
            'total_amount' => 200000,
            'shipping_address' => 'Jl. Mawar',
            'status' => 'menunggu_verifikasi',
        ]);

        $this->orderItem = OrderItem::create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price_per_day' => 100000,
            'subtotal' => 200000,
        ]);
    }

    public function test_admin_can_allocate_unit(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.orders.allocate', $this->order->id), [
            'order_item_id' => $this->orderItem->id,
            'equipment_unit_id' => $this->unit->id,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('order_item_units', [
            'order_item_id' => $this->orderItem->id,
            'equipment_unit_id' => $this->unit->id,
        ]);

        // Karena pesanan untuk besok (advance booking), unit fisik di gudang tetap 'tersedia' untuk hari ini
        $this->assertEquals('tersedia', $this->unit->fresh()->status);
    }

    public function test_admin_can_allocate_unit_for_active_today_order_and_unit_becomes_disewa(): void
    {
        $todayOrder = Order::create([
            'client_id' => $this->client->id,
            'user_id' => $this->order->user_id,
            'order_number' => 'ORD-ALOC-TODAY',
            'rental_start_date' => now(),
            'rental_end_date' => now()->addDay(),
            'duration_days' => 1,
            'total_amount' => 100000,
            'shipping_address' => 'Jl. Mawar No. 1',
            'status' => 'menunggu_verifikasi',
        ]);

        $todayItem = OrderItem::create([
            'order_id' => $todayOrder->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price_per_day' => 100000,
            'subtotal' => 100000,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.orders.allocate', $todayOrder->id), [
            'order_item_id' => $todayItem->id,
            'equipment_unit_id' => $this->unit->id,
        ]);

        $response->assertSessionHas('success');
        // Periode mencakup hari ini, maka status fisik unit berubah jadi 'disewa'
        $this->assertEquals('disewa', $this->unit->fresh()->status);
    }

    public function test_admin_can_allocate_same_unit_to_different_orders_with_non_overlapping_dates(): void
    {
        // Pesanan 1: Tanggal 10 - 11 November 2026
        $order1 = Order::create([
            'client_id' => $this->client->id,
            'user_id' => $this->order->user_id,
            'order_number' => 'ORD-NOV-10-11',
            'rental_start_date' => '2026-11-10 10:00:00',
            'rental_end_date' => '2026-11-11 10:00:00',
            'duration_days' => 2,
            'total_amount' => 200000,
            'shipping_address' => 'Jl. Mawar No. 1',
            'status' => 'menunggu_verifikasi',
        ]);
        $item1 = OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price_per_day' => 100000,
            'subtotal' => 200000,
        ]);

        // Alokasikan unit ke Pesanan 1
        $this->actingAs($this->admin)->post(route('admin.orders.allocate', $order1->id), [
            'order_item_id' => $item1->id,
            'equipment_unit_id' => $this->unit->id,
        ])->assertSessionHas('success');

        // Pesanan 2: Tanggal 8 - 9 November 2026 (sebelum pesanan 1, tidak bentrok)
        $order2 = Order::create([
            'client_id' => $this->client->id,
            'user_id' => $this->order->user_id,
            'order_number' => 'ORD-NOV-08-09',
            'rental_start_date' => '2026-11-08 10:00:00',
            'rental_end_date' => '2026-11-09 10:00:00',
            'duration_days' => 2,
            'total_amount' => 200000,
            'shipping_address' => 'Jl. Mawar No. 2',
            'status' => 'menunggu_verifikasi',
        ]);
        $item2 = OrderItem::create([
            'order_id' => $order2->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price_per_day' => 100000,
            'subtotal' => 200000,
        ]);

        // Alokasikan unit yang sama ke Pesanan 2 -> HARUS BERHASIL
        $this->actingAs($this->admin)->post(route('admin.orders.allocate', $order2->id), [
            'order_item_id' => $item2->id,
            'equipment_unit_id' => $this->unit->id,
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('order_item_units', [
            'order_item_id' => $item1->id,
            'equipment_unit_id' => $this->unit->id,
        ]);
        $this->assertDatabaseHas('order_item_units', [
            'order_item_id' => $item2->id,
            'equipment_unit_id' => $this->unit->id,
        ]);
    }

    public function test_admin_cannot_allocate_same_unit_to_orders_with_overlapping_dates(): void
    {
        // Pesanan 1: Tanggal 10 - 11 November 2026
        $order1 = Order::create([
            'client_id' => $this->client->id,
            'user_id' => $this->order->user_id,
            'order_number' => 'ORD-OVERLAP-1',
            'rental_start_date' => '2026-11-10 10:00:00',
            'rental_end_date' => '2026-11-11 10:00:00',
            'duration_days' => 2,
            'total_amount' => 200000,
            'shipping_address' => 'Jl. Mawar No. 1',
            'status' => 'menunggu_verifikasi',
        ]);
        $item1 = OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price_per_day' => 100000,
            'subtotal' => 200000,
        ]);

        $this->actingAs($this->admin)->post(route('admin.orders.allocate', $order1->id), [
            'order_item_id' => $item1->id,
            'equipment_unit_id' => $this->unit->id,
        ])->assertSessionHas('success');

        // Pesanan 3: Tanggal 9 - 10 November 2026 (bentrok di tanggal 10)
        $order3 = Order::create([
            'client_id' => $this->client->id,
            'user_id' => $this->order->user_id,
            'order_number' => 'ORD-OVERLAP-3',
            'rental_start_date' => '2026-11-09 10:00:00',
            'rental_end_date' => '2026-11-10 10:00:00',
            'duration_days' => 2,
            'total_amount' => 200000,
            'shipping_address' => 'Jl. Mawar No. 3',
            'status' => 'menunggu_verifikasi',
        ]);
        $item3 = OrderItem::create([
            'order_id' => $order3->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price_per_day' => 100000,
            'subtotal' => 200000,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.orders.allocate', $order3->id), [
            'order_item_id' => $item3->id,
            'equipment_unit_id' => $this->unit->id,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('order_item_units', [
            'order_item_id' => $item3->id,
            'equipment_unit_id' => $this->unit->id,
        ]);
    }

    public function test_admin_can_deallocate_unit_and_unit_becomes_available_again(): void
    {
        // First allocate
        $this->unit->update(['status' => 'disewa']);
        $allocation = OrderItemUnit::create([
            'order_item_id' => $this->orderItem->id,
            'equipment_unit_id' => $this->unit->id,
        ]);

        $response = $this->actingAs($this->admin)->delete(
            route('admin.orders.deallocate', [$this->order->id, $allocation->id])
        );

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('order_item_units', [
            'id' => $allocation->id,
        ]);

        $this->assertEquals('tersedia', $this->unit->fresh()->status);
    }

    public function test_cannot_deallocate_when_order_already_shipped(): void
    {
        $this->order->update(['status' => 'dikirim']);
        $this->unit->update(['status' => 'disewa']);
        $allocation = OrderItemUnit::create([
            'order_item_id' => $this->orderItem->id,
            'equipment_unit_id' => $this->unit->id,
        ]);

        $response = $this->actingAs($this->admin)->delete(
            route('admin.orders.deallocate', [$this->order->id, $allocation->id])
        );

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('order_item_units', [
            'id' => $allocation->id,
        ]);
        $this->assertEquals('disewa', $this->unit->fresh()->status);
    }
}
