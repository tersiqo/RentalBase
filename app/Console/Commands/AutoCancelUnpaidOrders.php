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

        // 1. Batalkan pesanan menunggu pembayaran yang melewati batas waktu 2 jam
        $unpaidDeadline = Carbon::now()->subHours(2);

        $unpaidOrders = Order::whereIn('status', ['menunggu_pembayaran', 'belum_dibayar'])
            ->where('updated_at', '<=', $unpaidDeadline)
            ->whereDoesntHave('payments', function ($query) {
                $query->where('status', 'terverifikasi');
            })
            ->with(['orderItems.orderItemUnits.equipmentUnit'])
            ->lazyById(100);

        $cancelledCount = 0;
        $failedCount = 0;

        foreach ($unpaidOrders as $order) {
            try {
                $processed = DB::transaction(function () use ($order, $unpaidDeadline) {
                    $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->first();

                    if (! $lockedOrder || ! in_array($lockedOrder->status, ['menunggu_pembayaran', 'belum_dibayar'])) {
                        return false;
                    }

                    // Re-check: pastikan belum ada pembayaran terverifikasi
                    if ($lockedOrder->payments()->where('status', 'terverifikasi')->exists()) {
                        return false;
                    }

                    // Re-check: pastikan batas waktu benar-benar sudah lewat
                    if ($lockedOrder->updated_at->gt($unpaidDeadline)) {
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
                    $this->line("Pesanan belum dibayar #{$order->order_number} berhasil dibatalkan otomatis.");
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

        // 2. Batalkan pesanan menunggu konfirmasi yang menggantung lebih dari 48 jam (Stale Orders)
        $this->info('Memulai pengecekan pesanan menunggu konfirmasi yang melewati batas waktu 48 jam...');
        $staleDeadline = Carbon::now()->subHours(48);

        $staleOrders = Order::where('status', 'menunggu_konfirmasi')
            ->where('created_at', '<=', $staleDeadline)
            ->with(['orderItems.orderItemUnits.equipmentUnit'])
            ->lazyById(100);

        $staleCancelledCount = 0;

        foreach ($staleOrders as $order) {
            try {
                $processed = DB::transaction(function () use ($order, $staleDeadline) {
                    $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->first();

                    if (! $lockedOrder || $lockedOrder->status !== 'menunggu_konfirmasi') {
                        return false;
                    }

                    if ($lockedOrder->created_at->gt($staleDeadline)) {
                        return false;
                    }

                    // Lepaskan alokasi unit fisik jika sempat dialokasikan
                    foreach ($lockedOrder->orderItems as $item) {
                        foreach ($item->orderItemUnits as $oiu) {
                            $unit = EquipmentUnit::where('id', $oiu->equipment_unit_id)->lockForUpdate()->first();
                            $oiu->delete();

                            if ($unit && ! $unit->isActivelyRented()) {
                                $unit->update(['status' => 'tersedia']);
                            }
                        }
                    }

                    if ($lockedOrder->identityGuarantee && $lockedOrder->identityGuarantee->status === 'menunggu') {
                        $lockedOrder->identityGuarantee->update(['status' => 'ditolak']);
                    }

                    $lockedOrder->update([
                        'status'              => 'dibatalkan',
                        'cancellation_reason' => 'Dibatalkan otomatis oleh sistem karena melewati batas waktu konfirmasi awal (48 jam).',
                    ]);

                    return true;
                });

                if ($processed) {
                    $staleCancelledCount++;
                    $this->line("Pesanan stale #{$order->order_number} berhasil dibatalkan otomatis.");
                }
            } catch (\Throwable $e) {
                $failedCount++;
                Log::error("Gagal membatalkan pesanan stale #{$order->id}: " . $e->getMessage(), [
                    'order_id' => $order->id,
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        $this->info("Pemeriksaan selesai. Berhasil membatalkan {$cancelledCount} pesanan belum bayar dan {$staleCancelledCount} pesanan stale. Gagal: {$failedCount}.");

        return Command::SUCCESS;
    }
}
