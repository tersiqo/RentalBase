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

class AutoCompleteOrderJob implements ShouldQueue
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
        $order = Order::with(['returnModel', 'identityGuarantee', 'damageCases', 'orderItems.orderItemUnits.equipmentUnit'])->find($this->orderId);

        if (! $order) {
            return;
        }

        // Hanya proses pesanan yang sedang disewa atau sudah dikembalikan
        if (! in_array($order->status, ['dikembalikan', 'sedang_disewa'])) {
            return;
        }

        $return = $order->returnModel;
        if (! $return || ! in_array($return->return_status, ['diterima', 'selesai'])) {
            return;
        }

        // Jika ada denda keterlambatan yang belum dibayar, jangan selesaikan
        if ($return->late_fee_status === 'menunggu_pembayaran') {
            return;
        }

        // Jika ada kasus klaim kerusakan yang masih aktif/belum selesai, tahan
        $hasActiveDamage = $order->damageCases()
            ->where('status', '!=', 'selesai')
            ->exists();

        if ($hasActiveDamage) {
            Log::info("AutoCompleteOrderJob: Pesanan #{$order->order_number} ditangguhkan karena masih memiliki klaim kerusakan yang belum selesai.");
            return;
        }

        DB::transaction(function () use ($order) {
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->first();

            if (! $lockedOrder || ! in_array($lockedOrder->status, ['dikembalikan', 'sedang_disewa'])) {
                return;
            }

            $lockedOrder->update([
                'status' => 'selesai',
            ]);

            // Mastiin unit fisik kembali ke status tersedia
            foreach ($lockedOrder->orderItems as $item) {
                foreach ($item->orderItemUnits as $orderItemUnit) {
                    if ($orderItemUnit->equipmentUnit && $orderItemUnit->equipmentUnit->status === 'disewa') {
                        $orderItemUnit->equipmentUnit->update([
                            'status' => 'tersedia',
                        ]);
                    }
                }
            }

            Log::info("AutoCompleteOrderJob: Pesanan #{$lockedOrder->order_number} (ID: {$lockedOrder->id}) berhasil diselesaikan otomatis setelah 24 jam pengembalian bebas kendala.");
        });
    }
}
