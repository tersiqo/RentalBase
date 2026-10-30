<?php

namespace App\Console\Commands;

use App\Models\EquipmentUnit;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoCancelUnpaidOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:auto-cancel-unpaid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membatalkan otomatis pesanan yang belum dibayar setelah melewati batas waktu pembayaran (2 jam).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memulai pengecekan pesanan belum dibayar yang melewati batas waktu 2 jam...');

        // Batas waktu: 2 jam yang lalu
        $deadline = Carbon::now()->subHours(2);

        // Ambil order dengan status menunggu pembayaran yang terakhir diperbarui/dibuat sebelum deadline
        $orders = Order::whereIn('status', ['menunggu_pembayaran', 'belum_dibayar'])
            ->where('updated_at', '<=', $deadline)
            ->whereDoesntHave('payments', function ($query) {
                $query->where('status', 'terverifikasi');
            })
            ->with(['orderItems.orderItemUnits.equipmentUnit'])
            ->lazyById(100);

        $cancelledCount = 0;
        $failedCount = 0;

        foreach ($orders as $order) {
            try {
                $processed = DB::transaction(function () use ($order, $deadline) {
                    $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->first();

                    if (! $lockedOrder || ! in_array($lockedOrder->status, ['menunggu_pembayaran', 'belum_dibayar'])) {
                        return false;
                    }

                    // Re-check: pastikan belum ada pembayaran terverifikasi
                    if ($lockedOrder->payments()->where('status', 'terverifikasi')->exists()) {
                        return false;
                    }

                    // Re-check: pastikan batas waktu benar-benar sudah lewat
                    if ($lockedOrder->updated_at->gt($deadline)) {
                        return false;
                    }

                    // Lepaskan alokasi unit fisik
                    foreach ($lockedOrder->orderItems as $item) {
                        foreach ($item->orderItemUnits as $oiu) {
                            $unit = EquipmentUnit::where('id', $oiu->equipment_unit_id)->lockForUpdate()->first();
                            $oiu->delete();

                            // Jika unit tidak memiliki sewa aktif lain, kembalikan ke status tersedia
                            if ($unit && ! $unit->isActivelyRented()) {
                                $unit->update(['status' => 'tersedia']);
                            }
                        }
                    }

                    $lockedOrder->update([
                        'status'              => 'dibatalkan',
                        'cancellation_reason' => 'Dibatalkan otomatis oleh sistem: Melewati batas waktu pembayaran (2 jam).',
                    ]);

                    return true;
                });

                if ($processed) {
                    $cancelledCount++;
                    $this->line("Pesanan #{$order->order_number} berhasil dibatalkan otomatis.");
                }
            } catch (\Throwable $e) {
                $failedCount++;
                Log::error("Gagal membatalkan pesanan #{$order->id} secara otomatis: " . $e->getMessage(), [
                    'order_id' => $order->id,
                    'trace' => $e->getTraceAsString(),
                ]);
                $this->error("Error memproses pesanan #{$order->order_number}: {$e->getMessage()}");
            }
        }

        $this->info("Pemeriksaan selesai. Berhasil membatalkan {$cancelledCount} pesanan. Gagal: {$failedCount}.");

        return Command::SUCCESS;
    }
}
