<?php

namespace Tests\Feature\Customer;

use App\Livewire\Customer\ProductCatalog;
use App\Models\Category;
use App\Models\Client;
use App\Models\EquipmentUnit;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemUnit;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
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

    public function test_it_06_001_quick_add_to_cart_adds_product_to_session_and_dispatches_event()
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

    public function test_it_05_005_date_filter_checks_availability_against_overlapping_orders()
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

    public function test_product_period_availability_handles_multi_unit_and_order_statuses()
    {
        $product = Product::create([
            'client_id' => $this->client->id,
            'category_id' => $this->category->id,
            'name' => 'Stroller Joie Pact',
            'slug' => 'stroller-joie-pact',
            'rental_price_per_day' => 80000,
            'status' => 'aktif',
        ]);

        // Create 3 operational units + 1 damaged unit
        for ($i = 1; $i <= 3; $i++) {
            EquipmentUnit::create([
                'client_id' => $this->client->id,
                'product_id' => $product->id,
                'unit_code' => "STR-0{$i}",
                'status' => 'tersedia',
            ]);
        }
        EquipmentUnit::create([
            'client_id' => $this->client->id,
            'product_id' => $product->id,
            'unit_code' => 'STR-04',
            'status' => 'rusak',
        ]);

        $user = User::create([
            'name' => 'Siti Customer',
            'email' => 'siti@test.com',
            'password' => bcrypt('secret123'),
            'role' => 'customer',
        ]);

        // Active order booking 2 units from 2026-12-01 to 2026-12-05
        $activeOrder = Order::create([
            'client_id' => $this->client->id,
            'user_id' => $user->id,
            'order_number' => 'ORD-ACT-01',
            'rental_start_date' => '2026-12-01',
            'rental_end_date' => '2026-12-05',
            'duration_days' => 5,
            'shipping_address' => 'Jl. Soekarno Hatta, Malang',
            'total_amount' => 800000,
            'status' => 'menunggu_konfirmasi',
        ]);

        OrderItem::create([
            'order_id' => $activeOrder->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price_per_day' => 80000,
            'subtotal' => 800000,
        ]);

        // Cancelled order booking 1 unit on same dates (should be ignored)
        $cancelledOrder = Order::create([
            'client_id' => $this->client->id,
            'user_id' => $user->id,
            'order_number' => 'ORD-CAN-01',
            'rental_start_date' => '2026-12-01',
            'rental_end_date' => '2026-12-05',
            'duration_days' => 5,
            'shipping_address' => 'Jl. Soekarno Hatta, Malang',
            'total_amount' => 400000,
            'status' => 'batal',
        ]);

        OrderItem::create([
            'order_id' => $cancelledOrder->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price_per_day' => 80000,
            'subtotal' => 400000,
        ]);

        // Overlapping period: 3 operational - 2 booked = 1 available
        $this->assertEquals(1, $product->getAvailableUnitsForPeriod('2026-12-02', '2026-12-04'));

        // Non-overlapping period: all 3 operational units available
        $this->assertEquals(3, $product->getAvailableUnitsForPeriod('2026-12-10', '2026-12-12'));
    }

    public function test_it_06_002_add_to_cart_modal_validates_period_availability()
    {
        $product = Product::create([
            'client_id' => $this->client->id,
            'category_id' => $this->category->id,
            'name' => 'Baby Box Nuna',
            'slug' => 'baby-box-nuna',
            'rental_price_per_day' => 120000,
            'status' => 'aktif',
        ]);

        // 2 operational units
        for ($i = 1; $i <= 2; $i++) {
            EquipmentUnit::create([
                'client_id' => $this->client->id,
                'product_id' => $product->id,
                'unit_code' => "BBX-0{$i}",
                'status' => 'tersedia',
            ]);
        }

        $user = User::create([
            'name' => 'Andi Customer',
            'email' => 'andi@test.com',
            'password' => bcrypt('secret123'),
            'role' => 'customer',
        ]);

        // Book 1 unit on 2026-12-15 to 2026-12-18
        $order = Order::create([
            'client_id' => $this->client->id,
            'user_id' => $user->id,
            'order_number' => 'ORD-BBX-01',
            'rental_start_date' => '2026-12-15',
            'rental_end_date' => '2026-12-18',
            'duration_days' => 4,
            'shipping_address' => 'Malang',
            'total_amount' => 480000,
            'status' => 'siap_kirim',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price_per_day' => 120000,
            'subtotal' => 480000,
        ]);

        // Requesting 2 units on overlapping dates should fail because only 1 is left
        Livewire::test(\App\Livewire\Customer\AddToCartModal::class, ['product' => $product])
            ->call('checkAvailability', '2026-12-16', '2026-12-17')
            ->assertSet('availableUnits', 1)
            ->call('confirm', '2026-12-16', '2026-12-17', 2)
            ->assertNotDispatched('cart-updated')
            ->assertSee('Jumlah unit melebihi stok tersedia pada tanggal tersebut (tersisa 1 unit).');

        // Requesting 1 unit on overlapping dates should succeed
        Livewire::test(\App\Livewire\Customer\AddToCartModal::class, ['product' => $product])
            ->call('confirm', '2026-12-16', '2026-12-17', 1)
            ->assertDispatched('cart-updated');
    }

    public function test_catalog_opens_add_to_cart_modal_and_confirms_item()
    {
        $product = Product::create([
            'client_id' => $this->client->id,
            'category_id' => $this->category->id,
            'name' => 'Sony FX3 Cinema Line',
            'slug' => 'sony-fx3-cinema-line',
            'rental_price_per_day' => 450000,
            'status' => 'aktif',
        ]);

        EquipmentUnit::create([
            'client_id' => $this->client->id,
            'product_id' => $product->id,
            'unit_code' => 'FX3-01',
            'status' => 'tersedia',
        ]);

        $categories = Category::where('client_id', $this->client->id)->get();

        Livewire::test(ProductCatalog::class, ['client' => $this->client, 'categories' => $categories])
            ->call('openAddToCartModal', $product->id)
            ->assertSet('showModal', true)
            ->assertSet('modalProduct.id', $product->id)
            ->assertSet('modalAvailableUnits', 1)
            ->set('modalStartDate', '2026-12-20')
            ->set('modalEndDate', '2026-12-22')
            ->set('modalQuantity', 1)
            ->call('confirmModalAddToCart')
            ->assertSet('showModal', false)
            ->assertDispatched('cart-updated');

        $cart = session()->get('cart', []);
        $this->assertNotEmpty($cart);
        $cartItem = array_values($cart)[0];
        $this->assertEquals($product->id, $cartItem['product_id']);
        $this->assertEquals('2026-12-20', $cartItem['start_date']);
        $this->assertEquals('2026-12-22', $cartItem['end_date']);
        $this->assertEquals(3, $cartItem['duration_days']);
        $this->assertEquals(1350000, $cartItem['subtotal']);
    }

    public function test_it_06_003_cart_page_can_edit_rental_dates_and_updates_period()
    {
        $product = Product::create([
            'client_id' => $this->client->id,
            'category_id' => $this->category->id,
            'name' => 'Sony A7S III',
            'slug' => 'sony-a7s-iii',
            'rental_price_per_day' => 350000,
            'status' => 'aktif',
        ]);

        EquipmentUnit::create([
            'client_id' => $this->client->id,
            'product_id' => $product->id,
            'unit_code' => 'A7S-01',
            'status' => 'tersedia',
        ]);

        $oldStart = '2026-12-01';
        $oldEnd = '2026-12-02';
        $oldHash = md5($product->id . $oldStart . $oldEnd);

        session()->put('cart', [
            $oldHash => [
                'hash' => $oldHash,
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->rental_price_per_day,
                'deposit_fee' => 0,
                'main_image' => null,
                'start_date' => $oldStart,
                'end_date' => $oldEnd,
                'duration_days' => 2,
                'quantity' => 1,
                'subtotal' => 700000,
            ],
        ]);

        $user = User::create([
            'name' => 'Cart Customer',
            'email' => 'cart_customer@test.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'status' => 'aktif',
        ]);
        $this->actingAs($user);

        $newStart = '2026-12-05';
        $newEnd = '2026-12-08'; // 4 days
        $newHash = md5($product->id . $newStart . $newEnd);

        Livewire::test(\App\Livewire\Customer\CartPage::class, ['subdomain' => $this->client->subdomain])
            ->call('openEditDateModal', $oldHash)
            ->assertSet('showEditDateModal', true)
            ->assertSet('editingStartDate', $oldStart)
            ->assertSet('editingEndDate', $oldEnd)
            ->set('editingStartDate', $newStart)
            ->set('editingEndDate', $newEnd)
            ->call('saveEditedDate')
            ->assertSet('showEditDateModal', false)
            ->assertDispatched('cart-updated');

        $cart = session()->get('cart', []);
        $this->assertArrayNotHasKey($oldHash, $cart);
        $this->assertArrayHasKey($newHash, $cart);
        $this->assertEquals(4, $cart[$newHash]['duration_days']);
        $this->assertEquals(1400000, $cart[$newHash]['subtotal']);
        $this->assertEquals($newStart, $cart[$newHash]['start_date']);
        $this->assertEquals($newEnd, $cart[$newHash]['end_date']);
    }

    public function test_guest_sees_limited_preview_and_locked_catalog_notice()
    {
        for ($i = 1; $i <= 6; $i++) {
            Product::create([
                'client_id' => $this->client->id,
                'category_id' => $this->category->id,
                'name' => "Peralatan Bayi {$i}",
                'slug' => "peralatan-bayi-{$i}",
                'rental_price_per_day' => 50000 * $i,
                'status' => 'aktif',
            ]);
        }

        $categories = Category::where('client_id', $this->client->id)->get();

        Livewire::test(ProductCatalog::class, ['client' => $this->client, 'categories' => $categories])
            ->assertSee('Buka Akses Penuh')
            ->assertSee('Mau Lihat Pilihan Alat Lainnya?')
            ->assertSee('Masuk ke Akun')
            ->assertSee('Daftar Akun Baru')
            ->assertSee('Peralatan Bayi 1')
            ->assertSee('Peralatan Bayi 2')
            ->assertSee('Peralatan Bayi 3');
    }

    public function test_authenticated_customer_sees_all_products_without_lock()
    {
        for ($i = 1; $i <= 6; $i++) {
            Product::create([
                'client_id' => $this->client->id,
                'category_id' => $this->category->id,
                'name' => "Peralatan Lengkap {$i}",
                'slug' => "peralatan-lengkap-{$i}",
                'rental_price_per_day' => 60000 * $i,
                'status' => 'aktif',
            ]);
        }

        $user = User::create([
            'name' => 'Customer Login',
            'email' => 'customer_login@test.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        $this->actingAs($user);

        $categories = Category::where('client_id', $this->client->id)->get();

        Livewire::test(ProductCatalog::class, ['client' => $this->client, 'categories' => $categories])
            ->assertDontSee('Buka Akses Penuh')
            ->assertDontSee('Mau Lihat Pilihan Alat Lainnya?')
            ->assertSee('Peralatan Lengkap 1')
            ->assertSee('Peralatan Lengkap 6');
    }

    public function test_guest_cannot_access_product_detail_and_is_redirected_to_login()
    {
        $product = Product::create([
            'client_id' => $this->client->id,
            'category_id' => $this->category->id,
            'name' => 'Detail Test Product',
            'slug' => 'detail-test-product',
            'rental_price_per_day' => 150000,
            'status' => 'aktif',
        ]);

        $url = route('customer.product.show', ['subdomain' => $this->client->subdomain, 'product' => $product->id]);

        $response = $this->get($url);

        $response->assertRedirect();
        $this->assertStringContainsString('/login', $response->headers->get('Location'));
    }

    public function test_it_06_004_guest_cannot_access_cart_and_is_redirected_to_login()
    {
        $url = route('customer.cart', ['subdomain' => $this->client->subdomain]);

        $response = $this->get($url);

        $response->assertRedirect();
        $this->assertStringContainsString('/login', $response->headers->get('Location'));
    }

    public function test_catalog_page_applies_custom_theme_color_to_hero_and_badges()
    {
        \App\Models\TenantProfile::create([
            'client_id' => $this->client->id,
            'name' => 'Kamera Malang Rental',
            'subdomain' => $this->client->subdomain,
            'theme_color' => '#1D4ED8',
            'text_color' => '#ffffff',
        ]);

        $response = $this->get(route('customer.home', ['subdomain' => $this->client->subdomain]));

        $response->assertStatus(200);
        $response->assertSee('--tenant-theme: #1D4ED8;', false);
        $response->assertSee('tenant-hero-bg');
        $response->assertSee('tenant-hero-card');
        $response->assertSee('tenant-badge');
    }

    public function test_customer_layout_renders_interactive_cara_sewa_and_bantuan_modals()
    {
        $response = $this->get(route('customer.home', ['subdomain' => $this->client->subdomain]));

        $response->assertStatus(200);
        $response->assertSee('howToRentModalOpen');
        $response->assertSee('helpModalOpen');
        $response->assertSee('Panduan Cara Sewa &amp; Jaminan Identitas', false);
        $response->assertSee('Wajib Titip KTP');
        $response->assertSee('Pusat Bantuan &amp; Tanya Jawab', false);
        $response->assertSee('Apakah perlu membayar uang deposit jaminan?');
    }

    public function test_catalog_hero_renders_default_illustration_when_mode_is_illustration()
    {
        Cache::forever("tenant_hero_showcase_{$this->client->id}", [
            'mode' => 'illustration',
            'product_id' => null,
        ]);

        Product::create([
            'client_id' => $this->client->id,
            'category_id' => $this->category->id,
            'name' => 'Sony FX3 Cinema Line',
            'slug' => 'sony-fx3-cinema-line',
            'rental_price_per_day' => 450000,
            'status' => 'aktif',
        ]);

        $categories = Category::where('client_id', $this->client->id)->get();

        Livewire::test(ProductCatalog::class, ['client' => $this->client, 'categories' => $categories])
            ->assertSeeHtml('<svg width="230" height="230"')
            ->assertDontSee('⭐ Produk Unggulan');
    }

    public function test_catalog_hero_renders_featured_product_showcase_when_mode_is_featured()
    {
        Cache::forever("tenant_hero_showcase_{$this->client->id}", [
            'mode' => 'featured',
            'product_id' => null,
        ]);

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
            'unit_code' => 'SNY-HERO-01',
            'status' => 'tersedia',
        ]);

        $categories = Category::where('client_id', $this->client->id)->get();

        Livewire::test(ProductCatalog::class, ['client' => $this->client, 'categories' => $categories])
            ->assertSee('⭐ Produk Unggulan')
            ->assertSee('Sony A7 IV Body Only')
            ->assertSee('250.000')
            ->assertSee('1 Tersedia')
            ->assertSee('Lihat Detail')
            ->assertSee('+ Sewa');
    }

    public function test_catalog_hero_renders_specific_featured_product_when_specified_by_admin()
    {
        $productA = Product::create([
            'client_id' => $this->client->id,
            'category_id' => $this->category->id,
            'name' => 'Produk A Biasa',
            'slug' => 'produk-a-biasa',
            'rental_price_per_day' => 100000,
            'status' => 'aktif',
        ]);

        $productB = Product::create([
            'client_id' => $this->client->id,
            'category_id' => $this->category->id,
            'name' => 'Produk B Pilihan Admin',
            'slug' => 'produk-b-pilihan-admin',
            'rental_price_per_day' => 350000,
            'status' => 'aktif',
        ]);

        Cache::forever("tenant_hero_showcase_{$this->client->id}", [
            'mode' => 'featured',
            'product_id' => $productB->id,
        ]);

        $categories = Category::where('client_id', $this->client->id)->get();

        $component = Livewire::test(ProductCatalog::class, ['client' => $this->client, 'categories' => $categories]);

        $component->assertSee('⭐ Produk Unggulan')
            ->assertSee('Produk B Pilihan Admin')
            ->assertSee('350.000');

        $this->assertEquals($productB->id, $component->get('featuredProduct')->id);
    }

    public function test_catalog_hero_falls_back_to_illustration_when_featured_mode_but_no_active_products()
    {
        Cache::forever("tenant_hero_showcase_{$this->client->id}", [
            'mode' => 'featured',
            'product_id' => null,
        ]);

        // Toko tidak memiliki produk aktif sama sekali
        $categories = Category::where('client_id', $this->client->id)->get();

        Livewire::test(ProductCatalog::class, ['client' => $this->client, 'categories' => $categories])
            ->assertSeeHtml('<svg width="230" height="230"')
            ->assertDontSee('⭐ Produk Unggulan');
    }

    public function test_admin_can_update_hero_showcase_settings_via_tenant_profile_controller()
    {
        $admin = User::create([
            'name' => 'Admin Utama',
            'email' => 'admin_hero@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin_rental',
            'client_id' => $this->client->id,
            'status' => 'aktif',
        ]);

        $this->client->update(['primary_admin_id' => $admin->id]);

        $this->actingAs($admin);

        $product = Product::create([
            'client_id' => $this->client->id,
            'category_id' => $this->category->id,
            'name' => 'Kamera Spesifik Highlight',
            'slug' => 'kamera-spesifik-highlight',
            'rental_price_per_day' => 200000,
            'status' => 'aktif',
        ]);

        $response = $this->post(route('admin.settings.profile.update'), [
            'name' => 'Kamera Malang Rental Baru',
            'hero_showcase_mode' => 'featured',
            'hero_featured_product_id' => $product->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $savedCache = Cache::get("tenant_hero_showcase_{$this->client->id}");
        $this->assertNotNull($savedCache);
        $this->assertEquals('featured', $savedCache['mode']);
        $this->assertEquals($product->id, $savedCache['product_id']);

        // Test switch back to illustration
        $response2 = $this->post(route('admin.settings.profile.update'), [
            'name' => 'Kamera Malang Rental Baru',
            'hero_showcase_mode' => 'illustration',
            'hero_featured_product_id' => null,
        ]);

        $response2->assertRedirect();
        $savedCache2 = Cache::get("tenant_hero_showcase_{$this->client->id}");
        $this->assertEquals('illustration', $savedCache2['mode']);
        $this->assertNull($savedCache2['product_id']);
    }

    public function test_advance_booking_preserves_availability_for_prior_non_conflicting_dates(): void
    {
        $user = User::factory()->create([
            'client_id' => $this->client->id,
            'role' => 'customer',
        ]);

        $product = Product::create([
            'client_id' => $this->client->id,
            'category_id' => $this->category->id,
            'name' => 'Stroller Bayi Fleksibel',
            'slug' => 'stroller-bayi-fleksibel',
            'rental_price_per_day' => 50000,
            'status' => 'aktif',
        ]);

        $unit = EquipmentUnit::create([
            'client_id' => $this->client->id,
            'product_id' => $product->id,
            'unit_code' => 'STR-FLEKS-01',
            'status' => 'tersedia',
        ]);

        // Customer A memesan untuk Tanggal 10 - 11 November 2026
        $order = Order::create([
            'client_id' => $this->client->id,
            'user_id' => $user->id,
            'order_number' => 'ORD-NOV-10-11',
            'rental_start_date' => '2026-11-10 10:00:00',
            'rental_end_date' => '2026-11-11 10:00:00',
            'duration_days' => 2,
            'shipping_address' => 'Jl. Ijen No. 10, Malang',
            'total_amount' => 100000,
            'status' => 'siap_kirim',
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price_per_day' => 50000,
            'subtotal' => 100000,
        ]);

        OrderItemUnit::create([
            'order_item_id' => $item->id,
            'equipment_unit_id' => $unit->id,
        ]);

        $categories = Category::where('client_id', $this->client->id)->get();

        // 1. Saat buka katalog tanpa memilih tanggal (hari ini: tgl 1 Nov):
        // Unit tetap terlihat 'Tersedia 1' karena tidak ada yang menyewa hari ini
        Livewire::test(ProductCatalog::class, ['client' => $this->client, 'categories' => $categories])
            ->assertSee($product->name)
            ->assertSee('Tersedia 1');

        // 2. Saat Customer B mencari tanggal 8 - 9 November (tidak bentrok):
        // Unit tetap 'Tersedia 1' sehingga Customer B BISA MENYEWA!
        Livewire::test(ProductCatalog::class, ['client' => $this->client, 'categories' => $categories])
            ->set('startDate', '2026-11-08')
            ->set('endDate', '2026-11-09')
            ->assertSee('Tersedia 1');

        // 3. Saat mencari tanggal 10 - 11 November (bentrok dengan Customer A):
        // Unit tampil 'Unit Kosong'
        Livewire::test(ProductCatalog::class, ['client' => $this->client, 'categories' => $categories])
            ->set('startDate', '2026-11-10')
            ->set('endDate', '2026-11-11')
            ->assertSee('Unit Kosong');
    }
}


