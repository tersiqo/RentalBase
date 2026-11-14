<?php

namespace Tests\Unit;

use App\Models\Client;
use App\Models\Order;
use App\Models\ReturnModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * UT-MOD-10 — Retur Barang & Denda Keterlambatan (Unit Testing)
 * 
 * Menguji:
 * 1. Relasi model ReturnModel ke Order (belongsTo)
 * 2. Field denda keterlambatan (calculated_late_fee, late_fee_amount, late_fee_proof, late_fee_status)
 * 3. Casting atribut datetime dan desimal pada model ReturnModel
 * 4. Nilai bawaan (default value) status denda dan status pengembalian
 * 5. Relasi inverse model Order ke ReturnModel (hasOne returnModel)
 */
class ReturnLateFeeLogicTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;
    private User $customer;
    private Order $order;

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
            'email' => 'zaskia.customer@example.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        $this->order = Order::create([
            'client_id' => $this->client->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-RETURN-001',
            'status' => 'menunggu_pembayaran_denda',
            'rental_start_date' => now()->subDays(5),
            'rental_end_date' => now()->subDays(2),
            'duration_days' => 3,
            'total_amount' => 270000,
            'shipping_address' => 'Jl. Soekarno Hatta No. 20, Malang',
            'shipping_method' => 'kurir',
        ]);
    }

    /**
     * UT-RET-FEE-001: Memastikan model ReturnModel memiliki relasi belongsTo ke model Order.
     */
    public function test_ut_ret_fee_001_return_belongs_to_order_relationship(): void
    {
        $return = ReturnModel::create([
            'order_id' => $this->order->id,
            'return_method' => 'kurir',
            'return_date' => now(),
            'return_status' => 'diterima',
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $return->order());
        $this->assertEquals($this->order->id, $return->order->id);
    }

    /**
     * UT-RET-FEE-002: Memastikan field denda keterlambatan (calculated_late_fee, late_fee_amount, late_fee_proof, status) tersimpan akurat.
     */
    public function test_ut_ret_fee_002_return_late_fee_and_proof_attributes(): void
    {
        $return = ReturnModel::create([
            'order_id' => $this->order->id,
            'return_method' => 'kurir',
            'courier_name' => 'J&T Express',
            'tracking_number' => 'JT-RET-998811',
            'return_date' => now(),
            'late_hours' => 6,
            'calculated_late_fee' => 60000,
            'late_fee_amount' => 50000, // Diskon denda disesuaikan admin
            'late_fee_status' => 'menunggu_verifikasi',
            'late_fee_proof' => 'returns/bukti_denda_123.jpg',
            'return_status' => 'diterima',
            'notes' => 'Terlambat 6 jam karena kendala kurir',
        ]);

        $this->assertEquals(6, $return->late_hours);
        $this->assertEquals('60000.00', $return->calculated_late_fee);
        $this->assertEquals('50000.00', $return->late_fee_amount);
        $this->assertEquals('menunggu_verifikasi', $return->late_fee_status);
        $this->assertEquals('returns/bukti_denda_123.jpg', $return->late_fee_proof);
        $this->assertEquals('Terlambat 6 jam karena kendala kurir', $return->notes);
    }

    /**
     * UT-RET-FEE-003: Memastikan casting atribut return_date ke Carbon dan kalkulasi denda ke format desimal.
     */
    public function test_ut_ret_fee_003_return_casts_attributes(): void
    {
        $return = ReturnModel::create([
            'order_id' => $this->order->id,
            'return_method' => 'ambil_toko',
            'return_date' => '2026-11-13 16:30:00',
            'calculated_late_fee' => '75000.50',
            'late_fee_amount' => '75000.50',
            'late_fee_status' => 'menunggu_pembayaran',
        ]);

        $this->assertInstanceOf(Carbon::class, $return->return_date);
        $this->assertEquals('2026-11-13 16:30:00', $return->return_date->toDateTimeString());
        $this->assertIsString($return->calculated_late_fee);
        $this->assertEquals('75000.50', $return->calculated_late_fee);
        $this->assertEquals('75000.50', $return->late_fee_amount);
    }

    /**
     * UT-RET-FEE-004: Memastikan nilai default atribut denda dan retur sesuai standar bisnis saat awal dibuat.
     */
    public function test_ut_ret_fee_004_return_default_statuses(): void
    {
        $return = ReturnModel::create([
            'order_id' => $this->order->id,
            'return_method' => 'ambil_toko',
            'return_date' => now(),
        ]);

        $this->assertEquals('tidak_ada', $return->late_fee_status);
        $this->assertEquals('diajukan', $return->return_status);
        $this->assertEquals(0, $return->late_hours);
        $this->assertEquals('0.00', $return->calculated_late_fee);
        $this->assertEquals('0.00', $return->late_fee_amount);
        $this->assertNull($return->late_fee_proof);
    }

    /**
     * UT-RET-FEE-005: Memastikan relasi inverse Order hasOne ke model ReturnModel terhubung presisi.
     */
    public function test_ut_ret_fee_005_order_has_one_return_model_relationship(): void
    {
        $return = ReturnModel::create([
            'order_id' => $this->order->id,
            'return_method' => 'kurir',
            'return_date' => now(),
            'return_status' => 'diterima',
            'late_fee_status' => 'lunas',
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOne::class, $this->order->returnModel());
        $this->assertEquals($return->id, $this->order->returnModel->id);
    }
}
