<?php

namespace Tests\Unit;

use App\Livewire\Customer\AddToCartModal;
use App\Livewire\Customer\CartPage;
use App\Models\Client;
use App\Models\EquipmentUnit;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CartAvailabilityUnitTest extends TestCase
{
    use RefreshDatabase;
    /**
     * [7.2-I1] Memastikan unit fisik berstatus 'tersedia' mengembalikan nilai true saat dicek periode sewa valid.
     */
    public function test_ut_06_001_equipment_unit_is_available_for_valid_period(): void
    {
        $unit = new EquipmentUnit(['status' => 'tersedia']);

        $this->assertTrue($unit->isAvailableForPeriod('2026-12-01', '2026-12-05'));
    }

    /**
     * [7.2-I1] Memastikan sinkronisasi status fisik unit tidak menimpa status unit yang sedang rusak.
     */
    public function test_ut_06_002_sync_physical_status_preserves_damaged_status(): void
    {
        $unit = new EquipmentUnit(['status' => 'rusak']);
        $unit->syncPhysicalStatus();

        $this->assertEquals('rusak', $unit->status);
    }

    /**
     * [7.2-I1] Memastikan relasi model Product ke master unit fisik (EquipmentUnit) terhubung via HasMany.
     */
    public function test_ut_06_003_product_units_relationship(): void
    {
        $product = new Product();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $product->units());
    }

    /**
     * [7.2-I1] Memastikan accessor cover_image_url menghasilkan fallback placeholder saat gambar utama kosong.
     */
    public function test_ut_06_004_product_cover_image_url_fallback(): void
    {
        $product = new Product(['name' => 'Kamera Sony A7IV', 'main_image' => null]);

        $this->assertNotNull($product->cover_image_url);
        $this->assertStringContainsString('placehold.co', $product->cover_image_url);
    }

    /**
     * [7.2-I1] Memastikan relasi model Product terhubung ke Category dan Client via BelongsTo.
     */
    public function test_ut_06_005_product_category_and_client_relationships(): void
    {
        $product = new Product();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $product->category());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $product->client());
    }

    /**
     * [7.2-I1] Memastikan model Cart dan CartItem dapat diinstansiasi dengan benar untuk penyimpanan session dan database.
     */
    public function test_ut_06_006_cart_and_cart_item_models_instantiation(): void
    {
        $cart = new \App\Models\Cart();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Model::class, $cart);

        $cartItem = new \App\Models\CartItem();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Model::class, $cartItem);
    }

    /**
     * UT-06-009: Memastikan method removeItem menghapus data item sesi keranjang berdasarkan hash dan memicu event
     */
    public function test_ut_06_009_cart_page_remove_item_session_and_events(): void
    {
        $client = Client::create([
            'business_name' => 'Kamera Malang',
            'subdomain' => 'kameramalang',
            'status' => 'aktif',
        ]);

        $category = \App\Models\Category::forceCreate([
            'client_id' => $client->id,
            'name' => 'Kamera',
            'slug' => 'kamera',
        ]);

        $product = Product::create([
            'client_id' => $client->id,
            'category_id' => $category->id,
            'name' => 'Lensa Sony 50mm',
            'slug' => 'lensa-sony-50mm',
            'rental_price_per_day' => 50000,
            'deposit_fee' => 0,
            'status' => 'aktif',
        ]);

        $user = User::create([
            'name' => 'Fiza Tester',
            'email' => 'fiza.cart@example.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        $start = now()->addDays(2)->format('Y-m-d');
        $end = now()->addDays(4)->format('Y-m-d');
        $hash = md5($product->id . $start . $end);

        session()->put('cart', [
            $hash => [
                'hash' => $hash,
                'client_id' => $client->id,
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => 50000,
                'deposit_fee' => 0,
                'main_image' => null,
                'start_date' => $start,
                'end_date' => $end,
                'duration_days' => 2,
                'quantity' => 1,
                'subtotal' => 100000,
            ],
        ]);

        Livewire::actingAs($user)
            ->test(CartPage::class, ['subdomain' => $client->subdomain])
            ->call('removeItem', $hash)
            ->assertDispatched('cart-updated');

        $this->assertArrayNotHasKey($hash, session()->get('cart', []));
    }

    /**
     * UT-06-010: Memastikan penambahan item via AddToCartModal terisolasi per client_id tenant
     */
    public function test_ut_06_010_add_to_cart_modal_tenant_isolation(): void
    {
        $client = Client::create([
            'business_name' => 'Outdoor Store',
            'subdomain' => 'outdoorstore',
            'status' => 'aktif',
        ]);

        $category = \App\Models\Category::forceCreate([
            'client_id' => $client->id,
            'name' => 'Outdoor',
            'slug' => 'outdoor',
        ]);

        $product = Product::create([
            'client_id' => $client->id,
            'category_id' => $category->id,
            'name' => 'Carrier Deuter 60L',
            'slug' => 'carrier-deuter-60l',
            'rental_price_per_day' => 45000,
            'deposit_fee' => 0,
            'status' => 'aktif',
        ]);

        EquipmentUnit::create([
            'client_id' => $client->id,
            'product_id' => $product->id,
            'unit_code' => 'UNIT-DEUTER-01',
            'status' => 'tersedia',
        ]);

        $startDate = now()->addDays(5)->format('Y-m-d');
        $endDate = now()->addDays(7)->format('Y-m-d');

        Livewire::test(AddToCartModal::class, ['product' => $product])
            ->call('confirm', $startDate, $endDate, 1)
            ->assertDispatched('cart-updated')
            ->assertDispatched('close-cart-modal');

        $cart = session()->get('cart', []);
        $this->assertNotEmpty($cart);
        $addedItem = array_values($cart)[0];
        $this->assertEquals($client->id, $addedItem['client_id']);
        $this->assertEquals($product->id, $addedItem['product_id']);
    }
}
