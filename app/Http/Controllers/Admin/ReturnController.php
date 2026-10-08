<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ReturnModel;
use App\Http\Requests\Admin\ReturnProcessRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReturnController extends Controller
{
    public function index()
    {
        // 1. TAMBAHKAN BARIS INI UNTUK MENGHAPUS SISA LOGIN USER 1 SEBELUMNYA
        Auth::logout();
        
        // 2. KODE GAYA BYPASS BAWAAN PROJECTMU (Tidak usah diubah)
        $user = Auth::user();
        if (! $user) {
            $user = \App\Models\User::whereNotNull('client_id')->first(); // Ini akan otomatis memilih Admin Budi
        }
        $client = $user?->client; 

        if (!$client) {
            abort(403, 'Akses ditolak: Akun tidak terhubung ke toko.');
        }
        
        // ... (kode sisanya biarkan sama)
        
        $orders = Order::with(['orderItems.product', 'user'])
            ->where('client_id', $client->id)
            ->where('status', 'sedang_disewa')
            ->orderBy('rental_end_date', 'asc')
            ->get();
            
        return view('admin.returns.index', compact('orders'));
    }

    public function process(ReturnProcessRequest $request, Order $order)
    {
        // GAYA BYPASS SEPERTI PENGIRIMAN
        $user = Auth::user();
        if (! $user) {
            $user = \App\Models\User::whereNotNull('client_id')->first();
        }
        $client = $user?->client; 

        // Validasi Multi-Client Mutlak
        if (!$client || $order->client_id !== $client->id) {
            abort(403, 'Anda tidak berhak mengakses pesanan ini.');
        }

        DB::beginTransaction();
        try {
            $now = Carbon::now();
            $dueDate = Carbon::parse($order->rental_end_date);
            
            $lateHours = 0;
            if ($now->greaterThan($dueDate)) {
                $lateHours = $dueDate->diffInHours($now);
            }

            $calculatedLateFee = 0;
            foreach ($order->orderItems as $item) {
                $productLateFee = $item->product->late_fee_per_hour ?? 0;
                $calculatedLateFee += ($productLateFee * $item->quantity * $lateHours);
            }

            $lateFeeAmount = $request->input('late_fee_amount');
            $actionType = $request->input('action_type');

            $return = ReturnModel::firstOrNew(['order_id' => $order->id]);
            $return->return_method = 'langsung'; 
            $return->return_date = $now;
            $return->late_hours = $lateHours;
            $return->calculated_late_fee = $calculatedLateFee;
            $return->late_fee_amount = $lateFeeAmount;
            $return->processed_by = $user->id; // Gunakan id dari user hasil bypass
            
            if ($actionType === 'denda' && $lateFeeAmount > 0) {
                $return->late_fee_status = 'menunggu_pembayaran';
                $return->return_status = 'diterima'; 
                $order->status = 'menunggu_pembayaran_denda';
            } else {
                $return->late_fee_status = 'tidak_ada';
                $return->return_status = 'diterima';
                $order->status = 'selesai'; 
            }

            $return->save();
            $order->save();

            DB::commit();

            return redirect()->route('admin.returns.index')
                ->with('success', 'Pengembalian barang berhasil diproses.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
}