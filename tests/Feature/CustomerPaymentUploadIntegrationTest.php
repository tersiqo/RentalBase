<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientPaymentMethod;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerPaymentUploadIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function createOrderFixture(): array
    {
        $client = Client::create([
            'business_name' => 'Kamera Studio Pro',
            'subdomain' => 'kamerastudio',
            'status' => 'aktif',
        ]);

        $paymentMethod = ClientPaymentMethod::create([
            'client_id' => $client->id,
            'bank_name' => 'BCA',
            'account_number' => '8881234567',
            'account_holder' => 'Kamera Studio Pro',
            'is_active' => true,
        ]);

        $customer = User::factory()->create([
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        $order = Order::create([
            'client_id' => $client->id,
            'user_id' => $customer->id,
            'order_number' => 'ORD-PAY-TEST-' . uniqid(),
            'status' => 'menunggu_pembayaran',
            'rental_start_date' => now()->addDay(),
            'rental_end_date' => now()->addDays(3),
            'duration_days' => 2,
            'total_amount' => 500000,
            'shipping_method' => 'kurir',
            'shipping_address' => 'Jl. Ijen No. 10, Malang',
        ]);

        return [$client, $paymentMethod, $customer, $order];
    }

    public function test_it_08_001_customer_can_upload_payment_proof_for_own_order(): void
    {
        Storage::fake('public');
        [$client, $paymentMethod, $customer, $order] = $this->createOrderFixture();

        $file = UploadedFile::fake()->image('bukti_transfer.jpg', 600, 800)->size(500);

        $response = $this->actingAs($customer)
            ->from("/my-orders/{$order->id}")
            ->post("/my-orders/{$order->id}/payment", [
                'payment_proof' => $file,
                'client_payment_method_id' => $paymentMethod->id,
            ]);

        $response->assertRedirect("/my-orders/{$order->id}");
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'client_payment_method_id' => $paymentMethod->id,
            'amount' => 500000,
            'status' => 'menunggu_konfirmasi',
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'menunggu_verifikasi',
        ]);
    }

    public function test_customer_cannot_upload_payment_for_other_customer_order(): void
    {
        Storage::fake('public');
        [$client, $paymentMethod, $customer, $order] = $this->createOrderFixture();

        $otherCustomer = User::factory()->create([
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        $file = UploadedFile::fake()->image('bukti_iseng.jpg', 600, 800);

        $response = $this->actingAs($otherCustomer)
            ->post("/my-orders/{$order->id}/payment", [
                'payment_proof' => $file,
                'client_payment_method_id' => $paymentMethod->id,
            ]);

        $response->assertStatus(403);
    }

    public function test_payment_proof_upload_validates_file_requirement(): void
    {
        [$client, $paymentMethod, $customer, $order] = $this->createOrderFixture();

        $response = $this->actingAs($customer)
            ->from("/my-orders/{$order->id}")
            ->post("/my-orders/{$order->id}/payment", [
                'payment_proof' => '',
                'client_payment_method_id' => $paymentMethod->id,
            ]);

        $response->assertSessionHasErrors('payment_proof');
    }
}
