<?php

namespace Tests\Feature\Admin;

use App\Livewire\Customer\ProductCatalog;
use App\Models\Category;
use App\Models\Client;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\TenantProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SystemRevisionsTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;
    private User $admin;
    private User $owner;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->client = Client::create([
            'business_name' => 'Rental Outdoor Explorer',
            'subdomain' => 'outdoorexplorer',
            'status' => 'aktif',
        ]);

        $this->admin = User::create([
            'client_id' => $this->client->id,
            'name' => 'Admin Outdoor',
            'email' => 'admin@outdoorexplorer.com',
            'password' => bcrypt('password'),
            'role' => 'admin_rental',
            'status' => 'aktif',
            'is_primary' => true,
        ]);

        $this->owner = User::create([
            'name' => 'Owner Platform RentalBase',
            'email' => 'owner@rentalbase.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'status' => 'aktif',
        ]);

        $this->category = Category::create([
            'client_id' => $this->client->id,
            'name' => 'Tenda & Camping',
            'slug' => 'tenda-camping',
        ]);
    }

    public function test_admin_can_upload_multiple_product_images_and_gallery_accessor_combines_them(): void
    {
        $this->actingAs($this->admin);

        $mainImage = UploadedFile::fake()->image('cover.jpg', 600, 600);
        $galleryImg1 = UploadedFile::fake()->image('gallery1.jpg', 600, 600);
        $galleryImg2 = UploadedFile::fake()->image('gallery2.jpg', 600, 600);

        $response = $this->post(route('admin.products.store'), [
            'name' => 'Tenda Dome Arpenaz 4.1',
            'category_id' => $this->category->id,
            'rental_price_per_day' => 75000,
            'late_fee_per_hour' => 10000,
            'main_image' => $mainImage,
            'images' => [$galleryImg1, $galleryImg2],
            'status' => 'aktif',
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $product = Product::where('slug', 'tenda-dome-arpenaz-41')->first();
        $this->assertNotNull($product);
        $this->assertNotNull($product->main_image);
        $this->assertIsArray($product->images);
        $this->assertCount(2, $product->images);

        // Verifikasi accessor gallery_images menggabungkan main_image + images
        $gallery = $product->gallery_images;
        $this->assertCount(3, $gallery);
        $this->assertEquals($product->cover_image_url, $gallery[0]);

        // Verifikasi file tersimpan di storage disk
        Storage::disk('public')->assertExists($product->main_image);
        foreach ($product->images as $imgPath) {
            Storage::disk('public')->assertExists($imgPath);
        }
    }

    public function test_admin_can_delete_selected_gallery_images_on_update(): void
    {
        $this->actingAs($this->admin);

        $disk = Storage::disk('public');
        $mainPath = 'products/main_test.jpg';
        $disk->put($mainPath, 'dummy cover');

        $galPath1 = 'products/gal_1.jpg';
        $galPath2 = 'products/gal_2.jpg';
        $disk->put($galPath1, 'dummy gal 1');
        $disk->put($galPath2, 'dummy gal 2');

        $product = Product::create([
            'client_id' => $this->client->id,
            'category_id' => $this->category->id,
            'name' => 'Carrier Deuter 65L',
            'slug' => 'carrier-deuter-65l',
            'rental_price_per_day' => 50000,
            'late_fee_per_hour' => 5000,
            'main_image' => $mainPath,
            'images' => [$galPath1, $galPath2],
            'status' => 'aktif',
        ]);

        // Update dengan menghapus galPath1
        $response = $this->put(route('admin.products.update', $product->id), [
            'name' => 'Carrier Deuter 65L (Updated)',
            'category_id' => $this->category->id,
            'rental_price_per_day' => 55000,
            'late_fee_per_hour' => 5000,
            'deleted_images' => [$galPath1],
            'status' => 'aktif',
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $fresh = $product->fresh();
        $this->assertCount(1, $fresh->images);
        $this->assertContains($galPath2, $fresh->images);
        $this->assertNotContains($galPath1, $fresh->images);

        // File galPath1 harus terhapus dari storage
        $disk->assertMissing($galPath1);
        $disk->assertExists($galPath2);
    }

    public function test_customer_product_detail_page_renders_interactive_gallery(): void
    {
        $customer = User::create([
            'name' => 'Budi Pendaki',
            'email' => 'budi@pendaki.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'status' => 'aktif',
        ]);
        $this->actingAs($customer);

        $disk = Storage::disk('public');
        $mainPath = 'products/main_tent.jpg';
        $galPath = 'products/gal_tent.jpg';
        $disk->put($mainPath, 'dummy main');
        $disk->put($galPath, 'dummy gal');

        $product = Product::create([
            'client_id' => $this->client->id,
            'category_id' => $this->category->id,
            'name' => 'Sleeping Bag Polar Bulu',
            'slug' => 'sleeping-bag-polar-bulu',
            'rental_price_per_day' => 20000,
            'late_fee_per_hour' => 2000,
            'main_image' => $mainPath,
            'images' => [$galPath],
            'status' => 'aktif',
        ]);

        $response = $this->get(route('customer.product.show', [
            'subdomain' => $this->client->subdomain,
            'product' => $product->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('activeIdx: 0', false);
        $response->assertSee('Sleeping Bag Polar Bulu');
        $response->assertSee('Foto Sebelumnya');
        $response->assertSee('Foto Berikutnya');
    }

    public function test_admin_can_request_subscription_renewal(): void
    {
        $this->actingAs($this->admin);

        // Buat subscription aktif yang sedang berjalan
        Subscription::create([
            'client_id' => $this->client->id,
            'plan_name' => 'starter',
            'billing_cycle' => 'monthly',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonths(2),
            'status' => 'aktif',
            'payment_status' => 'diverifikasi',
            'price' => 299000,
        ]);

        $proof = UploadedFile::fake()->image('transfer_renew.jpg');

        $response = $this->post(route('admin.subscriptions.renew'), [
            'payment_proof' => $proof,
            'notes' => 'Transfer via BCA atas nama Iqo Alfarezel',
        ]);

        $response->assertRedirect(route('admin.subscriptions.index'));
        $response->assertSessionHas('success');

        $newSub = Subscription::where('client_id', $this->client->id)
            ->where('status', 'menunggu_konfirmasi')
            ->first();

        $this->assertNotNull($newSub);
        $this->assertEquals('Starter', $newSub->plan_name);
        $this->assertEquals('menunggu', $newSub->payment_status);
        $this->assertEquals('Transfer via BCA atas nama Iqo Alfarezel', $newSub->notes);
        Storage::disk('public')->assertExists($newSub->payment_proof);
    }

    public function test_admin_can_request_subscription_upgrade(): void
    {
        $this->actingAs($this->admin);

        Subscription::create([
            'client_id' => $this->client->id,
            'plan_name' => 'starter',
            'billing_cycle' => 'monthly',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonths(2),
            'status' => 'aktif',
            'payment_status' => 'diverifikasi',
            'price' => 299000,
        ]);

        $proof = UploadedFile::fake()->image('transfer_upgrade.png');

        $response = $this->post(route('admin.subscriptions.upgrade'), [
            'plan_name' => 'business',
            'payment_proof' => $proof,
            'notes' => 'Upgrade ke Business 6 bulan demi tambah kuota unit',
        ]);

        $response->assertRedirect(route('admin.subscriptions.index'));
        $response->assertSessionHas('success');

        $upgradeSub = Subscription::where('client_id', $this->client->id)
            ->where('status', 'menunggu_konfirmasi')
            ->first();

        $this->assertNotNull($upgradeSub);
        $this->assertEquals('Business', $upgradeSub->plan_name);
        $this->assertEquals('menunggu', $upgradeSub->payment_status);
    }

    public function test_admin_subscriptions_page_renders_successfully(): void
    {
        $this->actingAs($this->admin);

        Subscription::create([
            'client_id' => $this->client->id,
            'plan_name' => 'starter',
            'billing_cycle' => 'monthly',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonths(2),
            'status' => 'aktif',
            'payment_status' => 'diverifikasi',
            'price' => 0,
        ]);

        $response = $this->get(route('admin.subscriptions.index'));

        $response->assertStatus(200);
        $response->assertSee('Starter');
        $response->assertSee('Business');
        $response->assertSee('Professional');
        $response->assertDontSee('reserved_subdomains');
    }

    public function test_owner_verification_of_renewal_accumulates_end_date(): void
    {
        $this->actingAs($this->owner);

        $now = Carbon::now('Asia/Jakarta');
        $initialEndDate = $now->copy()->addDays(20);

        // Subscription aktif
        $activeSub = Subscription::create([
            'client_id' => $this->client->id,
            'plan_name' => 'starter',
            'billing_cycle' => 'monthly',
            'start_date' => $now->copy()->subMonths(2),
            'end_date' => $initialEndDate,
            'status' => 'aktif',
            'payment_status' => 'diverifikasi',
            'price' => 299000,
        ]);

        // Pengajuan perpanjangan (Starter = 3 bulan)
        $pendingRenewal = Subscription::create([
            'client_id' => $this->client->id,
            'plan_name' => 'starter',
            'billing_cycle' => 'monthly',
            'start_date' => $now->copy(),
            'end_date' => $now->copy()->addMonths(3),
            'status' => 'menunggu_konfirmasi',
            'payment_status' => 'menunggu',
            'price' => 299000,
            'payment_proof' => 'subscriptions/proofs/test_renew.jpg',
            'notes' => 'Perpanjang Starter 3 Bulan',
        ]);

        $response = $this->post(route('owner.subscriptions.verify', $pendingRenewal->id));

        $response->assertRedirect(route('owner.subscriptions.index'));
        $response->assertSessionHas('success');

        $verified = $pendingRenewal->fresh();
        $this->assertEquals('aktif', $verified->status);
        $this->assertEquals('diverifikasi', $verified->payment_status);

        // Sisa hari tidak hangus: end_date baru = initialEndDate + 3 bulan
        $expectedEndDate = $initialEndDate->copy()->addMonths(3);
        $this->assertEquals($expectedEndDate->format('Y-m-d'), $verified->end_date->format('Y-m-d'));
    }

    public function test_owner_verification_of_upgrade_resets_full_period_from_today_and_boosts_limits(): void
    {
        $this->actingAs($this->owner);

        $now = Carbon::now('Asia/Jakarta');

        // Subscription aktif Starter
        $activeSub = Subscription::create([
            'client_id' => $this->client->id,
            'plan_name' => 'starter',
            'billing_cycle' => 'monthly',
            'start_date' => $now->copy()->subMonth(),
            'end_date' => $now->copy()->addMonths(2),
            'status' => 'aktif',
            'payment_status' => 'diverifikasi',
            'max_products' => 10,
            'max_users' => 1,
            'price' => 299000,
        ]);

        // Pengajuan Upgrade ke Business (6 bulan, limit 50 produk, 3 admin)
        $pendingUpgrade = Subscription::create([
            'client_id' => $this->client->id,
            'plan_name' => 'business',
            'billing_cycle' => 'monthly',
            'start_date' => $now->copy(),
            'end_date' => $now->copy()->addMonths(6),
            'status' => 'menunggu_konfirmasi',
            'payment_status' => 'menunggu',
            'price' => 599000,
            'payment_proof' => 'subscriptions/proofs/test_upgrade.jpg',
            'notes' => 'Upgrade to Business',
        ]);

        $response = $this->post(route('owner.subscriptions.verify', $pendingUpgrade->id));

        $response->assertRedirect(route('owner.subscriptions.index'));
        $response->assertSessionHas('success');

        // Subscription lama ditandai sebagai diperbarui
        $this->assertEquals('diperbarui', $activeSub->fresh()->status);

        // Subscription baru aktif, mulai hari ini dan durasi penuh 6 bulan (Model Pilihan A)
        $verifiedUpgrade = $pendingUpgrade->fresh();
        $this->assertEquals('aktif', $verifiedUpgrade->status);
        $this->assertEquals('diverifikasi', $verifiedUpgrade->payment_status);
        $this->assertEquals($now->format('Y-m-d'), $verifiedUpgrade->start_date->format('Y-m-d'));

        $expectedEnd = $now->copy()->addMonths(6);
        $this->assertEquals($expectedEnd->format('Y-m-d'), $verifiedUpgrade->end_date->format('Y-m-d'));

        // Kuota limit dinaikkan sesuai konfigurasi Business
        $this->assertEquals(50, $verifiedUpgrade->max_products);
        $this->assertEquals(3, $verifiedUpgrade->max_users);
    }

    public function test_owner_can_reject_subscription_with_reason(): void
    {
        $this->actingAs($this->owner);

        $pending = Subscription::create([
            'client_id' => $this->client->id,
            'plan_name' => 'business',
            'billing_cycle' => 'monthly',
            'start_date' => now(),
            'end_date' => now()->addMonths(6),
            'status' => 'menunggu_konfirmasi',
            'payment_status' => 'menunggu',
            'price' => 599000,
            'payment_proof' => 'subscriptions/proofs/invalid_proof.jpg',
            'notes' => 'Pengajuan ditolak test',
        ]);

        $reason = 'Nominal transfer tidak sesuai dengan harga paket Business.';

        $response = $this->post(route('owner.subscriptions.reject', $pending->id), [
            'rejection_reason' => $reason,
        ]);

        $response->assertRedirect(route('owner.subscriptions.index'));
        $response->assertSessionHas('success');

        $rejected = $pending->fresh();
        $this->assertEquals('ditolak', $rejected->status);
        $this->assertEquals('ditolak', $rejected->payment_status);
        $this->assertEquals($reason, $rejected->rejection_reason);
    }

    public function test_tenant_profile_hero_showcase_persists_to_database_and_is_used_by_catalog(): void
    {
        $this->actingAs($this->admin);

        $featured = Product::create([
            'client_id' => $this->client->id,
            'category_id' => $this->category->id,
            'name' => 'Tenda Eiger Safari 6P',
            'slug' => 'tenda-eiger-safari-6p',
            'rental_price_per_day' => 120000,
            'late_fee_per_hour' => 15000,
            'status' => 'aktif',
        ]);

        $profile = TenantProfile::create([
            'client_id' => $this->client->id,
            'name' => 'Rental Outdoor Explorer',
            'subdomain' => $this->client->subdomain,
            'hero_showcase_mode' => 'illustration',
            'hero_featured_product_id' => null,
        ]);

        // Simpan preferensi hero showcase via form update profil toko
        $response = $this->post(route('admin.settings.profile.update'), [
            'name' => 'Rental Outdoor Explorer Official',
            'hero_showcase_mode' => 'featured',
            'hero_featured_product_id' => $featured->id,
        ]);

        $response->assertSessionHas('success');

        $freshProfile = $profile->fresh();
        $this->assertEquals('featured', $freshProfile->hero_showcase_mode);
        $this->assertEquals($featured->id, $freshProfile->hero_featured_product_id);

        // Verifikasi Livewire ProductCatalog membaca setting hero dari database
        $categories = Category::where('client_id', $this->client->id)->get();
        Livewire::test(ProductCatalog::class, ['client' => $this->client->fresh(), 'categories' => $categories])
            ->assertSee('Tenda Eiger Safari 6P');
    }
}
