<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReturnProcessRequest;
use App\Models\Order;
use App\Models\ReturnModel;
use App\Models\ActivityLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReturnController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            $user = User::whereNotNull('client_id')->first();
        }
        $client = $user?->client;

        if (! $client) {
            abort(403, 'Akses ditolak: Akun tidak terhubung ke toko.');
        }

        $search = trim((string) $request->input('search', ''));
        $statusFilter = $request->input('status', '');
        $from = $request->input('from');
        $to = $request->input('to');

        $query = Order::with([
                'user',
                'orderItems.product',
                'orderItems.orderItemUnits.equipmentUnit.conditionChecks',
                'returnModel',
            ])
            ->where('client_id', $client->id)
            ->whereIn('status', ['sedang_disewa', 'dikembalikan', 'menunggu_pembayaran_denda']);

        if ($search !== '') {
            $likeOp = DB::connection()->getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
            $query->where(function ($q) use ($search, $likeOp) {
                $q->where('order_number', $likeOp, "%{$search}%")
                  ->orWhereHas('user', function ($u) use ($search, $likeOp) {
                      $u->where('name', $likeOp, "%{$search}%");
                  });
            });
        }

        if ($statusFilter === 'telat') {
            $query->where('rental_end_date', '<', now());
        } elseif ($statusFilter === 'diajukan') {
            $query->where('status', 'dikembalikan');
        } elseif ($statusFilter === 'menunggu_denda') {
            $query->where('status', 'menunggu_pembayaran_denda');
        }

        if (! empty($from)) {
            $query->whereDate('rental_end_date', '>=', $from);
        }

        if (! empty($to)) {
            $query->whereDate('rental_end_date', '<=', $to);
        }

        $orders = $query->orderBy('rental_end_date', 'asc')->get();

        return view('admin.returns.index', compact('orders', 'client'));
    }

    public function process(ReturnProcessRequest $request, Order $order)
    {
        $user = Auth::user();
        if (! $user) {
            $user = User::whereNotNull('client_id')->first();
        }
        $client = $user?->client;

        // Validasi Multi-Client Mutlak
        if (! $client || $order->client_id !== $client->id) {
            abort(403, 'Anda tidak berhak mengakses pesanan ini.');
        }

        // Sinkronkan sequence PostgreSQL agar tidak gagal insert karena bentrok ID seeder
        if (DB::connection()->getDriverName() === 'pgsql') {
            try {
                DB::statement("SELECT setval(pg_get_serial_sequence('returns', 'id'), COALESCE((SELECT MAX(id) FROM returns), 1))");
            } catch (\Throwable $t) {
                // Abaikan jika bukan sequence standar
            }
        }

        DB::beginTransaction();
        try {
            $order->loadMissing(['orderItems.product', 'orderItems.orderItemUnits.equipmentUnit', 'returnModel']);

            $now = Carbon::now();
            $dueDate = Carbon::parse($order->rental_end_date);

            // Gunakan waktu pengajuan customer jika sudah ada, atau waktu saat ini
            $effectiveReturnDate = $order->returnModel?->return_date
                ? Carbon::parse($order->returnModel->return_date)
                : $now;

            $lateHours = 0;
            if ($effectiveReturnDate->greaterThan($dueDate)) {
                $diffInMinutes = $dueDate->diffInMinutes($effectiveReturnDate);
                $lateHours = (int) ceil($diffInMinutes / 60);
            }

            $calculatedLateFee = 0;
            foreach ($order->orderItems as $item) {
                $productLateFee = (float) ($item->product->late_fee_per_hour ?? 0);
                // Fallback: jika produk lama dari seeder tarif dendanya 0, gunakan 10% dari harga sewa harian (atau 10.000)
                if ($productLateFee <= 0) {
                    $dailyPrice = (float) ($item->product->rental_price_per_day ?? 0);
                    $productLateFee = $dailyPrice > 0 ? round($dailyPrice * 0.1, -3) : 10000;
                }
                $calculatedLateFee += ($productLateFee * (int) $item->quantity * $lateHours);
            }

            $lateFeeAmount = (float) $request->input('late_fee_amount', 0);
            $actionType = $request->input('action_type');

            $return = ReturnModel::firstOrNew(['order_id' => $order->id]);
            $return->return_method = $return->return_method ?: ($order->isPickup() ? 'ambil_toko' : 'kurir');
            $return->return_date = $return->return_date ?: $now;
            $return->late_hours = $lateHours;
            $return->calculated_late_fee = $calculatedLateFee;
            $return->late_fee_amount = $lateFeeAmount;
            $return->processed_by = $user->id;

            if ($actionType === 'denda' && $lateFeeAmount > 0 && empty($return->late_fee_proof)) {
                $return->late_fee_status = 'menunggu_pembayaran';
                $return->return_status = 'diterima';
                $order->status = 'menunggu_pembayaran_denda';
                $msg = 'Tagihan denda sebesar Rp ' . number_format($lateFeeAmount, 0, ',', '.') . " untuk pesanan #{$order->order_number} berhasil diterbitkan.";
            } else {
                // Barang kembali normal ATAU denda sudah lunas (termasuk jika customer sudah unggah late_fee_proof) / dimaafkan
                $return->late_fee_status = (! empty($return->late_fee_proof) || $lateFeeAmount > 0) ? 'lunas' : 'tidak_ada';
                $return->return_status = 'selesai';
                $order->status = 'selesai';

                // Kembalikan status unit fisik yang disewa menjadi 'tersedia' (jika tidak sedang rusak/maintenance)
                foreach ($order->orderItems as $item) {
                    foreach ($item->orderItemUnits as $oiu) {
                        $unit = $oiu->equipmentUnit;
                        if ($unit && ! in_array($unit->status, ['rusak', 'damaged', 'maintenance'])) {
                            $unit->update(['status' => 'tersedia']);
                        }
                    }
                }

                $msg = "Pengembalian pesanan #{$order->order_number} selesai diproses dan status unit telah diperbarui.";
            }

            $return->save();
            $order->save();

            // Catat jejak audit pengembalian / denda
            ActivityLog::create([
                'client_id'   => $order->client_id,
                'user_id'     => $user->id,
                'action'      => ($actionType === 'denda' && $lateFeeAmount > 0 && empty($return->late_fee_proof)) ? 'late_fine_issued' : 'return_completed',
                'module'      => 'returns',
                'description' => ($actionType === 'denda' && $lateFeeAmount > 0 && empty($return->late_fee_proof))
                    ? sprintf('Tagihan denda keterlambatan sebesar Rp %s (%d jam terlambat) diterbitkan untuk pesanan #%s oleh %s.', number_format($lateFeeAmount, 0, ',', '.'), $lateHours, $order->order_number, $user->name ?? 'Admin')
                    : sprintf('Pengembalian pesanan #%s selesai diverifikasi dan unit barang kembali berstatus tersedia.', $order->order_number),
                'ip_address'  => $request->ip(),
                'user_agent'  => $request->userAgent(),
            ]);

            DB::commit();

            if (class_exists(\App\Events\ReturnAccepted::class)) {
                event(new \App\Events\ReturnAccepted($order));
            }

            if ($actionType === 'denda' && $lateFeeAmount > 0 && class_exists(\App\Events\FineIssued::class)) {
                event(new \App\Events\FineIssued($order, $lateFeeAmount));
            }

            return redirect()->route('admin.returns.index')->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memproses pengembalian pesanan', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Terjadi kesalahan sistem saat menyimpan data pengembalian.');
        }
    }
}

