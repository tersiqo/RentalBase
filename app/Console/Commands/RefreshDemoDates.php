<?php

namespace App\Console\Commands;

use App\Models\ClientRegistration;
use App\Models\Order;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RefreshDemoDates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh dynamic timestamps for RentalBase master demo orders, registrations, and subscriptions';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Refreshing RentalBase demo timestamps relative to Carbon::now()...');
        $now = Carbon::now();

        // 1. Menunggu Pembayaran (*01) - 25 minutes ago
        Order::where('order_number', 'LIKE', '%01')->update([
            'created_at' => $now->copy()->subMinutes(25),
            'updated_at' => $now->copy()->subMinutes(25),
            'rental_start_date' => $now->copy()->addDays(2),
            'rental_end_date' => $now->copy()->addDays(5),
            'status' => 'menunggu_pembayaran',
        ]);

        // 2. Menunggu Verifikasi (*02) - 1 hour ago
        Order::where('order_number', 'LIKE', '%02')->update([
            'created_at' => $now->copy()->subHours(1),
            'updated_at' => $now->copy()->subHours(1),
            'rental_start_date' => $now->copy()->addDays(1),
            'rental_end_date' => $now->copy()->addDays(3),
            'status' => 'menunggu_verifikasi',
        ]);

        // 3. Siap Kirim (*03) - 3 hours ago
        Order::where('order_number', 'LIKE', '%03')->update([
            'created_at' => $now->copy()->subHours(4),
            'updated_at' => $now->copy()->subHours(3),
            'rental_start_date' => $now->copy()->addDays(1),
            'rental_end_date' => $now->copy()->addDays(4),
            'status' => 'siap_kirim',
        ]);

        // 4. Dikirim (*04) - 1 hour ago
        Order::where('order_number', 'LIKE', '%04')->update([
            'created_at' => $now->copy()->subHours(5),
            'updated_at' => $now->copy()->subHours(1),
            'rental_start_date' => $now->copy()->startOfDay(),
            'rental_end_date' => $now->copy()->addDays(3),
            'status' => 'dikirim',
        ]);

        // 5. Sedang Disewa (*05) - Started yesterday, ends in 2 days
        Order::where('order_number', 'LIKE', '%05')->update([
            'created_at' => $now->copy()->subDays(2),
            'updated_at' => $now->copy()->subDays(1),
            'rental_start_date' => $now->copy()->subDays(1),
            'rental_end_date' => $now->copy()->addDays(2),
            'status' => 'sedang_disewa',
        ]);

        // 6. Dikembalikan (*06) - Returned 1 hour ago, waiting for inspection
        Order::where('order_number', 'LIKE', '%06')->update([
            'created_at' => $now->copy()->subDays(4),
            'updated_at' => $now->copy()->subHours(1),
            'rental_start_date' => $now->copy()->subDays(3),
            'rental_end_date' => $now->copy()->subHours(1),
            'status' => 'dikembalikan',
        ]);

        // 7. Menunggu Pembayaran Denda (*07) - Returned with fine 3 hours ago
        Order::where('order_number', 'LIKE', '%07')->update([
            'created_at' => $now->copy()->subDays(6),
            'updated_at' => $now->copy()->subHours(3),
            'rental_start_date' => $now->copy()->subDays(5),
            'rental_end_date' => $now->copy()->subDays(1),
            'status' => 'menunggu_pembayaran_denda',
        ]);

        // 8. Damage Case (*08) - Returned with damage report 4 hours ago
        Order::where('order_number', 'LIKE', '%08')->update([
            'created_at' => $now->copy()->subDays(5),
            'updated_at' => $now->copy()->subHours(4),
            'rental_start_date' => $now->copy()->subDays(4),
            'rental_end_date' => $now->copy()->subDays(1),
            'status' => 'dikembalikan',
        ]);

        // 9. Selesai (*09) - Completed 3 days ago
        Order::where('order_number', 'LIKE', '%09')->update([
            'created_at' => $now->copy()->subDays(7),
            'updated_at' => $now->copy()->subDays(3),
            'rental_start_date' => $now->copy()->subDays(6),
            'rental_end_date' => $now->copy()->subDays(3),
            'status' => 'selesai',
        ]);

        // 10. Dibatalkan (*10) - Cancelled 1 day ago
        Order::where('order_number', 'LIKE', '%10')->update([
            'created_at' => $now->copy()->subDays(2),
            'updated_at' => $now->copy()->subDays(1),
            'rental_start_date' => $now->copy()->subDays(2),
            'rental_end_date' => $now->copy()->addDays(1),
            'status' => 'dibatalkan',
        ]);

        // Owner Registration Sample: Menunggu Verifikasi (3 hours ago)
        ClientRegistration::where('subdomain', 'dronefly-malang')->update([
            'created_at' => $now->copy()->subHours(3),
            'updated_at' => $now->copy()->subHours(3),
        ]);

        // Expiring Subscription Sample (Sisa 5 hari)
        Subscription::where('plan_name', 'Basic (Simulasi Expiring)')->update([
            'start_date' => $now->copy()->subDays(25),
            'end_date' => $now->copy()->addDays(5),
            'status' => 'aktif',
        ]);

        $this->info('Successfully refreshed all demo timestamps to current time!');
        return self::SUCCESS;
    }
}
