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

    public function test_logout_from_my_orders_redirects_to_customer_catalog(): void
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

        $response = $this->actingAs($user)
            ->from('/my-orders?client=kamerakita')
            ->post('/logout');

        $response->assertRedirect('/client/kamerakita');
        $this->assertGuest();
    }

    public function test_logout_from_catalog_redirects_back_to_catalog(): void
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

        $response = $this->actingAs($user)
            ->from('/client/kamerakita')
            ->post('/logout');

        $response->assertRedirect('/client/kamerakita');
        $this->assertGuest();
    }

    public function test_customer_can_view_order_detail(): void
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
            'order_number' => 'ORD-DETAIL-999',
            'rental_start_date' => now()->addDay(),
            'rental_end_date' => now()->addDays(3),
            'duration_days' => 2,
            'total_amount' => 350000,
            'shipping_address' => 'Jl. Ijen No. 12 Malang',
            'status' => 'menunggu_pembayaran',
        ]);

        $response = $this->actingAs($user)->get('/my-orders/' . $order->id);

        $response->assertStatus(200);
        $response->assertViewIs('customer.orders.show');
        $response->assertSee('Detail Pesanan');
        $response->assertSee('ORD-DETAIL-999');
        $response->assertSee('Rp 350.000');
    }

    public function test_customer_cannot_view_another_customer_order(): void
    {
        $client = Client::create([
            'business_name' => 'Kamera Kita',
            'subdomain' => 'kamerakita',
            'status' => 'aktif',
        ]);

        $user1 = User::factory()->create(['client_id' => $client->id, 'role' => 'customer']);
        $user2 = User::factory()->create(['client_id' => $client->id, 'role' => 'customer']);

        $order = Order::create([
            'client_id' => $client->id,
            'user_id' => $user1->id,
            'order_number' => 'ORD-SECRET-123',
            'rental_start_date' => now()->addDay(),
            'rental_end_date' => now()->addDays(3),
            'duration_days' => 2,
            'total_amount' => 350000,
            'shipping_address' => 'Jl. Rahasia',
            'status' => 'menunggu_pembayaran',
        ]);

        $response = $this->actingAs($user2)->get('/my-orders/' . $order->id);

        $response->assertStatus(404);
    }

    public function test_customer_can_cancel_unpaid_order(): void
    {
        $client = Client::create([
            'business_name' => 'Kamera Kita',
            'subdomain' => 'kamerakita',
            'status' => 'aktif',
        ]);

        $user = User::factory()->create(['client_id' => $client->id, 'role' => 'customer']);

        $order = Order::create([
            'client_id' => $client->id,
            'user_id' => $user->id,
            'order_number' => 'ORD-CANCEL-123',
            'rental_start_date' => now()->addDay(),
            'rental_end_date' => now()->addDays(3),
            'duration_days' => 2,
            'total_amount' => 350000,
            'shipping_address' => 'Jl. Batal',
            'status' => 'menunggu_pembayaran',
        ]);

        $response = $this->actingAs($user)->post('/my-orders/' . $order->id . '/cancel', [
            'reason' => 'Salah pilih tanggal',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('batal', $order->fresh()->status);
        $this->assertEquals('Salah pilih tanggal', $order->fresh()->cancellation_reason);
    }

    public function test_customer_can_confirm_receive_shipped_order(): void
    {
        $client = Client::create([
            'business_name' => 'Kamera Kita',
            'subdomain' => 'kamerakita',
            'status' => 'aktif',
        ]);

        $user = User::factory()->create(['client_id' => $client->id, 'role' => 'customer']);

        $order = Order::create([
            'client_id' => $client->id,
            'user_id' => $user->id,
            'order_number' => 'ORD-RECEIVE-123',
            'rental_start_date' => now()->addDay(),
            'rental_end_date' => now()->addDays(3),
            'duration_days' => 2,
            'total_amount' => 350000,
            'shipping_address' => 'Jl. Penerima',
            'status' => 'dikirim',
        ]);

        $response = $this->actingAs($user)->post('/my-orders/' . $order->id . '/receive');

        $response->assertSessionHas('success');
        $this->assertEquals('sedang_disewa', $order->fresh()->status);
    }
}
