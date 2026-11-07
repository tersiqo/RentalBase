<?php

namespace Tests\Unit;

use App\Models\Client;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPaymentMethodTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function createTenantWithAdmin(string $subdomain = 'rentalsukses'): array
    {
        $client = Client::create([
            'business_name' => 'Rental Sukses ' . $subdomain,
            'subdomain' => $subdomain,
            'status' => 'aktif',
        ]);

        $admin = User::factory()->create([
            'client_id' => $client->id,
            'role' => 'admin_rental',
            'status' => 'aktif',
        ]);

        $client->update(['primary_admin_id' => $admin->id]);

        return [$client, $admin];
    }

    public function test_ut_04_009_admin_payment_methods_bank_and_qris_management(): void
    {
        [$client, $admin] = $this->createTenantWithAdmin('toko1');

        $response = $this->actingAs($admin)->get('/admin/settings/payments');

        $response->assertStatus(200);
        $response->assertViewHas('paymentMethods');
        $response->assertSee('Metode Pembayaran');
    }

    public function test_admin_can_create_bank_transfer_payment_method(): void
    {
        [$client, $admin] = $this->createTenantWithAdmin('toko2');

        $response = $this->actingAs($admin)->post('/admin/settings/payments', [
            'type' => 'bank_transfer',
            'bank_name' => 'BCA',
            'account_number' => '8830123456',
            'account_name' => 'PT Rental Sukses',
            'is_active' => true,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('payment_methods', [
            'client_id' => $client->id,
            'type' => 'bank_transfer',
            'bank_name' => 'BCA',
            'account_number' => '8830123456',
            'account_name' => 'PT Rental Sukses',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_create_qris_payment_method_with_barcode_upload(): void
    {
        Storage::fake('public');
        [$client, $admin] = $this->createTenantWithAdmin('toko3');

        $file = UploadedFile::fake()->image('qris-barcode.png', 400, 400);

        $response = $this->actingAs($admin)->post('/admin/settings/payments', [
            'type' => 'qris',
            'merchant_name' => 'Rental Sukses QRIS Official',
            'qris_image' => $file,
            'is_active' => true,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('payment_methods', [
            'client_id' => $client->id,
            'type' => 'qris',
            'qris_merchant_name' => 'Rental Sukses QRIS Official',
            'is_active' => true,
        ]);

        $pm = PaymentMethod::where('client_id', $client->id)->where('type', 'qris')->first();
        $this->assertNotNull($pm->qris_image_path);
    }

    public function test_admin_can_toggle_payment_method_status(): void
    {
        [$client, $admin] = $this->createTenantWithAdmin('toko4');

        $pm = PaymentMethod::create([
            'client_id' => $client->id,
            'type' => 'bank_transfer',
            'bank_name' => 'Mandiri',
            'account_number' => '1420009999',
            'account_name' => 'Rental Sukses Mandiri',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post("/admin/settings/payments/{$pm->id}/toggle");

        $response->assertRedirect();

        $pm->refresh();
        $this->assertFalse((bool) $pm->is_active);
    }

    public function test_admin_can_update_existing_payment_method(): void
    {
        [$client, $admin] = $this->createTenantWithAdmin('toko5');

        $pm = PaymentMethod::create([
            'client_id' => $client->id,
            'type' => 'bank_transfer',
            'bank_name' => 'BNI',
            'account_number' => '0234567890',
            'account_name' => 'Rekening Lama',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put("/admin/settings/payments/{$pm->id}", [
            'type' => 'bank_transfer',
            'bank_name' => 'BNI',
            'account_number' => '0999999999',
            'account_name' => 'Rekening Baru Terupdate',
            'is_active' => true,
        ]);

        $response->assertRedirect();

        $pm->refresh();
        $this->assertEquals('0999999999', $pm->account_number);
        $this->assertEquals('Rekening Baru Terupdate', $pm->account_name);
    }

    public function test_deleting_payment_method_uses_soft_deletes_to_preserve_history(): void
    {
        [$client, $admin] = $this->createTenantWithAdmin('toko6');

        $pm = PaymentMethod::create([
            'client_id' => $client->id,
            'type' => 'bank_transfer',
            'bank_name' => 'BRI',
            'account_number' => '001122334455',
            'account_name' => 'Rental Sukses BRI',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->delete("/admin/settings/payments/{$pm->id}");

        $response->assertRedirect();

        $this->assertSoftDeleted('payment_methods', ['id' => $pm->id]);
    }

    public function test_tenant_isolation_prevents_unauthorized_cross_tenant_access(): void
    {
        [$clientA, $adminA] = $this->createTenantWithAdmin('tokoA');
        [$clientB, $adminB] = $this->createTenantWithAdmin('tokoB');

        $pmB = PaymentMethod::create([
            'client_id' => $clientB->id,
            'type' => 'bank_transfer',
            'bank_name' => 'BCA',
            'account_number' => '777888999',
            'account_name' => 'Toko B',
            'is_active' => true,
        ]);

        // Admin A mencoba menghapus rekening Toko B -> harus 403 Forbidden
        $responseDelete = $this->actingAs($adminA)->delete("/admin/settings/payments/{$pmB->id}");
        $responseDelete->assertStatus(403);

        // Admin A mencoba toggle rekening Toko B -> harus 403 Forbidden
        $responseToggle = $this->actingAs($adminA)->post("/admin/settings/payments/{$pmB->id}/toggle");
        $responseToggle->assertStatus(403);
    }
}

