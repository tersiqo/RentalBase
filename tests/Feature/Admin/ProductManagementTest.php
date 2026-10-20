<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;

    private User $admin;

    private Category $category;

    private Client $otherClient;

    private User $otherAdmin;

    private Category $otherCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $disk = config('filesystems.default', 'public');
        Storage::fake($disk);
        if ($disk !== 'public') {
            Storage::fake('public');
        }

        $this->client = Client::create([
            'business_name' => 'BabyRent Malang',
            'subdomain' => 'babyrent',
            'status' => 'aktif',
        ]);

        $this->admin = User::create([
            'client_id' => $this->client->id,
            'name' => 'Budi Admin',
            'email' => 'budi@babyrental.com',
            'password' => bcrypt('password'),
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        $this->client->update(['primary_admin_id' => $this->admin->id]);

        $this->makeSubscription($this->client, 'starter');

        $this->category = Category::create([
            'client_id' => $this->client->id,
            'name' => 'Stroller Bayi',
            'slug' => 'stroller-bayi',
        ]);

        $this->otherClient = Client::create([
            'business_name' => 'Toko Alat Gunung',
            'subdomain' => 'alatgunung',
            'status' => 'aktif',
        ]);

        $this->otherAdmin = User::create([
            'client_id' => $this->otherClient->id,
            'name' => 'Siti Admin',
            'email' => 'siti@alatgunung.com',
            'password' => bcrypt('password'),
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        $this->otherClient->update(['primary_admin_id' => $this->otherAdmin->id]);

        $this->otherCategory = Category::create([
            'client_id' => $this->otherClient->id,
            'name' => 'Tenda Dome',
            'slug' => 'tenda-dome',
        ]);
    }

    private function makeSubscription(Client $client, string $plan): Subscription
    {
        return Subscription::create([
            'client_id' => $client->id,
            'plan_name' => $plan,
            'price' => 0,
            'billing_cycle' => 'bulanan',
            'start_date' => now()->subDay(),
            'end_date' => now()->addMonths(6),
            'status' => 'active',
            'payment_status' => 'diverifikasi',
        ]);
    }

    public function test_admin_can_view_products_index(): void
    {
        $product = Product::create([
            'client_id' => $this->client->id,
            'category_id' => $this->category->id,
            'name' => 'Babyzen YOYO2',
            'slug' => 'babyzen-yoyo2',
            'rental_price_per_day' => 150000,
            'late_fee_per_hour' => 10000,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.products.index'));

        $response->assertStatus(200);
        $response->assertSee('Katalog Produk');
        $response->assertSee('Babyzen YOYO2');
        $response->assertSee('150.000');
    }

    public function test_tenant_isolation_in_products_index(): void
    {
        $ownProduct = Product::create([
            'client_id' => $this->client->id,
            'category_id' => $this->category->id,
            'name' => 'Produk Toko Sendiri',
            'slug' => 'produk-toko-sendiri',
            'rental_price_per_day' => 100000,
            'late_fee_per_hour' => 10000,
            'status' => 'aktif',
        ]);

        $otherProduct = Product::create([
            'client_id' => $this->otherClient->id,
            'category_id' => $this->otherCategory->id,
            'name' => 'Produk Toko Sebelah',
            'slug' => 'produk-toko-sebelah',
            'rental_price_per_day' => 200000,
            'late_fee_per_hour' => 20000,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.products.index'));

        $response->assertStatus(200);
        $response->assertSee('Produk Toko Sendiri');
        $response->assertDontSee('Produk Toko Sebelah');
    }

    public function test_admin_can_create_product_with_valid_data(): void
    {
        $file = UploadedFile::fake()->image('stroller.jpg');

        $response = $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'name' => 'Cocolatte Pockit Cl-788',
            'category_id' => $this->category->id,
            'rental_price_per_day' => 90000,
            'late_fee_per_hour' => 5000,
            'deposit_fee' => 0,
            'description' => 'Stroller cabin size praktis dan ringan.',
            'identity_guarantee_requirements' => 'Wajib KTP asli dan selfie.',
            'status' => 'aktif',
            'image' => $file,
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'client_id' => $this->client->id,
            'category_id' => $this->category->id,
            'name' => 'Cocolatte Pockit Cl-788',
            'slug' => 'cocolatte-pockit-cl-788',
            'rental_price_per_day' => 90000,
            'late_fee_per_hour' => 5000,
            'status' => 'aktif',
        ]);

        $created = Product::where('name', 'Cocolatte Pockit Cl-788')->first();
        $this->assertNotNull($created->main_image);
        Storage::disk(config('filesystems.default', 'public'))->assertExists($created->main_image);
    }

    public function test_create_product_fails_when_plan_limit_reached(): void
    {
        // Starter plan limit is 10 products
        for ($i = 1; $i <= 10; $i++) {
            Product::create([
                'client_id' => $this->client->id,
                'category_id' => $this->category->id,
                'name' => "Produk Ke-{$i}",
                'slug' => "produk-ke-{$i}",
                'rental_price_per_day' => 50000,
                'late_fee_per_hour' => 5000,
                'status' => 'aktif',
            ]);
        }

        $response = $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'name' => 'Produk Ke-11 Melebihi Kuota',
            'category_id' => $this->category->id,
            'rental_price_per_day' => 50000,
            'late_fee_per_hour' => 5000,
            'status' => 'aktif',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseMissing('products', [
            'name' => 'Produk Ke-11 Melebihi Kuota',
        ]);
    }

    public function test_create_product_rejects_category_from_different_client(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'name' => 'Produk Cross Tenant',
            'category_id' => $this->otherCategory->id,
            'rental_price_per_day' => 50000,
            'late_fee_per_hour' => 5000,
            'status' => 'aktif',
        ]);

        $response->assertSessionHasErrors('category_id');
        $this->assertDatabaseMissing('products', [
            'name' => 'Produk Cross Tenant',
        ]);
    }

    public function test_admin_can_update_product(): void
    {
        $product = Product::create([
            'client_id' => $this->client->id,
            'category_id' => $this->category->id,
            'name' => 'Nama Produk Lama',
            'slug' => 'nama-produk-lama',
            'rental_price_per_day' => 75000,
            'late_fee_per_hour' => 5000,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.products.update', $product), [
            'name' => 'Nama Produk Baru',
            'category_id' => $this->category->id,
            'rental_price_per_day' => 85000,
            'late_fee_per_hour' => 7000,
            'status' => 'aktif',
            'description' => 'Deskripsi update.',
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Nama Produk Baru',
            'slug' => 'nama-produk-baru',
            'rental_price_per_day' => 85000,
            'late_fee_per_hour' => 7000,
        ]);
    }

    public function test_admin_cannot_update_product_of_another_client(): void
    {
        $foreignProduct = Product::create([
            'client_id' => $this->otherClient->id,
            'category_id' => $this->otherCategory->id,
            'name' => 'Produk Toko Lain',
            'slug' => 'produk-toko-lain',
            'rental_price_per_day' => 100000,
            'late_fee_per_hour' => 10000,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.products.update', $foreignProduct), [
            'name' => 'Coba Retas Produk',
            'category_id' => $this->category->id,
            'rental_price_per_day' => 1000,
            'late_fee_per_hour' => 100,
            'status' => 'aktif',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_toggle_product_status(): void
    {
        $product = Product::create([
            'client_id' => $this->client->id,
            'category_id' => $this->category->id,
            'name' => 'Produk Toggle',
            'slug' => 'produk-toggle',
            'rental_price_per_day' => 50000,
            'late_fee_per_hour' => 5000,
            'status' => 'aktif',
        ]);

        // Toggle from aktif to nonaktif
        $response = $this->actingAs($this->admin)->post(route('admin.products.toggle-status', $product));
        $response->assertRedirect(route('admin.products.index'));
        $this->assertEquals('nonaktif', $product->fresh()->status);

        // Toggle back from nonaktif to aktif
        $this->actingAs($this->admin)->post(route('admin.products.toggle-status', $product));
        $this->assertEquals('aktif', $product->fresh()->status);
    }

    public function test_admin_cannot_delete_product_with_orders(): void
    {
        $product = Product::create([
            'client_id' => $this->client->id,
            'category_id' => $this->category->id,
            'name' => 'Produk Yang Pernah Dipesan',
            'slug' => 'produk-yang-pernah-dipesan',
            'rental_price_per_day' => 120000,
            'late_fee_per_hour' => 10000,
            'status' => 'aktif',
        ]);

        $order = Order::create([
            'client_id' => $this->client->id,
            'order_number' => 'ORD-TEST-001',
            'user_id' => $this->admin->id,
            'status' => 'selesai',
            'total_amount' => 120000,
            'duration_days' => 1,
            'shipping_address' => 'Jl. Test No. 1 Malang',
            'rental_start_date' => now()->subDays(5),
            'rental_end_date' => now()->subDays(4),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price_per_day' => 120000,
            'subtotal' => 120000,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.products.destroy', $product));

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_admin_can_delete_product_without_orders(): void
    {
        $product = Product::create([
            'client_id' => $this->client->id,
            'category_id' => $this->category->id,
            'name' => 'Produk Baru Tanpa Order',
            'slug' => 'produk-baru-tanpa-order',
            'rental_price_per_day' => 50000,
            'late_fee_per_hour' => 5000,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.products.destroy', $product));

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
