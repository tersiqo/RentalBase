<?php

namespace Tests\Feature\Customer;

use App\Models\Category;
use App\Models\Client;
use App\Models\EquipmentUnit;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use App\Livewire\Customer\AddToCartModal;
use App\Models\OrderItem;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Client::create([
            'business_name' => 'BabyRent Malang',
            'subdomain' => 'babyrent',
            'status' => 'aktif',
        ]);

        $this->user = User::create([
            'client_id' => $this->client->id,
            'name' => 'Rental Customer',
            'email' => 'customer@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'status' => 'aktif',
        ]);
    }

    private function makeProduct(string $name, int $price): Product
    {
        $category = Category::forceCreate([
            'client_id' => $this->client->id,
            'name' => 'Perlengkapan Bayi',
            'slug' => 'perlengkapan-bayi',
        ]);

        $product = Product::create([
            'client_id' => $this->client->id,
            'category_id' => $category->id,
            'name' => $name,
            'slug' => str()->slug($name),
            'rental_price_per_day' => $price,
            'deposit_fee' => 0,
            'status' => 'aktif',
        ]);

        EquipmentUnit::create([
            'client_id' => $this->client->id,
            'product_id' => $product->id,
            'unit_code' => 'UNIT-'.$product->id.'-1',
            'status' => 'tersedia',
        ]);

        EquipmentUnit::create([
            'client_id' => $this->client->id,
            'product_id' => $product->id,
            'unit_code' => 'UNIT-'.$product->id.'-2',
            'status' => 'tersedia',
        ]);

        return $product;
    }

    private function seedCart(array $products, string $start, string $end): array
    {
        $cart = [];
        $hashes = [];

        foreach ($products as $index => $product) {
            $hash = md5($product->id.$start.$end);
            $cart[$hash] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->rental_price_per_day,
                'deposit_fee' => 0,
                'main_image' => null,
                'start_date' => $start,
                'end_date' => $end,
                'duration_days' => 2,
                'quantity' => $index === 0 ? 2 : 1,
                'subtotal' => $product->rental_price_per_day * 2 * ($index === 0 ? 2 : 1),
            ];
            $hashes[] = $hash;
        }

        session()->put('cart', $cart);

        return $hashes;
    }

    public function test_checkout_requires_authentication(): void
    {
        $product = $this->makeProduct('Box Bayi', 35000);
        $this->seedCart([$product], now()->format('Y-m-d'), now()->addDay()->format('Y-m-d'));

        // Project-wide guest redirect targets "/" (see bootstrap/app.php) while
        // still storing the intended checkout URL for after login.
        $this->get(route('customer.checkout', ['subdomain' => $this->client->subdomain]))
            ->assertRedirect('/');
    }

    public function test_create_shows_selected_cart_items(): void
    {
        $productA = $this->makeProduct('Box Bayi', 35000);
        $productB = $this->makeProduct('Stroller Lipat', 40000);
        $hashes = $this->seedCart([$productA, $productB], now()->format('Y-m-d'), now()->addDay()->format('Y-m-d'));

        $response = $this->actingAs($this->user)
            ->get(route('customer.checkout', ['subdomain' => $this->client->subdomain]));

        $response->assertOk()
            ->assertViewIs('customer.checkout')
            ->assertViewHas('items');

        $this->assertCount(2, $response->viewData('items'));
        $this->assertSame($hashes, session('checkout_items'));
    }

    public function test_create_rejects_items_with_different_periods(): void
    {
        $productA = $this->makeProduct('Box Bayi', 35000);
        $cart = session()->get('cart', []);

        $start1 = now()->format('Y-m-d');
        $end1 = now()->addDay()->format('Y-m-d');
        $start2 = now()->addDays(5)->format('Y-m-d');
        $end2 = now()->addDays(6)->format('Y-m-d');

        $cart[md5($productA->id.$start1.$end1)] = [
            'product_id' => $productA->id,
            'name' => $productA->name,
            'price' => $productA->rental_price_per_day,
            'deposit_fee' => 0,
            'main_image' => null,
            'start_date' => $start1,
            'end_date' => $end1,
            'duration_days' => 2,
            'quantity' => 1,
            'subtotal' => $productA->rental_price_per_day * 2,
        ];
        $cart[md5($productA->id.$start2.$end2)] = [
            'product_id' => $productA->id,
            'name' => $productA->name,
            'price' => $productA->rental_price_per_day,
            'deposit_fee' => 0,
            'main_image' => null,
            'start_date' => $start2,
            'end_date' => $end2,
            'duration_days' => 2,
            'quantity' => 1,
            'subtotal' => $productA->rental_price_per_day * 2,
        ];

        session()->put('cart', $cart);

        $this->withoutExceptionHandling();
        $this->actingAs($this->user)
            ->get(route('customer.checkout', ['subdomain' => $this->client->subdomain]))
            ->assertSessionHasErrors('period');
    }

    public function test_store_creates_order_with_matching_item_count(): void
    {
        $productA = $this->makeProduct('Box Bayi', 35000);
        $productB = $this->makeProduct('Stroller Lipat', 40000);
        $hashes = $this->seedCart([$productA, $productB], now()->format('Y-m-d'), now()->addDay()->format('Y-m-d'));

        $identityNumber = '1234567890123456';

        $response = $this->actingAs($this->user)->post(route('customer.checkout.store', [
            'subdomain' => $this->client->subdomain,
        ]), [
            'selected_hashes' => $hashes,
            'items' => [
                $hashes[0] => ['quantity' => 2],
                $hashes[1] => ['quantity' => 1],
            ],
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDay()->format('Y-m-d'),
            'shipping_address' => 'Jl. Merdeka No. 10, Klojen, Kota Malang, Jawa Timur 65111',
            'shipping_method' => Order::SHIPPING_KURIR,
            'full_name' => $this->user->name,
            'identity_number' => $identityNumber,
            'identity_address' => 'Jl. Merdeka No. 10, Klojen, Kota Malang, Jawa Timur 65111',
            'identity_document_image' => UploadedFile::fake()->image('ktp.jpg'),
            'face_image' => UploadedFile::fake()->image('selfie.jpg'),
            'terms' => '1',
        ]);

        $response->assertRedirect(route('customer.orders'));

        $order = Order::where('user_id', $this->user->id)->first();

        $this->assertNotNull($order);
        $this->assertSame('menunggu_konfirmasi', $order->status);
        $this->assertSame(Order::SHIPPING_KURIR, $order->shipping_method);
        $this->assertFalse($order->isPickup());
        $this->assertSame(2, $order->duration_days);
        $this->assertCount(2, $order->orderItems);
        $this->assertSame(2, $order->orderItems->first(fn ($item) => $item->product_id === $productA->id)->quantity);
        $this->assertSame(1, $order->orderItems->first(fn ($item) => $item->product_id === $productB->id)->quantity);
        $this->assertSame(35000.00 * 2 * 2 + 40000.00 * 2 * 1, (float) $order->total_amount);

        $this->assertDatabaseHas('identity_guarantees', [
            'order_id' => $order->id,
            'identity_number' => $identityNumber,
            'status' => 'menunggu',
        ]);

        $this->assertSame([], session('cart'));
        $this->assertNull(session('checkout_items'));
    }

    public function test_direct_seeds_product_into_cart_then_checkout(): void
    {
        $product = $this->makeProduct('Box Bayi', 35000);

        $this->actingAs($this->user)
            ->get(route('customer.checkout.direct', ['subdomain' => $this->client->subdomain, 'product' => $product->id]))
            ->assertRedirect(route('customer.checkout', ['subdomain' => $this->client->subdomain]));

        $cart = session('cart');
        $this->assertCount(1, $cart);

        $item = reset($cart);
        $this->assertSame($product->id, $item['product_id']);
        $this->assertSame(1, $item['quantity']);
    }

    public function test_store_resolves_cart_fallback_when_selected_hashes_missing_or_empty(): void
    {
        $product = $this->makeProduct('Box Bayi', 35000);
        $this->seedCart([$product], now()->format('Y-m-d'), now()->addDay()->format('Y-m-d'));

        $identityNumber = '1234567890123456';

        $response = $this->actingAs($this->user)->post(route('customer.checkout.store', [
            'subdomain' => $this->client->subdomain,
        ]), [
            'selected_hashes' => [''],
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDay()->format('Y-m-d'),
            'shipping_address' => 'Jl. Merdeka No. 10, Klojen, Kota Malang, Jawa Timur 65111',
            'shipping_method' => Order::SHIPPING_AMBIL_TOKO,
            'full_name' => $this->user->name,
            'identity_number' => $identityNumber,
            'identity_address' => 'Jl. Merdeka No. 10, Klojen, Kota Malang, Jawa Timur 65111',
            'identity_document_image' => UploadedFile::fake()->image('ktp.jpg'),
            'face_image' => UploadedFile::fake()->image('selfie.jpg'),
            'terms' => '1',
        ]);

        $response->assertRedirect(route('customer.orders'));
        $this->assertDatabaseHas('orders', ['user_id' => $this->user->id]);

        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertSame(Order::SHIPPING_AMBIL_TOKO, $order->shipping_method);
        $this->assertTrue($order->isPickup());
    }

    public function test_store_rejects_invalid_shipping_method(): void
    {
        $product = $this->makeProduct('Box Bayi', 35000);
        $this->seedCart([$product], now()->format('Y-m-d'), now()->addDay()->format('Y-m-d'));

        $response = $this->actingAs($this->user)->post(route('customer.checkout.store', [
            'subdomain' => $this->client->subdomain,
        ]), [
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDay()->format('Y-m-d'),
            'shipping_address' => 'Jl. Merdeka No. 10, Klojen, Kota Malang, Jawa Timur 65111',
            'shipping_method' => 'ojol',
            'full_name' => $this->user->name,
            'identity_number' => '1234567890123456',
            'identity_address' => 'Jl. Merdeka No. 10, Klojen, Kota Malang, Jawa Timur 65111',
            'identity_document_image' => UploadedFile::fake()->image('ktp.jpg'),
            'face_image' => UploadedFile::fake()->image('selfie.jpg'),
            'terms' => '1',
        ]);

        $response->assertSessionHasErrors('shipping_method');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_direct_finds_next_available_period_when_tomorrow_is_booked(): void
    {
        $product = $this->makeProduct('Box Bayi', 35000);

        // Book all 2 units for tomorrow (day 1 to day 2)
        $order = Order::create([
            'client_id' => $this->client->id,
            'user_id' => $this->user->id,
            'order_number' => 'ORD-TEST-BOOKED',
            'status' => 'disetujui',
            'shipping_method' => Order::SHIPPING_KURIR,
            'shipping_address' => 'Test Address',
            'rental_start_date' => now()->addDays(1)->format('Y-m-d'),
            'rental_end_date' => now()->addDays(2)->format('Y-m-d'),
            'duration_days' => 2,
            'total_amount' => 70000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price_per_day' => 35000,
            'subtotal' => 70000,
        ]);

        $this->actingAs($this->user)
            ->get(route('customer.checkout.direct', ['subdomain' => $this->client->subdomain, 'product' => $product->id]))
            ->assertRedirect(route('customer.checkout', ['subdomain' => $this->client->subdomain]));

        $cart = session('cart');
        $this->assertCount(1, $cart);

        $item = reset($cart);
        $this->assertSame($product->id, $item['product_id']);
        // Earliest available 2-day period starts after day 2
        $this->assertTrue(now()->parse($item['start_date'])->gte(now()->addDays(3)->startOfDay()));
    }

    public function test_direct_redirects_back_when_no_units_available_in_30_days(): void
    {
        $product = $this->makeProduct('Box Bayi', 35000);

        // Book all 2 units for 35 days
        $order = Order::create([
            'client_id' => $this->client->id,
            'user_id' => $this->user->id,
            'order_number' => 'ORD-TEST-FULL-MONTH',
            'status' => 'disetujui',
            'shipping_method' => Order::SHIPPING_KURIR,
            'shipping_address' => 'Test Address',
            'rental_start_date' => now()->addDays(1)->format('Y-m-d'),
            'rental_end_date' => now()->addDays(35)->format('Y-m-d'),
            'duration_days' => 35,
            'total_amount' => 35000 * 35 * 2,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price_per_day' => 35000,
            'subtotal' => 35000 * 35 * 2,
        ]);

        $this->actingAs($this->user)
            ->get(route('customer.checkout.direct', ['subdomain' => $this->client->subdomain, 'product' => $product->id]))
            ->assertRedirect(route('customer.product.show', ['subdomain' => $this->client->subdomain, 'product' => $product->id]))
            ->assertSessionHas('error');
    }

    public function test_add_to_cart_modal_confirm_and_checkout(): void
    {
        $product = $this->makeProduct('Box Bayi', 35000);
        $start = now()->addDays(3)->format('Y-m-d');
        $end = now()->addDays(4)->format('Y-m-d');

        Livewire::test(AddToCartModal::class, ['product' => $product])
            ->call('confirmAndCheckout', $start, $end, 1)
            ->assertRedirect(route('customer.checkout', ['subdomain' => $this->client->subdomain]));

        $cart = session('cart');
        $this->assertNotEmpty($cart);
        $this->assertNotEmpty(session('checkout_items'));
    }
}
