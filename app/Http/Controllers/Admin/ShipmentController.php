<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order; // Kita pakai Request bawaan standar
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShipmentController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (! $user) {
            $user = User::whereNotNull('client_id')->first();
        }

        $client = $user?->client;

        if (! $client) {
            abort(403, 'Akses ditolak: Akun tidak terhubung ke toko.');
        }

        // Eager load relasi user, produk, dan unit yang dialokasikan
        $orders = Order::with(['user', 'orderItems.product', 'orderItems.orderItemUnits'])
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
            $user = User::whereNotNull('client_id')->first();
        }
        $client = $user?->client;

        if (! $client || $order->client_id !== $client->id) {
            abort(403, 'Kamu tidak memiliki akses ke pesanan ini.');
        }

        // SOP Rental: Wajib pastikan seluruh unit fisik sudah dialokasikan sebelum dikirim
        $totalRequired = $order->orderItems->sum('quantity');
        $totalAllocated = $order->orderItems->sum(fn ($item) => $item->orderItemUnits->count());

        if ($totalAllocated < $totalRequired) {
            return redirect()->route('admin.orders.show', $order->id)
                ->with('error', 'Pengiriman belum dapat diproses: Unit fisik belum dialokasikan lengkap (' . $totalAllocated . '/' . $totalRequired . '). Silakan alokasikan unit fisik terlebih dahulu di halaman ini.');
        }

        // Tambahkan $request di dalam "use" agar datanya bisa dibaca
        DB::transaction(function () use ($order, $request) {
            $isPickup = $order->isPickup();

            Shipment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'shipping_method' => $isPickup ? 'ambil_toko' : 'kurir',
                    // Menangkap data kurir dan resi dari Pop Up
                    'courier_name' => $request->courier_name,
                    'tracking_number' => $request->tracking_number,
                    'delivery_address' => $order->shipping_address ?: 'Pelanggan ambil di toko',
                    'status' => 'dikirim',
                    'shipped_at' => now(),
                ]
            );

            // Mengubah status order menjadi "dikirim"
            $order->update([
                'status' => 'dikirim',
            ]);

            // Pastikan seluruh unit fisik yang dialokasikan berubah status menjadi 'disewa'
            foreach ($order->orderItems as $item) {
                foreach ($item->orderItemUnits as $oiu) {
                    $oiu->equipmentUnit?->update(['status' => 'disewa']);
                }
            }
        });

        return redirect()->route('admin.shipments.index')
            ->with('success', 'Pesanan #' . $order->order_number . ' berhasil diproses dan dikirim!');
    }
}
