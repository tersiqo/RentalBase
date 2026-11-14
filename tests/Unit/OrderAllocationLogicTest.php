<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Client;
use App\Models\ClientPaymentMethod;
use App\Models\EquipmentUnit;
use App\Models\IdentityGuarantee;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemUnit;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * MOD-07 — Master Pesanan & Alokasi Unit (Unit Testing)
 * 
 * Menguji:
 * 1. Relasi pivot OrderItem dan OrderItemUnit
 * 2. Logika accessor getCanReceiveAttribute()
 * 3. Logika accessor getCanReuploadIdentityAttribute()
 * 4. Logika accessor getCanUploadPaymentAttribute()
 * 5. Method syncRentalHoursOnHandover() pada Model Order
 */
class OrderAllocationLogicTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;
    private User $customer;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Client::create([
            'business_name' => 'Baby Rental Studio',
            'subdomain' => 'babyrental',
            'status' => 'aktif',
        ]);

        $this->customer = User::create([
            'name' => 'Zaskia Customer',
            'email' => 'zaskia@example.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        $category = Category::create([
            'client_id' => $this->client->id,
            'name' => 'Stroller',
            'slug' => 'stroller',
        ]);

        $this->product = Product::create([
            'client_id' => $this->client->id,
            'category_id' => $category->id,
            'name' => 'Stroller Nuna',
            'slug' => 'stroller-nuna',
            'rental_price_per_day' => 50000,
            'status' => 'aktif',
        ]);
    }

    private function createDummyOrder(array $attributes = []): Order
    {
        return Order::create(array_merge([
            'client_id' => $this->client->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-' . uniqid(),
            'status' => 'menunggu_verifikasi',
            'rental_start_date' => now()->addDays(1),
            'rental_end_date' => now()->addDays(3),
            'duration_days' => 2,
            'total_amount' => 100000,
            'shipping_address' => 'Jl. Veteran No. 10, Malang',
            'shipping_method' => 'kurir',
        ], $attributes));
    }

    /**
     * UT-ORD-ALLOC-001: Memastikan kontrak relasi antar model OrderItem, OrderItemUnit, dan EquipmentUnit terdefinisi dengan tepat.
     */
    public function test_ut_ord_alloc_001_order_item_and_unit_relationships(): void
    {
        $order = $this->createDummyOrder(['order_number' => 'ORD-ALLOC-REL-01']);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price_per_day' => 50000,
            'subtotal' => 100000,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $orderItem->order());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $orderItem->product());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $orderItem->orderItemUnits());

        $unit = EquipmentUnit::create([
            'client_id' => $this->client->id,
            'product_id' => $this->product->id,
            'unit_code' => 'UNIT-ST-001',
            'status' => 'tersedia',
            'condition' => 'baik',
        ]);

        $orderItemUnit = OrderItemUnit::create([
            'order_item_id' => $orderItem->id,
            'equipment_unit_id' => $unit->id,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $orderItemUnit->orderItem());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $orderItemUnit->equipmentUnit());
        $this->assertEquals($orderItem->id, $orderItemUnit->orderItem->id);
        $this->assertEquals($unit->id, $orderItemUnit->equipmentUnit->id);
    }

    /**
     * UT-ORD-ALLOC-002: Memastikan accessor can_receive bernilai true HANYA saat pesanan berstatus 'dikirim'.
     */
    public function test_ut_ord_alloc_002_can_receive_accessor(): void
    {
        $order = new Order(['status' => 'dikirim']);
        $this->assertTrue($order->can_receive);

        $statusesNotReceivable = ['menunggu_pembayaran', 'verifikasi', 'siap_kirim', 'sedang_disewa', 'selesai', 'dibatalkan'];
        foreach ($statusesNotReceivable as $status) {
            $order->status = $status;
            $this->assertFalse($order->can_receive, "Status {$status} seharusnya tidak dapat konfirmasi penerimaan.");
        }
    }

    /**
     * UT-ORD-ALLOC-003: Memastikan accessor can_reupload_identity bernilai true saat KTP ditolak admin dan status pesanan masih aktif.
     */
    public function test_ut_ord_alloc_003_can_reupload_identity_accessor(): void
    {
        $order = $this->createDummyOrder([
            'order_number' => 'ORD-REUPLOAD-ID-01',
            'status' => 'menunggu_verifikasi',
        ]);

        // Belum ada identityGuarantee
        $this->assertFalse($order->can_reupload_identity);

        // Ada identityGuarantee tapi status masih menunggu atau diverifikasi
        $guarantee = IdentityGuarantee::create([
            'order_id' => $order->id,
            'customer_id' => $this->customer->id,
            'full_name' => 'Zaskia Customer',
            'identity_number' => '3579000011112222',
            'identity_document_image' => 'ktp/sample.jpg',
            'face_image' => 'selfie/sample.jpg',
            'identity_address' => 'Jl. Veteran No. 10, Malang',
            'status' => 'menunggu',
        ]);

        $order->load('identityGuarantee');
        $this->assertFalse($order->can_reupload_identity);

        // KTP ditolak oleh admin
        $guarantee->update(['status' => 'ditolak']);
        $order->load('identityGuarantee');
        $this->assertTrue($order->can_reupload_identity);

        // Pesanan sudah dibatalkan atau selesai tidak boleh reupload
        $order->status = 'dibatalkan';
        $this->assertFalse($order->can_reupload_identity);
    }

    /**
     * UT-ORD-ALLOC-004: Memastikan accessor can_upload_payment memvalidasi status pesanan, verifikasi KTP, dan belum adanya pembayaran terverifikasi.
     */
    public function test_ut_ord_alloc_004_can_upload_payment_accessor(): void
    {
        $order = $this->createDummyOrder([
            'order_number' => 'ORD-PAY-UPLOAD-01',
            'status' => 'menunggu_pembayaran',
        ]);

        // Tanpa jaminan identitas, default true jika menunggu pembayaran
        $this->assertTrue($order->can_upload_payment);

        // Ada jaminan identitas tapi belum diverifikasi
        $guarantee = IdentityGuarantee::create([
            'order_id' => $order->id,
            'customer_id' => $this->customer->id,
            'full_name' => 'Zaskia Customer',
            'identity_number' => '3579000011112222',
            'identity_document_image' => 'ktp/sample.jpg',
            'face_image' => 'selfie/sample.jpg',
            'identity_address' => 'Jl. Veteran No. 10, Malang',
            'status' => 'menunggu',
        ]);
        $order->load('identityGuarantee');
        $this->assertFalse($order->can_upload_payment);

        // Jaminan identitas telah diverifikasi
        $guarantee->update(['status' => 'diverifikasi']);
        $order->load('identityGuarantee');
        $this->assertTrue($order->can_upload_payment);

        // Jika sudah ada pembayaran yang berstatus terverifikasi, tidak boleh upload lagi
        $cpm = ClientPaymentMethod::create([
            'client_id' => $this->client->id,
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_holder' => 'Baby Rental',
            'is_active' => true,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'client_payment_method_id' => $cpm->id,
            'amount' => 100000,
            'payment_proof' => 'payments/proof.jpg',
            'status' => 'terverifikasi',
        ]);
        $order->unsetRelation('payments');
        $this->assertFalse($order->can_upload_payment);
    }

    /**
     * UT-ORD-ALLOC-005: Memastikan syncRentalHoursOnHandover menyesuaikan tanggal sewa saat serah terima barang terlambat dari rencana awal.
     */
    public function test_ut_ord_alloc_005_sync_rental_hours_on_handover_adjusts_dates(): void
    {
        $originalStart = Carbon::now()->subDay()->setTime(10, 0, 0);
        $originalEnd = Carbon::now()->addDays(2)->setTime(10, 0, 0);

        $order = $this->createDummyOrder([
            'order_number' => 'ORD-SYNC-HO-01',
            'status' => 'siap_kirim',
            'rental_start_date' => $originalStart,
            'rental_end_date' => $originalEnd,
            'duration_days' => 3,
        ]);

        $handoverTime = Carbon::now()->setTime(14, 30, 0);
        $synced = $order->syncRentalHoursOnHandover($handoverTime);

        $this->assertTrue($synced);
        $order->refresh();

        $this->assertEquals($handoverTime->copy()->second(0)->toDateTimeString(), $order->rental_start_date->toDateTimeString());
        $this->assertEquals($handoverTime->copy()->second(0)->addDays(2)->toDateTimeString(), $order->rental_end_date->toDateTimeString());
    }

    /**
     * UT-ORD-ALLOC-006: Memastikan syncRentalHoursOnHandover mengembalikan false jika serah terima dilakukan sebelum rencana awal sewa.
     */
    public function test_ut_ord_alloc_006_sync_rental_hours_on_handover_does_nothing_if_earlier(): void
    {
        $futureStart = Carbon::now()->addDays(2)->setTime(10, 0, 0);
        $futureEnd = Carbon::now()->addDays(5)->setTime(10, 0, 0);

        $order = $this->createDummyOrder([
            'order_number' => 'ORD-SYNC-HO-02',
            'status' => 'siap_kirim',
            'rental_start_date' => $futureStart,
            'rental_end_date' => $futureEnd,
            'duration_days' => 3,
        ]);

        $handoverTime = Carbon::now()->setTime(9, 0, 0);
        $synced = $order->syncRentalHoursOnHandover($handoverTime);

        $this->assertFalse($synced);
    }
}
