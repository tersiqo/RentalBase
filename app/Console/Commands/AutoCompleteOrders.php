<?php

namespace App\Console\Commands;

use App\Models\DamageCase;
use App\Models\EquipmentUnit;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoCompleteOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:auto-complete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menyelesaikan otomatis pesanan yang barangnya telah dikembalikan dan diinspeksi melewati masa tenggang 24 jam tanpa kendala.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memulai pengecekan pesanan yang siap diselesaikan otomatis...');

        $thresholdTime = Carbon::now()->subHours(24);

        // Cari pesanan yang statusnya 'dikembalikan' atau 'sedang_disewa' yang memiliki pengembalian selesai
        $orders = Order::whereIn('status', ['dikembalikan', 'sedang_disewa'])
            ->whereHas('returnModel', function ($query) use ($thresholdTime) {
                // Pengembalian sudah berstatus diterima/selesai
                $query->whereIn('return_status', ['diterima', 'selesai'])
                    // Tidak ada denda yang menggantung
                    ->where('late_fee_status', '!=', 'menunggu_pembayaran')
                    // Sudah melewati 24 jam sejak diperbarui/diterima
                    ->where('updated_at', '<=', $thresholdTime);
            })
            ->with(['returnModel', 'orderItems.orderItemUnits.equipmentUnit'])
            ->lazyById(100);

        $completedCount = 0;
        $failedCount = 0;

        foreach ($orders as $order) {
            try {
                $processed = DB::transaction(function () use ($order, $thresholdTime) {
                    $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->first();

                    if (! $lockedOrder || ! in_array($lockedOrder->status, ['dikembalikan', 'sedang_disewa'])) {
                        return false;
                    }

                    $return = $lockedOrder->returnModel()->lockForUpdate()->first();
                    if (! $return || ! in_array($return->return_status, ['diterima', 'selesai'])) {
                        return false;
                    }

                    // Re-check denda: tidak boleh ada denda yang menunggu pembayaran
                    if ($return->late_fee_status === 'menunggu_pembayaran') {
                        return false;
                    }

                    // Re-check waktu tenggang 24 jam
                    $returnTime = $return->updated_at ?? $return->created_at;
                    if ($returnTime && $returnTime->gt($thresholdTime)) {
                        return false;
                    }

                    // Periksa apakah terdapat kasus kerusakan aktif yang belum selesai/lunas
                    $hasUnresolvedDamage = DamageCase::whereHas('damageReport', function ($q) use ($lockedOrder) {
                        $q->where('order_id', $lockedOrder->id);
                    })->where(function ($q) {
                        $q->whereNotIn('case_status', ['selesai', 'dibatalkan', 'lunas'])
                          ->orWhere('payment_status', 'belum_dibayar');
                    })->exists();

                    if ($hasUnresolvedDamage) {
                        return false;
                    }

                    // Ubah status order menjadi selesai
                    $lockedOrder->update(['status' => 'selesai']);

                    // Perbarui status unit fisik agar kembali tersedia jika tidak ada order aktif lain
                    foreach ($lockedOrder->orderItems as $item) {
                        foreach ($item->orderItemUnits as $oiu) {
                            $unit = EquipmentUnit::where('id', $oiu->equipment_unit_id)->lockForUpdate()->first();
                            if ($unit && ! $unit->isActivelyRented()) {
                                $unit->update(['status' => 'tersedia']);
                            }
                        }
                    }

                    return true;
                });

                if ($processed) {
                    $completedCount++;
                    $this->line("Pesanan #{$order->order_number} berhasil diselesaikan otomatis.");
                }
            } catch (\Throwable $e) {
                $failedCount++;
                Log::error("Gagal menyelesaikan otomatis pesanan #{$order->id}: " . $e->getMessage(), [
                    'order_id' => $order->id,
                    'trace' => $e->getTraceAsString(),
                ]);
                $this->error("Error memproses pesanan #{$order->order_number}: {$e->getMessage()}");
            }
        }

        $this->info("Penyelesaian otomatis selesai. Total pesanan diselesaikan: {$completedCount}. Gagal: {$failedCount}.");

        return Command::SUCCESS;
    }
}
