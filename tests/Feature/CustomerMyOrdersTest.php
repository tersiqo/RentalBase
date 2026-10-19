<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Client;
use App\Models\ClientPaymentMethod;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ReturnModel;
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

    public function test_orders_are_scoped_to_active_client(): void
    {
        $clientA = Client::create([
            'business_name' => 'Toko A',
            'subdomain' => 'tokoa',
            'status' => 'aktif',
        ]);

        $clientB = Client::create([
            'business_name' => 'Toko B',
            'subdomain' => 'tokob',
            'status' => 'aktif',
        ]);

        $user = User::factory()->create([
            'client_id' => $clientA->id,
            'role' => 'customer',
        ]);

        $orderA = Order::create([
            'client_id' => $clientA->id,
            'user_id' => $user->id,
            'order_number' => 'ORD-TOKO-A-001',
            'rental_start_date' => now()->addDay(),
            'rental_end_date' => now()->addDays(3),
            'duration_days' => 2,
            'total_amount' => 200000,
            'shipping_address' => 'Jl. A',
            'status' => 'menunggu_pembayaran',
        ]);

        $orderB = Order::create([
            'client_id' => $clientB->id,
            'user_id' => $user->id,
            'order_number' => 'ORD-TOKO-B-002',
            'rental_start_date' => now()->addDay(),
            'rental_end_date' => now()->addDays(3),
            'duration_days' => 2,
            'total_amount' => 300000,
            'shipping_address' => 'Jl. B',
            'status' => 'menunggu_pembayaran',
        ]);

        // Visit toko A: should see order A but NOT order B
        $responseA = $this->actingAs($user)->get('/my-orders?client=tokoa');
        $responseA->assertStatus(200);
        $responseA->assertSee('ORD-TOKO-A-001');
        $responseA->assertDontSee('ORD-TOKO-B-002');

        // Visit toko B: should see order B but NOT order A
        $responseB = $this->actingAs($user)->get('/my-orders?client=tokob');
        $responseB->assertStatus(200);
        $responseB->assertSee('ORD-TOKO-B-002');
        $responseB->assertDontSee('ORD-TOKO-A-001');
    }

    public function test_customer_return_order_updates_order_status_to_dikembalikan(): void
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
            'order_number' => 'ORD-RETURN-001',
            'rental_start_date' => now()->subDays(3),
            'rental_end_date' => now(),
            'duration_days' => 3,
            'total_amount' => 300000,
            'shipping_address' => 'Jl. Return',
            'status' => 'sedang_disewa',
        ]);

        $response = $this->actingAs($user)->post('/my-orders/' . $order->id . '/return', [
            'return_method' => 'ambil_toko',
            'notes' => 'Barang selesai dipakai dalam kondisi baik.',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('dikembalikan', $order->fresh()->status);
        $this->assertDatabaseHas('returns', [
            'order_id' => $order->id,
            'return_status' => 'diajukan',
        ]);
    }

    public function test_cancel_order_with_payment_sets_refund_status(): void
    {
        $client = Client::create([
            'business_name' => 'Kamera Kita',
            'subdomain' => 'kamerakita',
            'status' => 'aktif',
        ]);

        $payMethod = ClientPaymentMethod::create([
            'client_id' => $client->id,
            'bank_name' => 'BCA',
            'account_number' => '12345678',
            'account_holder' => 'Kamera Kita',
            'is_active' => true,
        ]);

        $user = User::factory()->create(['client_id' => $client->id, 'role' => 'customer']);

        $order = Order::create([
            'client_id' => $client->id,
            'user_id' => $user->id,
            'order_number' => 'ORD-CANCEL-PAY',
            'rental_start_date' => now()->addDay(),
            'rental_end_date' => now()->addDays(3),
            'duration_days' => 2,
            'total_amount' => 200000,
            'shipping_address' => 'Jl. Batal',
            'status' => 'menunggu_pembayaran',
        ]);

        Payment::create([
            'order_id' => $order->id,
            'client_payment_method_id' => $payMethod->id,
            'amount' => 200000,
            'payment_proof' => 'payments/sample.jpg',
            'status' => 'pending',
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($user)->post('/my-orders/' . $order->id . '/cancel', [
            'reason' => 'Perubahan jadwal',
        ]);

        $response->assertSessionHas('success');
        $order = $order->fresh();
        $this->assertEquals('batal', $order->status);
        $this->assertEquals('menunggu_refund', $order->refund_status);
    }

    public function test_processed_order_shows_verified_payment_card_and_no_warning(): void
    {
        $client = Client::create([
            'business_name' => 'Kamera Kita',
            'subdomain' => 'kamerakita',
            'status' => 'aktif',
        ]);

        $payMethod = ClientPaymentMethod::create([
            'client_id' => $client->id,
            'bank_name' => 'BCA',
            'account_number' => '12345678',
            'account_holder' => 'Kamera Kita',
            'is_active' => true,
        ]);

        $user = User::factory()->create(['client_id' => $client->id, 'role' => 'customer']);

        $order = Order::create([
            'client_id' => $client->id,
            'user_id' => $user->id,
            'order_number' => 'ORD-PROCESSED-001',
            'rental_start_date' => now()->addDay(),
            'rental_end_date' => now()->addDays(3),
            'duration_days' => 2,
            'total_amount' => 250000,
            'shipping_address' => 'Jl. Diproses',
            'status' => 'diproses',
        ]);

        Payment::create([
            'order_id' => $order->id,
            'client_payment_method_id' => $payMethod->id,
            'amount' => 250000,
            'payment_proof' => 'payments/sample.jpg',
            'status' => 'dikonfirmasi',
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/my-orders/' . $order->id);

        $response->assertStatus(200);
        $response->assertSee('Pembayaran Terverifikasi');
        $response->assertDontSee('Pembayaran Belum Dibuka');
    }
}
