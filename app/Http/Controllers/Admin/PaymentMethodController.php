<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentMethodRequest;
use App\Models\Client;
use App\Models\PaymentMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PaymentMethodController extends Controller
{
    /**
     * Menampilkan daftar metode pembayaran milik tenant yang sedang login.
     * Query selalu scoped ke client_id milik admin.
     */
    public function index(Request $request): View
    {
        $client = $this->resolveCurrentClient();

        // Query selalu scoped ke client_id
        $paymentMethods = PaymentMethod::where('client_id', $client->id)
            ->orderByDesc('is_active')
            ->latest()
            ->get();

        $viewName = view()->exists('admin.settings.payments.index')
            ? 'admin.settings.payments.index'
            : (view()->exists('admin.payments.index') ? 'admin.payments.index' : 'admin.settings.payments.index');

        return view($viewName, compact('client', 'paymentMethods'));
    }

    /**
     * Menyimpan metode pembayaran baru.
     * Scoped ke client_id admin yang login.
     */
    public function store(PaymentMethodRequest $request): RedirectResponse
    {
        $client = $this->resolveCurrentClient();

        $validated = $request->validated();
        $validated['client_id'] = $client->id;
        $validated['is_active'] = $request->boolean('is_active', true);

        // Standardisasi alias field merchant_name
        if (! empty($validated['merchant_name']) && empty($validated['qris_merchant_name'])) {
            $validated['qris_merchant_name'] = $validated['merchant_name'];
        }
        unset($validated['merchant_name']);

        // Upload gambar barcode QRIS ke disk supabase / s3 jika tipe qris
        if ($validated['type'] === 'qris' && $request->hasFile('qris_image')) {
            $disk = Storage::disk($this->resolveStorageDisk());
            $file = $request->file('qris_image');
            $extension = $file->getClientOriginalExtension();
            $filename = 'qris-' . $client->id . '-' . time() . '.' . $extension;

            $storedPath = $disk->putFileAs('payment-qris', $file, $filename, 'public');
            $validated['qris_image_path'] = $storedPath;
        }

        unset($validated['qris_image']);

        PaymentMethod::create($validated);

        return redirect()
            ->back()
            ->with('success', 'Metode pembayaran baru berhasil ditambahkan.');
    }

    /**
     * Mengambil detail metode pembayaran untuk diedit.
     * Diproteksi scoping client_id.
     */
    public function edit(PaymentMethod $paymentMethod)
    {
        $client = $this->resolveCurrentClient();
        $this->authorizePaymentMethod($client, $paymentMethod);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'data'    => $paymentMethod,
                'qris_url'=> $paymentMethod->qris_image_path ? Storage::url($paymentMethod->qris_image_path) : null,
            ]);
        }

        $viewName = view()->exists('admin.settings.payments.edit')
            ? 'admin.settings.payments.edit'
            : 'admin.settings.payments.index';

        return view($viewName, compact('client', 'paymentMethod'));
    }

    /**
     * Memperbarui metode pembayaran.
     * Diproteksi scoping client_id.
     */
    public function update(PaymentMethodRequest $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $client = $this->resolveCurrentClient();
        $this->authorizePaymentMethod($client, $paymentMethod);

        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active', $paymentMethod->is_active);

        // Standardisasi alias field merchant_name
        if (! empty($validated['merchant_name']) && empty($validated['qris_merchant_name'])) {
            $validated['qris_merchant_name'] = $validated['merchant_name'];
        }
        unset($validated['merchant_name']);

        // Handle update file gambar QRIS
        if ($validated['type'] === 'qris' && $request->hasFile('qris_image')) {
            $disk = Storage::disk($this->resolveStorageDisk());

            // Hapus file gambar QRIS lama dari storage jika ada
            if (! empty($paymentMethod->qris_image_path)) {
                $this->deleteOldQrisImage($paymentMethod->qris_image_path);
            }

            $file = $request->file('qris_image');
            $extension = $file->getClientOriginalExtension();
            $filename = 'qris-' . $client->id . '-' . time() . '.' . $extension;

            $storedPath = $disk->putFileAs('payment-qris', $file, $filename, 'public');
            $validated['qris_image_path'] = $storedPath;
        }

        unset($validated['qris_image']);

        $paymentMethod->update($validated);

        return redirect()
            ->back()
            ->with('success', 'Metode pembayaran berhasil diperbarui.');
    }

    /**
     * Menghapus metode pembayaran menggunakan SoftDeletes.
     * WAJIB menggunakan metode delete bawaan model agar ter-trigger SoftDeletes.
     */
    public function destroy(PaymentMethod $paymentMethod): RedirectResponse
    {
        $client = $this->resolveCurrentClient();
        $this->authorizePaymentMethod($client, $paymentMethod);

        // Menjalankan SoftDeletes (mengisi deleted_at tanpa menghapus baris fisik)
        // File gambar QRIS di storage tetap dipertahankan untuk histori order/invoice lama
        $paymentMethod->delete();

        return redirect()
            ->back()
            ->with('success', 'Metode pembayaran berhasil dihapus.');
    }

    /**
     * Mengubah status aktif / nonaktif metode pembayaran dengan cepat.
     */
    public function toggleStatus(PaymentMethod $paymentMethod): RedirectResponse
    {
        $client = $this->resolveCurrentClient();
        $this->authorizePaymentMethod($client, $paymentMethod);

        $paymentMethod->update([
            'is_active' => ! $paymentMethod->is_active,
        ]);

        $statusText = $paymentMethod->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->back()
            ->with('success', "Metode pembayaran berhasil {$statusText}.");
    }

    /**
     * Mendapatkan Client milik admin yang sedang login.
     */
    protected function resolveCurrentClient(): Client
    {
        $user = Auth::user();
        $client = $user?->client ?? $user?->tenant;

        if (! $client) {
            abort(403, 'Akses ditolak: Akun Anda tidak terhubung ke tenant/toko rental manapun.');
        }

        return $client;
    }

    /**
     * Verifikasi bahwa record metode pembayaran benar-benar milik client_id admin yang login.
     */
    protected function authorizePaymentMethod(Client $client, PaymentMethod $paymentMethod): void
    {
        if ((int) $paymentMethod->client_id !== (int) $client->id) {
            abort(403, 'Akses ditolak: Anda tidak memiliki wewenang atas metode pembayaran toko lain.');
        }
    }

    /**
     * Menentukan disk storage untuk upload file (supabase -> s3 -> default).
     */
    protected function resolveStorageDisk(): string
    {
        if (config('filesystems.disks.supabase')) {
            return 'supabase';
        }

        if (config('filesystems.disks.s3')) {
            return 's3';
        }

        return config('filesystems.default', 'public');
    }

    /**
     * Menghapus gambar barcode QRIS lama dari storage.
     */
    protected function deleteOldQrisImage(?string $path): void
    {
        if (empty($path)) {
            return;
        }

        $disksToCheck = array_unique([
            $this->resolveStorageDisk(),
            'supabase',
            's3',
            'public',
            config('filesystems.default', 'public'),
        ]);

        foreach ($disksToCheck as $diskName) {
            if (config("filesystems.disks.{$diskName}")) {
                try {
                    if (Storage::disk($diskName)->exists($path)) {
                        Storage::disk($diskName)->delete($path);
                        break;
                    }
                } catch (\Throwable $e) {
                    // Fail-safe handling
                }
            }
        }
    }
}
