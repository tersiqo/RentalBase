<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request; // Kita pakai Request bawaan standar
use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ShipmentController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        if (! $user) {
            $user = \App\Models\User::whereNotNull('client_id')->first();
        }

        $client = $user?->client; 

        if (!$client) {
            abort(403, 'Akses ditolak: Akun tidak terhubung ke toko.');
        }

        // PERUBAHAN: Tambahkan 'orderItems.product' agar bisa menampilkan List Detail Barang
        $orders = Order::with(['user', 'orderItems.product']) 
            ->where('client_id', $client->id)
            ->whereIn('status', ['siap_kirim', 'diproses'])
            ->latest()
            ->get();

        return view('admin.shipments.index', compact('orders'));
    }

    // PERUBAHAN: Gunakan Request biasa, bukan ShipmentProcessRequest
        public function process(Request $request, Order $order)
    {
        $user = Auth::user();
        if (! $user) {
            $user = \App\Models\User::whereNotNull('client_id')->first();
        }
        $client = $user?->client;

        if (!$client || $order->client_id !== $client->id) {
            abort(403, 'Kamu tidak memiliki akses ke pesanan ini.');
        }

        // Tambahkan $request di dalam "use" agar datanya bisa dibaca
        DB::transaction(function () use ($order, $request) {
            $isPickup = empty($order->shipping_address) || str_contains(strtolower($order->shipping_address), 'toko');

            Shipment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'shipping_method'  => $isPickup ? 'ambil_toko' : 'kurir',
                    // Menangkap data kurir dan resi dari Pop Up
                    'courier_name'     => $request->courier_name,
                    'tracking_number'  => $request->tracking_number,
                    'delivery_address' => $order->shipping_address ?? 'Pelanggan ambil di toko',
                    'status'           => 'dikirim',
                    'shipped_at'       => now(),
                ]
            );

            // Sesuai chat PM: mengubah status order menjadi "dikirim"
            $order->update([
                'status' => 'dikirim'
            ]);
        });

        return redirect()->route('admin.shipments.index')
                         ->with('success', 'Status pesanan berhasil diperbarui!');
    }
}
