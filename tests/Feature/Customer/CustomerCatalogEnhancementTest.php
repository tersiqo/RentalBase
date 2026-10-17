<?php

namespace Tests\Feature\Customer;

use App\Livewire\Customer\ProductCatalog;
use App\Models\Category;
use App\Models\Client;
use App\Models\EquipmentUnit;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerCatalogEnhancementTest extends TestCase
{
    use RefreshDatabase;

    protected $client;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Client::create([
            'business_name' => 'Kamera Malang Rental',
            'description' => 'Sewa kamera & lensa profesional termurah di Malang.',
            'subdomain' => 'kamerarental',
            'status' => 'aktif',
        ]);

        $this->category = Category::create([
            'client_id' => $this->client->id,
            'name' => 'Mirrorless',
            'slug' => 'mirrorless',
        ]);
    }

    public function test_catalog_renders_dynamic_client_description_in_hero()
    {
        $categories = Category::where('client_id', $this->client->id)->get();

        Livewire::test(ProductCatalog::class, ['client' => $this->client, 'categories' => $categories])
            ->assertSee('Sewa kamera & lensa profesional termurah di Malang.')
            ->assertSee('Kamera Malang Rental');
    }

    public function test_catalog_shows_category_badge_on_product_card()
    {
        $product = Product::create([
            'client_id' => $this->client->id,
            'category_id' => $this->category->id,
            'name' => 'Sony A7 IV Body Only',
            'slug' => 'sony-a7-iv-body-only',
            'rental_price_per_day' => 250000,
            'status' => 'aktif',
        ]);

        EquipmentUnit::create([
            'client_id' => $this->client->id,
            'product_id' => $product->id,
            'unit_code' => 'SNY-01',
            'status' => 'tersedia',
        ]);

        $categories = Category::where('client_id', $this->client->id)->get();

        Livewire::test(ProductCatalog::class, ['client' => $this->client, 'categories' => $categories])
            ->assertSee('Mirrorless')
            ->assertSee('Sony A7 IV Body Only');
    }

    public function test_quick_add_to_cart_adds_product_to_session_and_dispatches_event()
    {
        $product = Product::create([
            'client_id' => $this->client->id,
            'category_id' => $this->category->id,
            'name' => 'Fujifilm X-T4',
            'slug' => 'fujifilm-x-t4',
            'rental_price_per_day' => 180000,
            'status' => 'aktif',
        ]);

        EquipmentUnit::create([
            'client_id' => $this->client->id,
            'product_id' => $product->id,
            'unit_code' => 'FJI-01',
            'status' => 'tersedia',
        ]);

        $categories = Category::where('client_id', $this->client->id)->get();

        $tomorrow = Carbon::now()->addDays(2)->format('Y-m-d');
        $afterTomorrow = Carbon::now()->addDays(4)->format('Y-m-d');

        Livewire::test(ProductCatalog::class, ['client' => $this->client, 'categories' => $categories])
            ->set('startDate', $tomorrow)
            ->set('endDate', $afterTomorrow)
            ->call('quickAddToCart', $product->id)
            ->assertDispatched('cart-updated')
            ->assertSee('berhasil ditambahkan ke keranjang');

        $cart = session()->get('cart', []);
        $this->assertNotEmpty($cart);
        $cartItem = array_values($cart)[0];
        $this->assertEquals($product->id, $cartItem['product_id']);
        $this->assertEquals(3, $cartItem['duration_days']);
    }

    public function test_date_filter_checks_availability_against_overlapping_orders()
    {
        $product = Product::create([
            'client_id' => $this->client->id,
            'category_id' => $this->category->id,
            'name' => 'Canon EOS R6',
            'slug' => 'canon-eos-r6',
            'rental_price_per_day' => 300000,
            'status' => 'aktif',
        ]);

        // Create 1 unit
        EquipmentUnit::create([
            'client_id' => $this->client->id,
            'product_id' => $product->id,
            'unit_code' => 'CNN-01',
            'status' => 'tersedia',
        ]);

        $user = User::create([
            'name' => 'Budi Customer',
            'email' => 'budi@test.com',
            'password' => bcrypt('secret123'),
            'role' => 'customer',
        ]);

        // Order booked on 2026-11-01 to 2026-11-05
        $order = Order::create([
            'client_id' => $this->client->id,
            'user_id' => $user->id,
            'order_number' => 'ORD-TEST-01',
            'rental_start_date' => '2026-11-01',
            'rental_end_date' => '2026-11-05',
            'duration_days' => 5,
            'shipping_address' => 'Jl. Ijen No. 10, Malang',
            'total_amount' => 1500000,
            'status' => 'sedang_disewa',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price_per_day' => 300000,
            'subtotal' => 1500000,
        ]);

        $categories = Category::where('client_id', $this->client->id)->get();

        // When searching outside booked dates (e.g. 2026-11-10 to 2026-11-12) -> Unit should be available
        Livewire::test(ProductCatalog::class, ['client' => $this->client, 'categories' => $categories])
            ->set('startDate', '2026-11-10')
            ->set('endDate', '2026-11-12')
            ->assertSee('Tersedia 1');

        // When searching overlapping dates (e.g. 2026-11-02 to 2026-11-03) -> Unit should be empty
        Livewire::test(ProductCatalog::class, ['client' => $this->client, 'categories' => $categories])
            ->set('startDate', '2026-11-02')
            ->set('endDate', '2026-11-03')
            ->assertSee('Unit Kosong');
    }

    public function test_catalog_paginates_nine_items_per_page()
    {
        for ($i = 1; $i <= 12; $i++) {
            Product::create([
                'client_id' => $this->client->id,
                'category_id' => $this->category->id,
                'name' => "Alat Rental No {$i}",
                'slug' => "alat-rental-no-{$i}",
                'rental_price_per_day' => 100000 + ($i * 1000),
                'status' => 'aktif',
            ]);
        }

        $categories = Category::where('client_id', $this->client->id)->get();

        $component = Livewire::test(ProductCatalog::class, ['client' => $this->client, 'categories' => $categories]);

        $this->assertEquals(9, $component->get('products')->count());
        $this->assertEquals(12, $component->get('products')->total());
        $this->assertEquals(2, $component->get('products')->lastPage());
    }

    public function test_product_card_image_does_not_contain_opacity_zero_on_render_and_filter()
    {
        $product = Product::create([
            'client_id' => $this->client->id,
            'category_id' => $this->category->id,
            'name' => 'Sony A7 IV Body Only',
            'slug' => 'sony-a7-iv-body-only',
            'rental_price_per_day' => 250000,
            'main_image' => 'products/sample.jpg',
            'status' => 'aktif',
        ]);

        $categories = Category::where('client_id', $this->client->id)->get();

        Livewire::test(ProductCatalog::class, ['client' => $this->client, 'categories' => $categories])
            ->assertDontSee('opacity-0')
            ->set('selectedCategory', (string) $this->category->id)
            ->assertDontSee('opacity-0')
            ->assertSee('products/sample.jpg');
    }
}
