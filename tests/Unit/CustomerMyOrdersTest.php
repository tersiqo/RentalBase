<?php

namespace Tests\Unit;

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

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function createCustomerOrderFixture(array $orderAttributes = []): array
    {
        $client = Client::create([
            'business_name' => 'Rental Maju Jaya',
            'subdomain' => 'rentalmaju',
            'status' => 'aktif',
        ]);

        $customer = User::factory()->create([
            'role' => 'customer',
            'client_id' => $client->id,
            'name' => 'Budi Santoso',
            'email' => 'budi_' . uniqid() . '@example.com',
        ]);

        $category = Category::create([
            'client_id' => $client->id,
            'name' => 'Kamera & Lensa',
            'slug' => 'kamera-lensa-' . uniqid(),
        ]);

        $product = Product::create([
            'client_id' => $client->id,
            'category_id' => $category->id,
            'name' => 'Sony Alpha A7 IV',
            'slug' => 'sony-alpha-a7-iv-' . uniqid(),
            'rental_price_per_day' => 250000,
            'status' => 'aktif',
        ]);

        $order = Order::create(array_merge([
            'client_id' => $client->id,
            'user_id' => $customer->id,
            'order_number' => 'ORD-20261015-001',
            'rental_start_date' => now()->addDay(),
            'rental_end_date' => now()->addDays(3),
            'duration_days' => 2,
            'total_amount' => 500000,
            'shipping_method' => 'kurir',
            'shipping_address' => 'Jl. Soekarno Hatta No. 45, Malang',
            'status' => 'menunggu_pembayaran',
        ], $orderAttributes));

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price_per_day' => 250000,
            'subtotal' => 500000,
        ]);

        return compact('client', 'customer', 'product', 'order');
    }

    public function test_ut_09_001_customer_my_orders_page_and_status_filtering(): void
    {
        $response = $this->get('/my-orders');

        $response->assertRedirect();
        $this->assertGuest();
    }

    public function test_customer_can_view_my_orders_page_with_active_tenant_context(): void
    {
        $fixture = $this->createCustomerOrderFixture();

        $response = $this->actingAs($fixture['customer'])
            ->withSession(['active_client_id' => $fixture['client']->id, 'active_client_subdomain' => $fixture['client']->subdomain])
            ->get('/my-orders');

        $response->assertStatus(200);
        $response->assertViewIs('customer.orders.my-orders');
        $response->assertViewHas('orders');
    }

    public function test_my_orders_page_displays_all_eight_order_status_filter_tabs(): void
    {
        $fixture = $this->createCustomerOrderFixture();

        $response = $this->actingAs($fixture['customer'])
            ->withSession(['active_client_id' => $fixture['client']->id, 'active_client_subdomain' => $fixture['client']->subdomain])
            ->get('/my-orders');

        $response->assertSee('Semua');
        $response->assertSee('Belum Dibayar');
        $response->assertSee('Menunggu Verifikasi');
        $response->assertSee('Diproses');
        $response->assertSee('Dikirim');
        $response->assertSee('Sedang Disewa');
        $response->assertSee('Selesai');
        $response->assertSee('Batal');
    }

    public function test_my_orders_card_displays_complete_order_summary_and_schedule(): void
    {
        $fixture = $this->createCustomerOrderFixture();

        $response = $this->actingAs($fixture['customer'])
            ->withSession(['active_client_id' => $fixture['client']->id, 'active_client_subdomain' => $fixture['client']->subdomain])
            ->get('/my-orders');

        $response->assertSee('ORD-20261015-001');
        $response->assertSee('Sony Alpha A7 IV');
        $response->assertSee('2 hari sewa');
        $response->assertSee('Pengiriman Kurir');
        $response->assertSee('Rp 500.000');
        $response->assertSee('Belum Dibayar');
    }

    public function test_my_orders_page_provides_action_buttons_and_cancellation_modal(): void
    {
        $fixture = $this->createCustomerOrderFixture();

        $response = $this->actingAs($fixture['customer'])
            ->withSession(['active_client_id' => $fixture['client']->id, 'active_client_subdomain' => $fixture['client']->subdomain])
            ->get('/my-orders');

        $response->assertSee('Detail Pesanan');
        $response->assertSee('Batalkan Pesanan');
        $response->assertSee('Batalkan Pesanan?');
        $response->assertSee('Ya, Batalkan');
    }

    public function test_my_orders_tab_filter_bar_supports_responsive_horizontal_layout(): void
    {
        $fixture = $this->createCustomerOrderFixture();

        $response = $this->actingAs($fixture['customer'])
            ->withSession(['active_client_id' => $fixture['client']->id, 'active_client_subdomain' => $fixture['client']->subdomain])
            ->get('/my-orders');

        $response->assertSee('overflow-x-auto');
    }
}
