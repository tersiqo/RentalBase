<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\DamageCase;
use App\Models\DamageReport;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DamageReportController extends Controller
{
    /**
     * Tampilkan halaman Laporan Kerusakan Customer.
     */
    public function show(Request $request, Order $order)
    {
        // Otorisasi ketat: hanya pemilik order
        if ($order->user_id !== Auth::id()) {
            abort(404, 'Pesanan tidak ditemukan.');
        }

        $order->loadMissing([
            'client.paymentMethods',
            'damageReports.damageCase',
            'damageReports.equipmentUnit.product',
            'damageReports.reporter',
            'payments',
        ]);

        $client = $order->client;
        if (! $client) {
            $client = Client::find($order->client_id) ?? Client::where('status', 'aktif')->first();
        }

        // Ambil laporan kerusakan terbaru untuk order ini
        $damageReport = $order->damageReports()
            ->with(['damageCase', 'equipmentUnit.product', 'reporter'])
            ->latest()
            ->first();

        $damageCase = $damageReport?->damageCase;

        // Ambil metode pembayaran aktif toko (support client_payment_methods dan payment_methods)
        $paymentMethods = ($client?->clientPaymentMethods()->where('is_active', true)->exists())
            ? $client->clientPaymentMethods()->where('is_active', true)->get()
            : ($client?->paymentMethods()->where('is_active', true)->get() ?? collect());

        // Cari riwayat bukti pembayaran kompensasi yang sudah diunggah jika ada
        $compensationPayment = $order->payments()
            ->where('notes', 'LIKE', '%Kompensasi%')
            ->latest()
            ->first();

        return view('customer.damage.show', compact(
            'order',
            'client',
            'damageReport',
            'damageCase',
            'paymentMethods',
            'compensationPayment'
        ));
    }

    /**
     * Kirim catatan tanggapan / klarifikasi dari customer.
     */
    public function respond(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'damage_case_id' => 'required|exists:damage_cases,id',
            'notes' => 'required|string|min:5|max:1500',
        ]);

        $damageCase = DamageCase::with('damageReport')->findOrFail($request->damage_case_id);

        if ($damageCase->damageReport?->order_id !== $order->id) {
            abort(403, 'Kasus kerusakan tidak sesuai dengan pesanan.');
        }

        if (in_array(strtolower($damageCase->case_status), ['selesai', 'lunas'])) {
            return back()->with('error', 'Kasus kerusakan ini telah ditutup / selesai dan tidak dapat ditanggapi lagi.');
        }

        $timestamp = now()->translatedFormat('d M Y, H:i');
        $userName = Auth::user()->name ?? 'Penyewa';
        $newNote = "[Tanggapan {$userName} - {$timestamp} WIB]\n" . trim($request->notes);

        $existingNotes = $damageCase->resolution_notes ? trim($damageCase->resolution_notes) . "\n\n" : '';
        $updatedNotes = $existingNotes . $newNote;

        $damageCase->update([
            'resolution_notes' => $updatedNotes,
            'case_status' => 'dalam_tinjauan',
        ]);

        return back()->with('success', 'Tanggapan Anda berhasil dikirim ke pihak toko. Kasus saat ini dalam peninjauan.');
    }

    /**
     * Unggah bukti pembayaran transfer / QRIS kompensasi kerusakan.
     */
    public function pay(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'damage_case_id' => 'required|exists:damage_cases,id',
            'client_payment_method_id' => 'nullable|exists:client_payment_methods,id',
            'payment_proof' => 'required|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        $damageCase = DamageCase::with('damageReport')->findOrFail($request->damage_case_id);

        if ($damageCase->damageReport?->order_id !== $order->id) {
            abort(403, 'Kasus kerusakan tidak sesuai dengan pesanan.');
        }

        if (! $damageCase->can_pay) {
            return back()->with('error', 'Tagihan kompensasi ini tidak dalam status dapat dibayar (sudah lunas atau sedang diverifikasi).');
        }

        // Simpan bukti transfer ke storage
        $path = $request->file('payment_proof')->store('payments/damage', 'public');

        $paymentMethodId = $request->input('client_payment_method_id')
            ?? $order->client?->clientPaymentMethods()->where('is_active', true)->value('id')
            ?? $order->client?->clientPaymentMethods()->first()?->id
            ?? \App\Models\ClientPaymentMethod::where('is_active', true)->value('id')
            ?? \App\Models\ClientPaymentMethod::first()?->id;

        // Catat di tabel payments
        Payment::create([
            'order_id' => $order->id,
            'client_payment_method_id' => $paymentMethodId,
            'amount' => $damageCase->total_amount,
            'payment_proof' => $path,
            'status' => 'menunggu_konfirmasi',
            'notes' => 'Pembayaran Kompensasi Kerusakan Tiket #' . $damageCase->id,
            'paid_at' => now(),
        ]);

        // Catat riwayat di resolution_notes
        $timestamp = now()->translatedFormat('d M Y, H:i');
        $userName = Auth::user()->name ?? 'Penyewa';
        $notePayment = "[Bukti Pembayaran Diunggah {$userName} - {$timestamp} WIB]\nNominal: {$damageCase->formatted_total}";
        $existingNotes = $damageCase->resolution_notes ? trim($damageCase->resolution_notes) . "\n\n" : '';
        $updatedNotes = $existingNotes . $notePayment;

        $damageCase->update([
            'payment_status' => 'menunggu_verifikasi',
            'case_status' => 'kompensasi_dibayar',
            'resolution_notes' => $updatedNotes,
        ]);

        return back()->with('success', 'Bukti pembayaran kompensasi sebesar ' . $damageCase->formatted_total . ' berhasil dikirim. Toko akan segera memverifikasinya.');
    }
}
