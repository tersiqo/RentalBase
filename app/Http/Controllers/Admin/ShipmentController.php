<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShipmentController extends Controller
{
    /**
     * Menampilkan daftar pesanan yang siap dikirim / diserahkan kepada customer.
     */
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

        $search = $request->input('search');
        $method = $request->input('method'); // 'kurir', 'ambil_toko'
        $from   = $request->input('from');
        $to     = $request->input('to');

        // Eager load relasi user, produk, dan unit yang dialokasikan
        $query = Order::with(['user', 'orderItems.product', 'orderItems.orderItemUnits.equipmentUnit'])
            ->where('client_id', $client->id)
            ->whereIn('status', ['siap_kirim', 'diproses']);

        // Filter pencarian
        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'ILIKE', "%{$search}%")
                  ->orWhereHas('user', function ($u) use ($search) {
                      $u->where('name', 'ILIKE', "%{$search}%");
                  });
            });
        }

        // Filter rentang tanggal sewa
        if (! empty($from)) {
            $query->whereDate('rental_start_date', '>=', $from);
        }

        if (! empty($to)) {
            $query->whereDate('rental_start_date', '<=', $to);
        }

        $allOrders = $query->latest()->get();

        // Filter berdasarkan metode pengiriman
        if ($method === 'kurir') {
            $orders = $allOrders->reject(fn ($ord) => $ord->isPickup());
        } elseif ($method === 'ambil_toko') {
            $orders = $allOrders->filter(fn ($ord) => $ord->isPickup());
        } else {
            $orders = $allOrders;
        }

        return view('admin.shipments.index', compact('orders'));
    }

    /**
     * Memproses pengiriman kurir atau penyerahan barang di toko.
     */
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

        // Kunci Jadwal Pengiriman / Penyerahan: Ambil di Toko = Hari-H, Kurir = mulai H-1
        if (! $order->canProcessHandover()) {
            return redirect()->back()
                ->with('error', $order->handover_blocked_reason ?? 'Belum memasuki jadwal penyerahan atau pengiriman barang.');
        }

        // SOP Rental: Wajib pastikan seluruh unit fisik sudah dialokasikan sebelum dikirim
        $totalRequired = $order->orderItems->sum('quantity');
        $totalAllocated = $order->orderItems->sum(fn ($item) => $item->orderItemUnits->count());

        if ($totalAllocated < $totalRequired) {
            return redirect()->route('admin.orders.show', $order->id)
                ->with('error', 'Pengiriman belum dapat diproses: Unit fisik belum dialokasikan lengkap (' . $totalAllocated . '/' . $totalRequired . '). Silakan alokasikan unit fisik terlebih dahulu di halaman ini.');
        }

        $isPickup = $order->isPickup();

        $validationRules = [];
        $validationMessages = [];

        if (! $isPickup) {
            $validationRules['courier_name'] = 'required|string|max:100';
            $validationRules['tracking_number'] = 'required|string|max:100';
            $validationMessages['courier_name.required'] = 'Pilihan ekspedisi kurir wajib diisi.';
            $validationMessages['tracking_number.required'] = 'Nomor resi atau tautan pelacakan wajib diisi.';
        }

        // Validasi konfirmasi penerimaan fisik KTP sebagai jaminan (wajib jika ada jaminan identitas)
        if ($order->identityGuarantee) {
            $validationRules['ktp_deposited'] = 'accepted';
            $validationMessages['ktp_deposited.accepted'] = 'Konfirmasi penerimaan & penitipan fisik KTP / identitas asli wajib dicentang sebelum barang diserahkan.';
        }

        $request->validate($validationRules, $validationMessages);

        DB::transaction(function () use ($order, $request, $isPickup, $user) {
            $courierName    = $isPickup ? 'Ambil Sendiri di Toko' : ($request->courier_name ?? 'Kurir Toko');
            $trackingNumber = $isPickup ? ('PICKUP-' . ($order->order_number ?? $order->id)) : $request->tracking_number;
            $deliveryAddr   = $isPickup ? 'Pelanggan mengambil langsung di toko rental' : ($order->shipping_address ?: 'Alamat Pengiriman');

            $targetStatus = $isPickup ? 'sedang_disewa' : 'dikirim';

            Shipment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'shipping_method'  => $isPickup ? 'ambil_toko' : 'kurir',
                    'courier_name'     => $courierName,
                    'tracking_number'  => $trackingNumber,
                    'delivery_address' => $deliveryAddr,
                    'status'           => $isPickup ? 'diterima' : 'dikirim',
                    'shipped_at'       => now(),
                    'delivered_at'     => $isPickup ? now() : null,
                ]
            );

            // Mengubah status order menjadi "sedang_disewa" jika ambil di toko, atau "dikirim" jika kurir
            $order->update([
                'status' => $targetStatus,
            ]);

            // Update status jaminan KTP menjadi "dititipkan" dan catat waktu serah terima fisik KTP
            if ($order->identityGuarantee) {
                $order->identityGuarantee->update([
                    'status' => 'dititipkan',
                    'deposited_at' => now(),
                ]);
            }

            // Pastikan seluruh unit fisik yang dialokasikan berubah status menjadi 'disewa'
            foreach ($order->orderItems as $item) {
                foreach ($item->orderItemUnits as $oiu) {
                    $oiu->equipmentUnit?->update(['status' => 'disewa']);
                }
            }

            // Auto-sync jam sewa jika waktu serah terima melewati jadwal rencana sewa awal
            $wasSynced = $order->syncRentalHoursOnHandover(now());

            // Catat jejak audit aktivitas pengiriman logistik & penitipan KTP fisik
            $syncNote = $wasSynced
                ? sprintf(' Jam mulai & batas kembali sewa otomatis disinkronkan ke waktu serah terima (%s WIB).', now()->format('H:i'))
                : '';

            $activityDesc = $isPickup
                ? sprintf('Pesanan #%s berhasil diserahkan langsung kepada pelanggan di toko. Fisik KTP/identitas penyewa resmi dititipkan sebagai jaminan.%s', $order->order_number ?? $order->id, $syncNote)
                : sprintf('Pesanan #%s berhasil diproses dan dikirim via %s (Resi: %s). Fisik KTP/identitas penyewa resmi dicatat dititipkan sebagai jaminan.%s', $order->order_number ?? $order->id, $courierName, $trackingNumber, $syncNote);

            ActivityLog::create([
                'client_id'   => $order->client_id,
                'user_id'     => $user->id,
                'action'      => $isPickup ? 'order_handed_over' : 'order_shipped',
                'module'      => 'shipments',
                'description' => $activityDesc,
                'ip_address'  => $request->ip(),
                'user_agent'  => $request->userAgent(),
            ]);
        });

        if (class_exists(\App\Events\OrderShipped::class)) {
            event(new \App\Events\OrderShipped($order));
        }

        $orderLabel = '#' . ($order->order_number ?? $order->id);
        $successMsg = $isPickup
            ? "Pesanan {$orderLabel} berhasil diserahkan kepada pelanggan!"
            : "Pesanan {$orderLabel} berhasil diproses dan dikirim via {$request->courier_name} (Resi: {$request->tracking_number})!";

        return redirect()->route('admin.shipments.index')->with('success', $successMsg);
    }
}
