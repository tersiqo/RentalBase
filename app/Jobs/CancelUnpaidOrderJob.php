<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CancelUnpaidOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $orderId) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $order = Order::with(['payments', 'orderItems.orderItemUnits.equipmentUnit'])->find($this->orderId);

        if (! $order) {
            return;
        }

        // Hanya batalkan jika status masih menunggu pembayaran / belum dibayar
        if (! in_array($order->status, ['menunggu_pembayaran', 'belum_dibayar'])) {
            return;
        }

        // Jika sudah ada pembayaran terverifikasi, abaikan
        if ($order->payments()->where('status', 'terverifikasi')->exists()) {
            return;
        }

        DB::transaction(function () use ($order) {
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->first();

            if (! $lockedOrder || ! in_array($lockedOrder->status, ['menunggu_pembayaran', 'belum_dibayar'])) {
                return;
            }

            if ($lockedOrder->payments()->where('status', 'terverifikasi')->exists()) {
                return;
            }

            $lockedOrder->update([
                'status' => 'dibatalkan',
            ]);

            // Lepaskan alokasi unit fisik kembali ke status tersedia
            foreach ($lockedOrder->orderItems as $item) {
                foreach ($item->orderItemUnits as $orderItemUnit) {
                    if ($orderItemUnit->equipmentUnit) {
                        $orderItemUnit->equipmentUnit->update([
                            'status' => 'tersedia',
                        ]);
                    }
                }
            }

            Log::info("CancelUnpaidOrderJob: Pesanan #{$lockedOrder->order_number} (ID: {$lockedOrder->id}) berhasil dibatalkan otomatis setelah batas waktu pembayaran habis.");
        });
    }
}
