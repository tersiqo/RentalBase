<?php

namespace Tests\Feature\Storage;

use App\Models\Category;
use App\Models\Client;
use App\Models\EquipmentUnit;
use App\Models\IdentityGuarantee;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemUnit;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\StorageHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StorageStandardizationTest extends TestCase
{
    use RefreshDatabase;

    protected Client $client;
    protected User $admin;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->client = Client::create([
            'business_name' => 'Kamera Store Supabase',
            'subdomain' => 'kamerastore',
            'status' => 'aktif',
        ]);

        $this->admin = User::create([
            'client_id' => $this->client->id,
            'name' => 'Admin Kamera',
            'email' => 'admin@kamerastore.test',
            'password' => bcrypt('password'),
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        $this->customer = User::create([
            'name' => 'Customer Supabase',
            'email' => 'cust@supabase.test',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        \App\Models\ClientPaymentMethod::create([
            'client_id' => $this->client->id,
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_holder' => 'Kamera Store',
            'is_active' => true,
        ]);
    }

    public function test_storage_url_smart_resolver_behaviors(): void
    {
        // 1. Null / Empty returns null
        $this->assertNull(storage_url(null));
        $this->assertNull(storage_url(''));

        // 2. Full URL returns as-is
        $extUrl = 'https://supabase.co/storage/v1/object/public/rentalbase-storage/sample.jpg';
        $this->assertEquals($extUrl, storage_url($extUrl));

        // 3. Remote / Non-local path uses Storage::url
        $expectedUrl = Storage::disk(config('filesystems.default', 'public'))->url('identity_guarantees/remote.jpg');
        $this->assertEquals($expectedUrl, storage_url('identity_guarantees/remote.jpg'));
    }

    public function test_customer_checkout_uploads_identity_to_configured_filesystem_disk(): void
    {
        Storage::fake(config('filesystems.default', 'public'));

        $category = Category::create([
            'client_id' => $this->client->id,
            'name' => 'Kamera',
            'slug' => 'kamera',
        ]);

        $product = Product::create([
            'client_id' => $this->client->id,
            'category_id' => $category->id,
            'name' => 'Lumix S5',
            'slug' => 'lumix-s5',
            'rental_price_per_day' => 150000,
            'late_fee_per_hour' => 15000,
            'status' => 'aktif',
        ]);

        $unit = EquipmentUnit::create([
            'client_id' => $this->client->id,
            'product_id' => $product->id,
            'unit_code' => 'LMX-001',
            'condition' => 'baik',
            'status' => 'tersedia',
        ]);

        $startDate = now()->format('Y-m-d');
        $endDate = now()->addDays(2)->format('Y-m-d');
        $hash = md5($product->id . $startDate . $endDate);

        $cart = [
            $hash => [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->rental_price_per_day,
                'deposit_fee' => 0,
                'main_image' => 'products/lumix.jpg',
                'start_date' => $startDate,
                'end_date' => $endDate,
                'duration_days' => 2,
                'quantity' => 1,
                'subtotal' => 300000,
            ]
        ];

        $ktpFile = UploadedFile::fake()->image('ktp.jpg');
        $selfieFile = UploadedFile::fake()->image('selfie.jpg');

        $response = $this->actingAs($this->customer)
            ->withSession(['cart' => $cart])
            ->post(route('customer.checkout.store', ['subdomain' => $this->client->subdomain]), [
                'selected_hashes' => [$hash],
                'items' => [
                    $hash => ['quantity' => 1],
                ],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'shipping_address' => 'Jl. Supabase No. 1 Malang',
                'shipping_method' => 'ambil_toko',
                'full_name' => 'Budi Santoso',
                'identity_number' => '3573019999990001',
                'identity_address' => 'Jl. Supabase No. 1 Malang',
                'identity_document_image' => $ktpFile,
                'face_image' => $selfieFile,
                'terms' => '1',
            ]);

        $order = Order::where('user_id', $this->customer->id)->latest()->first();
        $this->assertNotNull($order);

        $guarantee = $order->identityGuarantee;
        $this->assertNotNull($guarantee);

        // Pastikan file tersimpan pada configured disk
        Storage::disk(config('filesystems.default', 'public'))->assertExists($guarantee->identity_document_image);
        Storage::disk(config('filesystems.default', 'public'))->assertExists($guarantee->face_image);

        // Pastikan model accessors mengembalikan URL yang valid
        $this->assertNotEmpty($guarantee->identity_document_url);
        $this->assertNotEmpty($guarantee->face_image_url);
    }

    public function test_customer_payment_upload_uses_configured_filesystem_disk(): void
    {
        Storage::fake(config('filesystems.default', 'public'));

        $order = Order::create([
            'client_id' => $this->client->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-PAY-TEST',
            'total_amount' => 250000,
            'rental_start_date' => now()->addDay(),
            'rental_end_date' => now()->addDays(3),
            'duration_days' => 2,
            'shipping_address' => 'Jl. Bunga Melati',
            'shipping_method' => 'ambil_toko',
            'status' => 'menunggu_pembayaran',
        ]);

        $proofFile = UploadedFile::fake()->image('bukti-transfer.jpg');

        $response = $this->actingAs($this->customer)
            ->post("/my-orders/{$order->id}/payment", [
                'payment_proof' => $proofFile,
            ]);

        $response->assertSessionHas('success');

        $payment = Payment::where('order_id', $order->id)->latest()->first();
        $this->assertNotNull($payment);

        Storage::disk(config('filesystems.default', 'public'))->assertExists($payment->payment_proof);
        $this->assertNotEmpty($payment->payment_proof_url);
    }

    public function test_condition_check_upload_uses_configured_filesystem_disk(): void
    {
        Storage::fake(config('filesystems.default', 'public'));

        $category = Category::create([
            'client_id' => $this->client->id,
            'name' => 'Lensa',
            'slug' => 'lensa',
        ]);

        $product = Product::create([
            'client_id' => $this->client->id,
            'category_id' => $category->id,
            'name' => 'Lensa 50mm',
            'slug' => 'lensa-50mm',
            'rental_price_per_day' => 50000,
            'late_fee_per_hour' => 5000,
            'status' => 'aktif',
        ]);

        $unit = EquipmentUnit::create([
            'client_id' => $this->client->id,
            'product_id' => $product->id,
            'unit_code' => 'LNS-001',
            'condition' => 'baik',
            'status' => 'tersedia',
        ]);

        $order = Order::create([
            'client_id' => $this->client->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-COND-TEST',
            'total_amount' => 100000,
            'rental_start_date' => now()->addDay(),
            'rental_end_date' => now()->addDays(3),
            'duration_days' => 2,
            'shipping_address' => 'Jl. Lensa',
            'shipping_method' => 'ambil_toko',
            'status' => 'siap_kirim',
        ]);

        $checkFile = UploadedFile::fake()->image('kondisi-lensa.jpg');

        $response = $this->actingAs($this->admin)
            ->post('/admin/condition-checks', [
                'order_id' => $order->id,
                'equipment_unit_id' => $unit->id,
                'check_type' => 'sebelum_serah',
                'condition' => 'baik',
                'notes' => 'Optik bersih tanpa jamur',
                'image' => $checkFile,
            ]);

        $response->assertSessionHas('success');

        $check = \App\Models\ConditionCheck::where('equipment_unit_id', $unit->id)->first();
        $this->assertNotNull($check);

        Storage::disk(config('filesystems.default', 'public'))->assertExists($check->image);
        $this->assertNotEmpty($check->first_image_url);
    }

    public function test_storage_helper_delete_removes_file_safely(): void
    {
        $diskName = config('filesystems.default', 'public');
        Storage::fake($diskName);
        Storage::fake('public');

        $path = 'dummy/test-file.jpg';
        Storage::disk($diskName)->put($path, 'sample-data');
        Storage::disk($diskName)->assertExists($path);

        StorageHelper::delete($path);
        Storage::disk($diskName)->assertMissing($path);
    }

    public function test_owner_tenant_update_stores_logo_to_configured_disk(): void
    {
        $diskName = config('filesystems.default', 'public');
        Storage::fake($diskName);
        Storage::fake('public');

        $owner = User::create([
            'name' => 'Super Owner',
            'email' => 'owner@platform.test',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'status' => 'aktif',
        ]);

        $logoFile = UploadedFile::fake()->image('new-tenant-logo.png');

        $response = $this->actingAs($owner)
            ->put("/owner/tenants/{$this->client->id}", [
                'business_name' => 'Kamera Store Updated',
                'subdomain' => 'kamerastore',
                'description' => 'Toko rental kamera terpercaya',
                'theme_color' => '#3B82F6',
                'admin_name' => 'Admin Kamera',
                'admin_email' => 'admin@kamerastore.test',
                'admin_phone' => '081234567890',
                'logo' => $logoFile,
            ]);

        $response->assertSessionHas('success');

        $updatedClient = $this->client->fresh();
        $this->assertNotNull($updatedClient->logo);

        Storage::disk($diskName)->assertExists($updatedClient->logo);
        $this->assertNotEmpty($updatedClient->logo_url);
    }
}
