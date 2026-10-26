<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EquipmentUnit;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        // Ambil identitas toko yang sedang login
        // (pola ini sama persis dengan ShipmentController dan ReturnController)
        $user = Auth::user();

        if (! $user) {
            $user = \App\Models\User::whereNotNull('client_id')->first();
        }

        $client = $user?->client;

        if (! $client) {
            abort(403, 'Akun tidak terhubung ke toko.');
        }

        $clientId = $client->id;

        // Baca parameter filter dari URL
        // Contoh: /admin/orders?search=budi&status=dikirim&from=2026-10-01
        $search = $request->input('search');
        $status = $request->input('status');
        $from   = $request->input('from');
        $to     = $request->input('to');

        // Bangun query secara bertahap
        $orders = Order::with(['user', 'orderItems.product'])

            // WAJIB ADA: batasi hanya pesanan milik toko ini
            ->where('client_id', $clientId)

            // Filter pencarian: nama customer ATAU kode order
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'ILIKE', '%' . $search . '%');
                    })
                    ->orWhere('order_number', 'ILIKE', '%' . $search . '%');
                });
            })

            // Filter berdasarkan status
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
            })

            // Filter tanggal awal
            ->when($from, function ($query, $from) {
                $query->whereDate('created_at', '>=', $from);
            })

            // Filter tanggal akhir
            ->when($to, function ($query, $to) {
                $query->whereDate('created_at', '<=', $to);
            })

            // Pesanan terbaru paling atas
            ->latest()

            // Tampilkan 15 pesanan per halaman
            ->paginate(15)

            // Filter tetap terbawa saat berpindah halaman
            ->withQueryString();

        // Daftar status untuk dropdown filter di halaman
        $statusOptions = [
            'menunggu_konfirmasi' => 'Menunggu Konfirmasi KTP',
            'menunggu_pembayaran' => 'Menunggu Pembayaran',
            'menunggu_verifikasi' => 'Menunggu Verifikasi Pembayaran',
            'siap_kirim'          => 'Siap Dikirim',
            'dikirim'             => 'Dikirim',
            'sedang_disewa'       => 'Sedang Disewa',
            'selesai'             => 'Selesai',
            'batal'               => 'Batal / Ditolak',
        ];

        return view('admin.orders.index', compact('orders', 'statusOptions'));
    }

    /**
     * Halaman detail pesanan.
     * Menampilkan semua informasi pesanan secara lengkap untuk admin.
     */
    public function show(Order $order)
    {
        // Pastikan order milik toko yang sedang login
        $user = Auth::user();
        if (! $user) {
            $user = \App\Models\User::whereNotNull('client_id')->first();
        }
        $client = $user?->client;

        if (! $client || $order->client_id !== $client->id) {
            abort(403, 'Kamu tidak memiliki akses ke pesanan ini.');
        }

        // Eager load semua relasi yang dibutuhkan halaman detail
        $order->load([
            'user',
            'orderItems.product',
            'orderItems.orderItemUnits.equipmentUnit',
            'payments',
            'latestPayment',
            'identityGuarantee',
            'shipment',
        ]);

        // Ambil daftar unit fisik milik toko ini yang statusnya 'available'
        // untuk keperluan dropdown alokasi unit
        $availableUnits = EquipmentUnit::where('client_id', $client->id)
            ->whereIn('status', ['tersedia', 'available'])
            ->with('product')
            ->get();

        // Helper: ambil URL file dari storage (disk public atau s3)
        // Mengembalikan null jika path kosong
        $getFileUrl = function (?string $path, string $disk = 'public'): ?string {
            if (! $path) {
                return null;
            }
            if (str_starts_with($path, 'http')) {
                return $path;
            }
            return Storage::disk($disk)->url($path);
        };

        // URL preview foto identitas (KTP dan selfie)
        $ktpUrl    = $getFileUrl($order->identityGuarantee?->ktp_photo);
        $selfieUrl = $getFileUrl($order->identityGuarantee?->selfie_photo);

        // URL preview bukti pembayaran (dari payment terakhir)
        $paymentProofUrl = $getFileUrl($order->latestPayment?->proof_of_payment);

        return view('admin.orders.show', compact(
            'order',
            'availableUnits',
            'ktpUrl',
            'selfieUrl',
            'paymentProofUrl',
        ));
    }
    /**
     * Admin menerima/mengkonfirmasi pesanan:
     * - Jika status menunggu_konfirmasi: verifikasi jaminan KTP & ubah status ke menunggu_pembayaran
     * - Jika status menunggu_pembayaran / menunggu_verifikasi: verifikasi bukti bayar & ubah status ke siap_kirim
     */
    public function confirm(Request $request, Order $order)
    {
        // Pastikan order milik toko yang sedang login
        $user = Auth::user();
        if (! $user) {
            $user = \App\Models\User::whereNotNull('client_id')->first();
        }
        $client = $user?->client;

        if (! $client || $order->client_id !== $client->id) {
            abort(403, 'Kamu tidak memiliki akses ke pesanan ini.');
        }

        // Hanya boleh konfirmasi jika statusnya masih dalam proses pra-pengiriman
        if (! in_array($order->status, ['menunggu_konfirmasi', 'menunggu_verifikasi', 'verifikasi', 'menunggu_pembayaran'])) {
            return redirect()->back()
                ->with('error', 'Pesanan ini tidak bisa dikonfirmasi karena statusnya sudah berubah.');
        }

        $directReady = $request->boolean('direct_ready');

        // JIKA STATUS MENUNGGU KONFIRMASI (Verifikasi KTP) dan bukan directReady:
        if ($order->status === 'menunggu_konfirmasi' && ! $directReady) {
            // Setujui jaminan identitas
            if ($order->identityGuarantee && $order->identityGuarantee->status !== 'diverifikasi') {
                $order->identityGuarantee->update([
                    'status' => 'diverifikasi',
                    'reviewed_by' => $user?->id,
                ]);
            }

            // Ubah status order menjadi menunggu_pembayaran
            $order->update(['status' => 'menunggu_pembayaran']);

            return redirect()->back()
                ->with('success', 'Identitas pesanan #' . $order->order_number . ' disetujui! Pesanan kini berstatus Menunggu Pembayaran dari customer.');
        }

        // JIKA VERIFIKASI PEMBAYARAN (menunggu_verifikasi / menunggu_pembayaran / directReady):
        // 1. Pastikan identitas terverifikasi
        if ($order->identityGuarantee && $order->identityGuarantee->status !== 'diverifikasi') {
            $order->identityGuarantee->update([
                'status' => 'diverifikasi',
                'reviewed_by' => $user?->id,
            ]);
        }

        // 2. Setujui bukti pembayaran
        if ($order->payments()->exists()) {
            $order->payments()->whereIn('status', ['menunggu_konfirmasi', 'pending', 'menunggu_verifikasi'])->update([
                'status' => 'terverifikasi',
                'verified_by' => $user?->id,
                'verified_at' => now(),
            ]);
        }

        // 3. Ubah status order ke siap_kirim
        $order->update(['status' => 'siap_kirim']);

        return redirect()->back()
            ->with('success', 'Pesanan #' . $order->order_number . ' berhasil dikonfirmasi dan masuk antrian pengiriman!');
    }

    /**
     * Admin menolak pesanan.
     * Status berubah dari menunggu_konfirmasi → dibatalkan
     */
    public function reject(\Illuminate\Http\Request $request, Order $order)
    {
        // Pastikan order milik toko yang sedang login
        $user = Auth::user();
        if (! $user) {
            $user = \App\Models\User::whereNotNull('client_id')->first();
        }
        $client = $user?->client;

        if (! $client || $order->client_id !== $client->id) {
            abort(403, 'Kamu tidak memiliki akses ke pesanan ini.');
        }

        // Hanya boleh tolak jika statusnya masih menunggu konfirmasi
        if (! in_array($order->status, ['menunggu_konfirmasi', 'menunggu_verifikasi', 'verifikasi'])) {
            return redirect()->route('admin.orders.index')
                ->with('error', 'Pesanan ini tidak bisa ditolak karena statusnya sudah berubah.');
        }

        $order->update([
            'status'              => 'dibatalkan',
            'cancellation_reason' => $request->input('cancellation_reason', 'Ditolak oleh admin.'),
        ]);

        return redirect()->route('admin.orders.index')
            ->with('success', 'Pesanan #' . $order->order_number . ' telah ditolak.');
    }

    /**
     * Alokasi unit fisik ke item pesanan.
     */
    public function allocate(Request $request, Order $order)
    {
        $user = Auth::user();
        if (! $user) {
            $user = \App\Models\User::whereNotNull('client_id')->first();
        }
        $client = $user?->client;

        if (! $client || $order->client_id !== $client->id) {
            abort(403, 'Kamu tidak memiliki akses ke pesanan ini.');
        }

        $request->validate([
            'order_item_id' => 'required|exists:order_items,id',
            'equipment_unit_id' => 'required|exists:equipment_units,id',
        ]);

        $orderItem = $order->orderItems()->findOrFail($request->order_item_id);
        $unit = EquipmentUnit::where('client_id', $client->id)->findOrFail($request->equipment_unit_id);

        if ($unit->product_id !== $orderItem->product_id) {
            return redirect()->back()->with('error', 'Unit fisik tidak cocok dengan produk pesanan.');
        }

        // Cek jika sudah dialokasikan ke item ini
        $alreadyAllocatedToThis = \App\Models\OrderItemUnit::where('order_item_id', $orderItem->id)
            ->where('equipment_unit_id', $unit->id)
            ->exists();

        if ($alreadyAllocatedToThis) {
            return redirect()->back()->with('error', 'Unit fisik ini sudah dialokasikan ke item ini.');
        }

        // Cek kuota item
        if ($orderItem->orderItemUnits()->count() >= $orderItem->quantity) {
            return redirect()->back()->with('error', 'Kebutuhan unit untuk item ini sudah terpenuhi.');
        }

        \App\Models\OrderItemUnit::create([
            'order_item_id' => $orderItem->id,
            'equipment_unit_id' => $unit->id,
        ]);

        $unit->update(['status' => 'disewa']);

        return redirect()->back()->with('success', 'Unit fisik ' . ($unit->unit_code ?? '#' . $unit->id) . ' berhasil dialokasikan ke pesanan!');
    }

    /**
     * Batalkan alokasi unit fisik dari item pesanan agar admin bisa memilih unit lain.
     */
    public function deallocate(Order $order, \App\Models\OrderItemUnit $orderItemUnit)
    {
        $user = Auth::user();
        if (! $user) {
            $user = \App\Models\User::whereNotNull('client_id')->first();
        }
        $client = $user?->client;

        if (! $client || $order->client_id !== $client->id) {
            abort(403, 'Kamu tidak memiliki akses ke pesanan ini.');
        }

        // Hanya boleh batalkan alokasi jika pesanan belum dikirim / sedang disewa
        if (! in_array($order->status, ['menunggu_konfirmasi', 'menunggu_verifikasi', 'verifikasi', 'siap_kirim'])) {
            return redirect()->back()->with('error', 'Alokasi unit tidak dapat dibatalkan pada status pesanan saat ini.');
        }

        // Pastikan orderItemUnit memang milik salah satu orderItem dari pesanan ini
        $orderItem = $order->orderItems()->where('id', $orderItemUnit->order_item_id)->first();
        if (! $orderItem) {
            abort(404, 'Item pesanan tidak ditemukan.');
        }

        $unit = $orderItemUnit->equipmentUnit;
        $unitCode = $unit?->unit_code ?? $unit?->asset_code ?? ('#' . $orderItemUnit->equipment_unit_id);

        if ($unit) {
            $unit->update(['status' => 'tersedia']);
        }

        $orderItemUnit->delete();

        return redirect()->back()->with('success', "Alokasi unit {$unitCode} berhasil dibatalkan. Kamu bisa memilih unit pengganti sekarang.");
    }
}