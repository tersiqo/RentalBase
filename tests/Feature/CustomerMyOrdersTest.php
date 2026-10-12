<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerMyOrdersTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_home(): void
    {
        $response = $this->get('/my-orders');

        $response->assertRedirect('/');
    }

    public function test_authenticated_user_can_view_my_orders(): void
    {
        $client = Client::create([
            'business_name' => 'Kamera Kita',
            'subdomain' => 'kamerakita',
            'status' => 'aktif',
        ]);

        $user = User::factory()->create([
            'client_id' => $client->id,
            'role' => 'customer',
        ]);

        $order = Order::create([
            'client_id' => $client->id,
            'user_id' => $user->id,
            'order_number' => 'ORD-TEST-001',
            'rental_start_date' => now()->addDay(),
            'rental_end_date' => now()->addDays(3),
            'duration_days' => 2,
            'total_amount' => 250000,
            'shipping_address' => 'Jl. Test No. 123',
            'status' => 'menunggu_pembayaran',
        ]);

        $response = $this->actingAs($user)->get('/my-orders');

        $response->assertStatus(200);
        $response->assertViewIs('customer.orders.my-orders');
        $response->assertViewHas('orders');
        $response->assertViewHas('client');
        $response->assertSee('Pesanan Saya');
        $response->assertSee('ORD-TEST-001');
        $response->assertSee('Belum Dibayar');
    }

    public function test_user_without_orders_sees_empty_state(): void
    {
        $client = Client::create([
            'business_name' => 'Kamera Kita',
            'subdomain' => 'kamerakita',
            'status' => 'aktif',
        ]);

        $user = User::factory()->create([
            'client_id' => $client->id,
            'role' => 'customer',
        ]);

        $response = $this->actingAs($user)->get('/my-orders');

        $response->assertStatus(200);
        $response->assertSee('Belum Ada Pesanan');
        $response->assertSee('Mulai Sewa Sekarang');
    }

    public function test_order_items_and_products_are_properly_displayed(): void
    {
        $client = Client::create([
            'business_name' => 'Kamera Kita',
            'subdomain' => 'kamerakita',
            'status' => 'aktif',
        ]);

        $category = Category::create([
            'client_id' => $client->id,
            'name' => 'Kamera',
            'slug' => 'kamera',
        ]);

        $user = User::factory()->create([
            'client_id' => $client->id,
            'role' => 'customer',
        ]);

        $product = Product::create([
            'client_id' => $client->id,
            'category_id' => $category->id,
            'name' => 'Sony A7 IV Mirroless',
            'slug' => 'sony-a7-iv',
            'rental_price_per_day' => 250000,
            'status' => 'aktif',
        ]);

        $order = Order::create([
            'client_id' => $client->id,
            'user_id' => $user->id,
            'order_number' => 'ORD-SONY-777',
            'rental_start_date' => now()->addDay(),
            'rental_end_date' => now()->addDays(3),
            'duration_days' => 2,
            'total_amount' => 500000,
            'shipping_address' => 'Jl. Ijen No. 10 Malang',
            'status' => 'sedang_disewa',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price_per_day' => 250000,
            'subtotal' => 500000,
        ]);

        $response = $this->actingAs($user)->get('/my-orders');

        $response->assertStatus(200);
        $response->assertSee('ORD-SONY-777');
        $response->assertSee('Sony A7 IV Mirroless');
        $response->assertSee('Sedang Disewa');
        $response->assertSee('Rp 500.000');
    }
}
