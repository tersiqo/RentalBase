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

        $this->assertEquals('disewa', $this->unit->fresh()->status);
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
