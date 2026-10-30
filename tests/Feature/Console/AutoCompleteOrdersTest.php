<?php

namespace Tests\Feature\Console;

use App\Models\Client;
use App\Models\DamageCase;
use App\Models\DamageReport;
use App\Models\EquipmentUnit;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ReturnModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AutoCompleteOrdersTest extends TestCase
{
    use RefreshDatabase;

    public function test_completes_order_meeting_all_criteria(): void
    {
        $client = Client::create(['business_name' => 'Toko B', 'subdomain' => 'tokob']);
        $user = User::factory()->create(['client_id' => $client->id, 'role' => 'customer']);

        $order = Order::create([
            'client_id' => $client->id,
            'user_id' => $user->id,
            'order_number' => 'ORD-COMPLETE-001',
            'rental_start_date' => now()->subDays(5),
            'rental_end_date' => now()->subDays(2),
            'duration_days' => 3,
            'total_amount' => 300000,
            'shipping_address' => 'Alamat Pengiriman',
            'status' => 'dikembalikan',
        ]);

        $return = ReturnModel::create([
            'order_id' => $order->id,
            'return_method' => 'kurir',
            'return_date' => now()->subHours(30),
            'return_status' => 'diterima',
            'late_fee_status' => 'tidak_ada',
            'updated_at' => Carbon::now()->subHours(25), // > 24 jam
        ]);

        $exitCode = Artisan::call('orders:auto-complete');

        $this->assertEquals(0, $exitCode);
        $this->assertEquals('selesai', $order->fresh()->status);
    }

    public function test_does_not_complete_order_with_unpaid_late_fee(): void
    {
        $client = Client::create(['business_name' => 'Toko B', 'subdomain' => 'tokob']);
        $user = User::factory()->create(['client_id' => $client->id, 'role' => 'customer']);

        $order = Order::create([
            'client_id' => $client->id,
            'user_id' => $user->id,
            'order_number' => 'ORD-FINE-001',
            'rental_start_date' => now()->subDays(5),
            'rental_end_date' => now()->subDays(2),
            'duration_days' => 3,
            'total_amount' => 300000,
            'shipping_address' => 'Alamat Pengiriman',
            'status' => 'dikembalikan',
        ]);

        ReturnModel::create([
            'order_id' => $order->id,
            'return_method' => 'kurir',
            'return_date' => now()->subHours(30),
            'return_status' => 'diterima',
            'late_fee_amount' => 50000,
            'late_fee_status' => 'menunggu_pembayaran', // Denda belum dibayar
            'updated_at' => Carbon::now()->subHours(25),
        ]);

        Artisan::call('orders:auto-complete');

        $this->assertEquals('dikembalikan', $order->fresh()->status);
    }

    public function test_does_not_complete_order_with_open_damage_case(): void
    {
        $client = Client::create(['business_name' => 'Toko B', 'subdomain' => 'tokob']);
        $category = \App\Models\Category::create(['client_id' => $client->id, 'name' => 'Lensa', 'slug' => 'lensa']);
        $user = User::factory()->create(['client_id' => $client->id, 'role' => 'customer']);
        $product = Product::create([
            'client_id' => $client->id,
            'category_id' => $category->id,
            'name' => 'Lensa 50mm',
            'slug' => 'lensa-50mm',
            'rental_price_per_day' => 50000,
            'status' => 'tersedia',
        ]);
        $unit = EquipmentUnit::create([
            'client_id' => $client->id,
            'product_id' => $product->id,
            'unit_code' => 'LEN-01',
            'status' => 'rusak',
        ]);

        $order = Order::create([
            'client_id' => $client->id,
            'user_id' => $user->id,
            'order_number' => 'ORD-DAMAGE-001',
            'rental_start_date' => now()->subDays(5),
            'rental_end_date' => now()->subDays(2),
            'duration_days' => 3,
            'total_amount' => 150000,
            'shipping_address' => 'Alamat Pengiriman',
            'status' => 'dikembalikan',
        ]);

        ReturnModel::create([
            'order_id' => $order->id,
            'return_method' => 'kurir',
            'return_date' => now()->subHours(30),
            'return_status' => 'diterima',
            'late_fee_status' => 'tidak_ada',
            'updated_at' => Carbon::now()->subHours(25),
        ]);

        $report = DamageReport::create([
            'order_id' => $order->id,
            'equipment_unit_id' => $unit->id,
            'description' => 'Lensa retak',
            'reported_by' => $user->id,
        ]);

        DamageCase::create([
            'damage_report_id' => $report->id,
            'repair_cost' => 100000,
            'fine_amount' => 0,
            'case_status' => 'terbuka', // Masih terbuka
            'payment_status' => 'belum_dibayar',
        ]);

        Artisan::call('orders:auto-complete');

        $this->assertEquals('dikembalikan', $order->fresh()->status);
    }

    public function test_does_not_complete_order_under_24_hours(): void
    {
        $client = Client::create(['business_name' => 'Toko B', 'subdomain' => 'tokob']);
        $user = User::factory()->create(['client_id' => $client->id, 'role' => 'customer']);

        $order = Order::create([
            'client_id' => $client->id,
            'user_id' => $user->id,
            'order_number' => 'ORD-RECENT-RET-001',
            'rental_start_date' => now()->subDays(5),
            'rental_end_date' => now()->subDay(),
            'duration_days' => 4,
            'total_amount' => 200000,
            'shipping_address' => 'Alamat Pengiriman',
            'status' => 'dikembalikan',
        ]);

        ReturnModel::create([
            'order_id' => $order->id,
            'return_method' => 'kurir',
            'return_date' => now()->subHours(5),
            'return_status' => 'diterima',
            'late_fee_status' => 'tidak_ada',
            'updated_at' => Carbon::now()->subHours(5), // Baru 5 jam yang lalu
        ]);

        Artisan::call('orders:auto-complete');

        $this->assertEquals('dikembalikan', $order->fresh()->status);
    }
}
