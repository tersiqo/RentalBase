<?php

namespace Tests\Feature\Customer;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Order;
use App\Models\ReturnModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * IT-MOD-08 — Pembayaran dan Refund
 * 
 * Pengujian Integrasi:
 * Customer Upload Bukti Bayar Denda Keterlambatan (POST /my-orders/{order}/pay-late-fee)
 */
class CustomerLateFeePaymentTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;
    private User $customer;
    private Order $order;
    private ReturnModel $returnModel;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->client = Client::create([
            'business_name' => 'Baby Rental Studio',
            'subdomain' => 'babyrental',
            'status' => 'aktif',
        ]);

        $this->customer = User::create([
            'name' => 'Zaskia Customer',
            'email' => 'zaskia@customer.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        $this->order = Order::create([
            'client_id' => $this->client->id,
            'user_id' => $this->customer->id,
            'order_number' => 'ORD-LATEFEE-001',
            'status' => 'menunggu_pembayaran_denda',
            'rental_start_date' => now()->subDays(5),
            'rental_end_date' => now()->subDays(2),
            'duration_days' => 3,
            'total_amount' => 300000,
            'shipping_address' => 'Jl. Soekarno Hatta No. 15, Malang',
            'shipping_method' => 'kurir',
        ]);

        $this->returnModel = ReturnModel::create([
            'order_id' => $this->order->id,
            'return_method' => 'kurir',
            'return_date' => now()->subDay(),
            'late_hours' => 5,
            'calculated_late_fee' => 50000,
            'late_fee_amount' => 50000,
            'late_fee_status' => 'menunggu_pembayaran',
            'return_status' => 'diterima',
        ]);
    }

    /**
     * IT-08-018: Memastikan customer pemilik pesanan sukses mengunggah bukti pembayaran denda keterlambatan.
     */
    public function test_it_08_018_customer_can_successfully_upload_late_fee_payment_proof(): void
    {
        $file = UploadedFile::fake()->image('bukti_transfer_denda.jpg', 600, 600);

        $response = $this->actingAs($this->customer)
            ->from(url('/my-orders/' . $this->order->id))
            ->post(route('customer.orders.pay-late-fee', $this->order), [
                'late_fee_proof' => $file,
            ]);

        $response->assertRedirect(url('/my-orders/' . $this->order->id));
        $response->assertSessionHas('success');

        $this->returnModel->refresh();
        $this->assertEquals('menunggu_verifikasi', $this->returnModel->late_fee_status);
        $this->assertNotNull($this->returnModel->late_fee_proof);

        Storage::disk('public')->assertExists($this->returnModel->late_fee_proof);

        $this->assertDatabaseHas('activity_logs', [
            'client_id' => $this->client->id,
            'user_id' => $this->customer->id,
            'action' => 'late_fee_payment_uploaded',
            'module' => 'returns',
        ]);
    }

    /**
     * IT-08-019: Memastikan sistem menolak unggah bukti denda jika pesanan tidak dalam status menunggu denda.
     */
    public function test_it_08_019_upload_fails_when_order_cannot_pay_late_fee(): void
    {
        $this->order->update(['status' => 'selesai']);

        $file = UploadedFile::fake()->image('bukti_transfer_denda.jpg');

        $response = $this->actingAs($this->customer)
            ->from(url('/my-orders/' . $this->order->id))
            ->post(route('customer.orders.pay-late-fee', $this->order), [
                'late_fee_proof' => $file,
            ]);

        $response->assertRedirect(url('/my-orders/' . $this->order->id));
        $response->assertSessionHas('error', 'Pembayaran denda tidak dapat diproses untuk pesanan ini.');

        $this->returnModel->refresh();
        $this->assertEquals('menunggu_pembayaran', $this->returnModel->late_fee_status);
    }

    /**
     * IT-08-020: Memastikan validasi request menolak jika bukti pembayaran kosong atau berformat tidak didukung.
     */
    public function test_it_08_020_validation_rejects_empty_or_invalid_file_format(): void
    {
        // 1. File kosong
        $responseEmpty = $this->actingAs($this->customer)
            ->post(route('customer.orders.pay-late-fee', $this->order), []);
        $responseEmpty->assertSessionHasErrors(['late_fee_proof']);

        // 2. Format file tidak didukung (misal file teks .txt)
        $invalidFile = UploadedFile::fake()->create('dokumen.txt', 100, 'text/plain');
        $responseInvalid = $this->actingAs($this->customer)
            ->post(route('customer.orders.pay-late-fee', $this->order), [
                'late_fee_proof' => $invalidFile,
            ]);
        $responseInvalid->assertSessionHasErrors(['late_fee_proof']);
    }

    /**
     * IT-08-021: Memastikan customer lain dilarang mengunggah bukti denda untuk pesanan milik orang lain (403 Forbidden).
     */
    public function test_it_08_021_customer_cannot_upload_late_fee_for_another_users_order(): void
    {
        $otherCustomer = User::create([
            'name' => 'Other Customer',
            'email' => 'other@customer.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        $file = UploadedFile::fake()->image('bukti_transfer_denda.jpg');

        $response = $this->actingAs($otherCustomer)
            ->post(route('customer.orders.pay-late-fee', $this->order), [
                'late_fee_proof' => $file,
            ]);

        $response->assertStatus(403);
    }

    /**
     * IT-08-022: Memastikan pengguna tanpa login (guest) dicegat saat mencoba mengakses endpoint bayar denda.
     */
    public function test_it_08_022_unauthenticated_user_cannot_upload_late_fee_proof(): void
    {
        $file = UploadedFile::fake()->image('bukti_transfer_denda.jpg');

        $response = $this->post(route('customer.orders.pay-late-fee', $this->order), [
            'late_fee_proof' => $file,
        ]);

        $response->assertRedirect(route('login'));
    }
}
