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

        // Auto-backfill: Cek apakah relasi payment_methods untuk client/tenant saat ini kosong.
        // Jika kosong tapi tabel legacy client_payment_methods memiliki data aktif,
        // otomatis duplikasi/sinkronisasikan ke payment_methods.
        $this->autoBackfillFromLegacyPaymentMethods($client);

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

        $paymentMethod = PaymentMethod::create($validated);
        $this->syncWithLegacyClientPaymentMethod($paymentMethod, 'save');

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

        $oldAccountIdentifier = $paymentMethod->type === 'qris'
            ? ($paymentMethod->account_number ?? ('QRIS-' . $paymentMethod->id))
            : $paymentMethod->account_number;

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
        $this->syncWithLegacyClientPaymentMethod($paymentMethod, 'save', $oldAccountIdentifier);

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

        $oldAccountIdentifier = $paymentMethod->type === 'qris'
            ? ($paymentMethod->account_number ?? ('QRIS-' . $paymentMethod->id))
            : $paymentMethod->account_number;

        // Menjalankan SoftDeletes (mengisi deleted_at tanpa menghapus baris fisik)
        // File gambar QRIS di storage tetap dipertahankan untuk histori order/invoice lama
        $paymentMethod->delete();
        $this->syncWithLegacyClientPaymentMethod($paymentMethod, 'delete', $oldAccountIdentifier);

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

        $oldAccountIdentifier = $paymentMethod->type === 'qris'
            ? ($paymentMethod->account_number ?? ('QRIS-' . $paymentMethod->id))
            : $paymentMethod->account_number;

        $paymentMethod->update([
            'is_active' => ! $paymentMethod->is_active,
        ]);
        $this->syncWithLegacyClientPaymentMethod($paymentMethod, 'save', $oldAccountIdentifier);

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

    /**
     * Auto-backfill: Duplikasi data dari tabel legacy client_payment_methods ke payment_methods
     * jika relasi payment_methods untuk client/tenant saat ini kosong.
     */
    protected function autoBackfillFromLegacyPaymentMethods(Client $client): void
    {
        try {
            if (! \Illuminate\Support\Facades\Schema::hasTable('payment_methods')
                || ! \Illuminate\Support\Facades\Schema::hasTable('client_payment_methods')) {
                return;
            }

            // Cek apakah payment_methods untuk client saat ini kosong
            $hasPaymentMethods = PaymentMethod::where('client_id', $client->id)->exists();
            if ($hasPaymentMethods) {
                return;
            }

            // Ambil data aktif dari tabel legacy client_payment_methods
            $legacyMethods = \App\Models\ClientPaymentMethod::where('client_id', $client->id)
                ->where('is_active', true)
                ->get();

            if ($legacyMethods->isEmpty()) {
                return;
            }

            foreach ($legacyMethods as $legacy) {
                $isQris = str_contains(strtolower((string) $legacy->bank_name), 'qris')
                    || str_starts_with((string) $legacy->account_number, 'QRIS-')
                    || (empty($legacy->account_number) && ! empty($legacy->qris_image));

                PaymentMethod::create([
                    'client_id'          => $client->id,
                    'type'               => $isQris ? 'qris' : 'bank_transfer',
                    'bank_name'          => $isQris ? null : $legacy->bank_name,
                    'account_number'     => $isQris ? null : $legacy->account_number,
                    'account_name'       => $isQris ? null : $legacy->account_holder,
                    'qris_merchant_name' => $isQris ? ($legacy->account_holder ?: ($client->business_name ?? 'QRIS')) : null,
                    'qris_image_path'    => $legacy->qris_image,
                    'is_active'          => (bool) $legacy->is_active,
                ]);
            }

            if ($client->relationLoaded('paymentMethods')) {
                $client->unsetRelation('paymentMethods');
            }
        } catch (\Throwable $e) {
            // Fail-safe handling
        }
    }

    /**
     * Sinkronisasi data ke tabel legacy client_payment_methods agar modul pesanan dan relasi payments tetap aman.
     */
    protected function syncWithLegacyClientPaymentMethod(PaymentMethod $paymentMethod, string $action = 'save', ?string $oldAccountNumber = null): void
    {
        try {
            if (! \Illuminate\Support\Facades\Schema::hasTable('client_payment_methods')) {
                return;
            }

            $currentAccountIdentifier = $paymentMethod->account_number
                ?? ($paymentMethod->type === 'qris' ? ('QRIS-' . $paymentMethod->id) : null);

            $targetAccount = $oldAccountNumber ?? $currentAccountIdentifier;

            if ($action === 'delete') {
                $query = \App\Models\ClientPaymentMethod::where('client_id', $paymentMethod->client_id);

                if ($targetAccount) {
                    $query->where(function ($q) use ($targetAccount, $paymentMethod) {
                        $q->where('account_number', $targetAccount);
                        if ($paymentMethod->type === 'qris') {
                            $q->orWhere('account_number', 'QRIS-' . $paymentMethod->id)
                              ->orWhere('bank_name', 'QRIS');
                        }
                    });
                } elseif ($paymentMethod->type === 'qris') {
                    $query->where(function ($q) use ($paymentMethod) {
                        $q->where('account_number', 'QRIS-' . $paymentMethod->id)
                          ->orWhere('bank_name', 'QRIS');
                    });
                }

                $query->update(['is_active' => false]);
                return;
            }

            // Cari record legacy yang sudah ada untuk di-update, atau buat baru jika belum ada
            $legacyRecord = null;

            // 1. Coba cari berdasarkan $oldAccountNumber jika ada
            if ($oldAccountNumber) {
                $legacyRecord = \App\Models\ClientPaymentMethod::where('client_id', $paymentMethod->client_id)
                    ->where('account_number', $oldAccountNumber)
                    ->first();
            }

            // 2. Jika belum ketemu, coba cari berdasarkan current account number
            if (! $legacyRecord && $currentAccountIdentifier) {
                $legacyRecord = \App\Models\ClientPaymentMethod::where('client_id', $paymentMethod->client_id)
                    ->where('account_number', $currentAccountIdentifier)
                    ->first();
            }

            // 3. Jika bertipe QRIS dan belum ketemu, coba cari berdasarkan identifier QRIS
            if (! $legacyRecord && $paymentMethod->type === 'qris') {
                $legacyRecord = \App\Models\ClientPaymentMethod::where('client_id', $paymentMethod->client_id)
                    ->where(function ($q) use ($paymentMethod) {
                        $q->where('account_number', 'QRIS-' . $paymentMethod->id)
                          ->orWhere('bank_name', 'QRIS');
                    })
                    ->first();
            }

            // 4. Jika bertipe bank_transfer dan belum ketemu, coba cari berdasarkan bank_name dan account_holder
            if (! $legacyRecord && $paymentMethod->type === 'bank_transfer' && $paymentMethod->bank_name) {
                $legacyRecord = \App\Models\ClientPaymentMethod::where('client_id', $paymentMethod->client_id)
                    ->where('bank_name', $paymentMethod->bank_name)
                    ->where('account_holder', $paymentMethod->account_name)
                    ->first();
            }

            $legacyData = [
                'client_id'      => $paymentMethod->client_id,
                'bank_name'      => $paymentMethod->bank_name ?? ($paymentMethod->qris_merchant_name ?: 'QRIS'),
                'account_number' => $paymentMethod->account_number ?? ('QRIS-' . $paymentMethod->id),
                'account_holder' => $paymentMethod->account_name ?? ($paymentMethod->qris_merchant_name ?: 'Merchant QRIS'),
                'qris_image'     => $paymentMethod->qris_image_path,
                'is_active'      => (bool) $paymentMethod->is_active,
            ];

            if ($legacyRecord) {
                $legacyRecord->update($legacyData);
            } else {
                \App\Models\ClientPaymentMethod::create($legacyData);
            }
        } catch (\Throwable $e) {
            // Fail-safe handling
        }
    }
}
