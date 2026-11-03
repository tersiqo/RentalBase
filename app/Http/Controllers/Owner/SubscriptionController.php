<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\ClientRegistration;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SubscriptionController extends Controller
{
    /**
     * Tampilkan halaman utama monitoring subscription tenant platform.
     */
    public function index(Request $request)
    {
        $timezone = config('app.timezone', 'Asia/Jakarta');
        $now = Carbon::now($timezone);

        // Update otomatis status subscription yang end_date-nya sudah lewat menjadi kadaluarsa
        Subscription::where('status', 'aktif')
            ->where('end_date', '<', $now)
            ->update(['status' => 'kadaluarsa']);

        // Filter & Search parameters
        $planFilter = strtolower((string) $request->query('plan', 'all'));
        $statusFilter = strtolower((string) $request->query('status', 'all'));
        $search = trim((string) $request->query('search', ''));

        // Query dasar Subscription dengan relasi Client dan Admin User
        $query = Subscription::with(['client.adminUser'])
            ->whereHas('client', function ($q) {
                // Jangan tampilkan tenant yang masih menunggu verifikasi pendaftaran
                $q->where('status', '!=', 'menunggu_verifikasi');
            });

        // Filter berdasarkan Paket (Starter, Business, Professional)
        if ($planFilter !== 'all' && in_array($planFilter, ['starter', 'business', 'professional'])) {
            $query->whereRaw('LOWER(plan_name) = ?', [$planFilter]);
        }

        // Filter berdasarkan Status (active, expiring_soon, expired, suspended, pending)
        if ($statusFilter !== 'all') {
            if ($statusFilter === 'active') {
                $query->where('status', 'aktif')
                    ->where('end_date', '>', $now->copy()->addDays(7));
            } elseif ($statusFilter === 'expiring_soon') {
                $query->where('status', 'aktif')
                    ->where('end_date', '>=', $now)
                    ->where('end_date', '<=', $now->copy()->addDays(7));
            } elseif ($statusFilter === 'expired') {
                $query->where(function ($q) use ($now) {
                    $q->where('status', 'kadaluarsa')
                      ->orWhere('end_date', '<', $now);
                });
            } elseif ($statusFilter === 'suspended') {
                $query->where(function ($q) {
                    $q->where('status', 'nonaktif')
                      ->orWhereHas('client', function ($cq) {
                          $cq->where('status', 'suspended');
                      });
                });
            } elseif ($statusFilter === 'pending' || $statusFilter === 'menunggu_konfirmasi') {
                $query->where(function ($q) {
                    $q->whereIn('status', ['menunggu_konfirmasi', 'menunggu'])
                      ->orWhere('payment_status', 'menunggu');
                });
            }
        }

        // Pencarian berdasarkan Nama Toko atau Subdomain
        if ($search !== '') {
            $query->whereHas('client', function ($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                  ->orWhere('subdomain', 'like', "%{$search}%");
            });
        }

        // Paginasi dengan mempertahankan query string
        $subscriptions = $query->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        // 4 KARTU RINGKASAN METRIK
        // A. Total Tenant Aktif Berlangganan (status aktif & end_date >= now)
        $totalAktif = Subscription::where('status', 'aktif')
            ->where('end_date', '>=', $now)
            ->whereHas('client', fn ($q) => $q->where('status', '!=', 'menunggu_verifikasi'))
            ->distinct('client_id')
            ->count('client_id');

        // B. Tenant Mendekati Jatuh Tempo (aktif & H-7 s/d now)
        $expiringSoon = Subscription::where('status', 'aktif')
            ->where('end_date', '>=', $now)
            ->where('end_date', '<=', $now->copy()->addDays(7))
            ->whereHas('client', fn ($q) => $q->where('status', '!=', 'menunggu_verifikasi'))
            ->count();

        // C. Tenant Kedaluwarsa (end_date < now atau status kadaluarsa)
        // Pastikan tidak menghitung tenant yang memiliki subscription aktif baru
        $expiredCount = Subscription::where(function ($q) use ($now) {
                $q->where('status', 'kadaluarsa')
                  ->orWhere('end_date', '<', $now);
            })
            ->whereHas('client', function ($q) use ($now) {
                $q->where('status', '!=', 'menunggu_verifikasi')
                  // Client tidak memiliki subscription lain yang sedang aktif
                  ->whereDoesntHave('subscriptions', function ($sq) use ($now) {
                      $sq->where('status', 'aktif')->where('end_date', '>=', $now);
                  });
            })
            ->distinct('client_id')
            ->count('client_id');

        // D. Estimasi Pendapatan Bulanan Platform (Monthly Recurring Revenue / MRR)
        // Dihitung berdasarkan normalisasi harga paket per bulan dari tenant-tenant aktif
        $activeSubscriptions = Subscription::where('status', 'aktif')
            ->where('end_date', '>=', $now)
            ->whereHas('client', fn ($q) => $q->where('status', '!=', 'menunggu_verifikasi'))
            ->get();

        $packagesConfig = collect(config('packages', []))->filter(function ($pkg, $k) {
            return is_array($pkg) && isset($pkg['name']) && $k !== 'reserved_subdomains';
        })->all();
        $totalMrr = 0;

        foreach ($activeSubscriptions as $sub) {
            $pKey = strtolower($sub->plan_name);
            $pkg = $packagesConfig[$pKey] ?? null;
            $durationMonths = $pkg['duration_months'] ?? 1;
            $price = $sub->price > 0 ? (float) $sub->price : (float) ($pkg['price'] ?? 0);

            // Normalisasi harga paket per periode menjadi nominal per bulan
            if ($durationMonths > 0) {
                $totalMrr += ($price / $durationMonths);
            }
        }

        $metrics = [
            'total_active'   => $totalAktif,
            'expiring_soon'  => $expiringSoon,
            'expired'        => $expiredCount,
            'estimated_mrr'  => round($totalMrr),
        ];

        $pendaftaranMenunggu = ClientRegistration::where('status', 'menunggu_verifikasi')->count();
        $pendingSubscriptionsCount = Subscription::where('status', 'menunggu_konfirmasi')->orWhere('payment_status', 'menunggu')->count();

        return view('owner.subscriptions.index', compact(
            'subscriptions',
            'metrics',
            'planFilter',
            'statusFilter',
            'search',
            'packagesConfig',
            'pendaftaranMenunggu',
            'pendingSubscriptionsCount'
        ));
    }

    /**
     * Memproses perpanjangan masa aktif subscription berdasarkan konfirmasi pembayaran Owner.
     */
    public function renew(Request $request, $id)
    {
        $timezone = config('app.timezone', 'Asia/Jakarta');
        $now = Carbon::now($timezone);

        $subscription = Subscription::with('client')->findOrFail($id);

        // Validasi input
        $request->validate([
            'reference_number' => 'required|string|max:100',
            'payment_proof'    => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120',
        ], [
            'reference_number.required' => 'Nomor referensi pembayaran / invoice wajib diisi.',
            'reference_number.max'      => 'Nomor referensi maksimal 100 karakter.',
            'payment_proof.required'    => 'Bukti pembayaran wajib diunggah.',
            'payment_proof.file'        => 'File bukti pembayaran tidak valid.',
            'payment_proof.mimes'       => 'Format file bukti pembayaran harus JPG, JPEG, PNG, atau PDF.',
            'payment_proof.max'         => 'Ukuran file bukti pembayaran maksimal 5MB.',
        ]);

        $planKey = strtolower($subscription->plan_name);
        $packageConfig = config("packages.{$planKey}");

        // Durasi perpanjangan mengacu pada konfigurasi resmi paket RentalBase
        // Starter: 3 bulan, Business: 6 bulan, Professional: 12 bulan
        $durationMonths = $packageConfig['duration_months'] ?? 3;
        $price = (float) ($packageConfig['price'] ?? 0);

        // Logika Penghitungan Masa Aktif Baru:
        // A. Jika subscription masih aktif (end_date >= now):
        //    Tanggal akhir baru dihitung dari tanggal akhir yang sedang berlaku ditambah durasi paket
        // B. Jika subscription sudah expired (end_date < now):
        //    Tanggal mulai baru ditetapkan dari waktu sekarang, tanggal akhir baru = now() + durasi
        $isCurrentlyActive = $subscription->end_date && Carbon::parse($subscription->end_date, $timezone)->isFuture();

        if ($isCurrentlyActive) {
            $newStartDate = Carbon::parse($subscription->start_date, $timezone);
            $newEndDate = Carbon::parse($subscription->end_date, $timezone)->copy()->addMonths($durationMonths);
        } else {
            $newStartDate = $now->copy();
            $newEndDate = $now->copy()->addMonths($durationMonths);
        }

        // Simpan file bukti pembayaran ke storage
        $file = $request->file('payment_proof');
        $fileName = 'proof_' . $subscription->id . '_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $disk = config('filesystems.default', 'public');
        $storedPath = $file->storeAs('subscriptions/proofs', $fileName, $disk);

        // Jalankan transaksi database
        DB::transaction(function () use (
            $subscription,
            $newStartDate,
            $newEndDate,
            $storedPath,
            $price,
            $packageConfig,
            $request,
            $durationMonths
        ) {
            // Update subscription
            $subscription->update([
                'start_date'     => $newStartDate,
                'end_date'       => $newEndDate,
                'status'         => 'aktif',
                'payment_status' => 'lunas',
                'payment_proof'  => $storedPath,
                'price'          => $price,
                'max_products'   => $packageConfig['limits']['products'] ?? $subscription->max_products,
                'max_users'      => $packageConfig['limits']['admins'] ?? $subscription->max_users,
            ]);

            // Jika status tenant sebelumnya dinonaktifkan/ditangguhkan karena expired, aktifkan kembali
            if ($subscription->client && $subscription->client->status !== 'aktif') {
                $subscription->client->update(['status' => 'aktif']);
            }

            // Catat ke ActivityLog
            ActivityLog::create([
                'client_id'   => $subscription->client_id,
                'user_id'     => Auth::id(),
                'action'      => 'subscription_renewed',
                'module'      => 'subscriptions',
                'description' => sprintf(
                    'Perpanjangan langganan paket %s (%d bulan). Ref: %s. Periode baru: %s s/d %s oleh %s.',
                    ucfirst($subscription->plan_name),
                    $durationMonths,
                    $request->reference_number,
                    $newStartDate->format('d/m/Y'),
                    $newEndDate->format('d/m/Y'),
                    Auth::user()?->name ?? 'Owner Platform'
                ),
                'ip_address'  => $request->ip(),
                'user_agent'  => $request->userAgent(),
            ]);
        });

        return redirect()->route('owner.subscriptions.index')
            ->with('success', "Masa aktif subscription tenant '{$subscription->client?->business_name}' berhasil diperpanjang selama {$durationMonths} bulan hingga " . $newEndDate->translatedFormat('d F Y') . ".");
    }

    /**
     * Tampilkan riwayat perpanjangan dan aktivitas subscription tenant yang dapat diverifikasi.
     */
    public function history($id)
    {
        $subscription = Subscription::with(['client'])->findOrFail($id);

        // Ambil riwayat aktivitas dari tabel activity_logs
        $logs = ActivityLog::where('client_id', $subscription->client_id)
            ->where(function ($q) {
                $q->where('module', 'subscriptions')
                  ->orWhere('action', 'like', '%subscription%');
            })
            ->with(['user'])
            ->orderByDesc('created_at')
            ->get();

        // Ambil riwayat subscription milik client ini
        $allSubscriptions = Subscription::where('client_id', $subscription->client_id)
            ->orderByDesc('created_at')
            ->get();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'subscription' => $subscription,
                'logs'         => $logs,
                'history'      => $allSubscriptions,
            ]);
        }

        return view('owner.subscriptions.history', compact('subscription', 'logs', 'allSubscriptions'));
    }

    /**
     * Memverifikasi pengajuan perpanjangan / upgrade dari Admin Tenant.
     */
    public function verifyPayment(Request $request, $id)
    {
        $timezone = config('app.timezone', 'Asia/Jakarta');
        $now = Carbon::now($timezone);

        $subscription = Subscription::with('client')->findOrFail($id);

        if ($subscription->status === 'aktif' && $subscription->payment_status === 'diverifikasi') {
            return redirect()->route('owner.subscriptions.index')
                ->with('error', 'Langganan ini sudah diverifikasi dan berstatus aktif.');
        }

        $planKey = strtolower($subscription->plan_name);
        $packageConfig = config("packages.{$planKey}", [
            'duration_months' => 6,
            'price' => 0,
            'limits' => ['products' => 50, 'admins' => 3],
        ]);
        $durationMonths = $packageConfig['duration_months'] ?? 6;

        // Ambil subscription aktif sebelumnya (jika ada)
        $previousActive = Subscription::where('client_id', $subscription->client_id)
            ->where('status', 'aktif')
            ->where('id', '!=', $subscription->id)
            ->latest('id')
            ->first();

        $isUpgrade = $previousActive && strtolower($previousActive->plan_name) !== $planKey;
        $isCurrentlyActive = $previousActive && $previousActive->end_date && Carbon::parse($previousActive->end_date, $timezone)->isFuture();

        // Model Pilihan A:
        // 1. Jika Upgrade: Reset periode penuh terhitung dari HARI INI (now() s/d now() + duration_months)
        // 2. Jika Perpanjang (Paket Sama):
        //    - Jika masih aktif: Tanggal akhir baru = end_date_lama + duration_months (sisa hari TIDAK hangus)
        //    - Jika sudah expired: start_date = now(), end_date = now() + duration_months
        if ($isUpgrade || ! $isCurrentlyActive) {
            $newStartDate = $now->copy();
            $newEndDate = $now->copy()->addMonths($durationMonths);
        } else {
            $newStartDate = Carbon::parse($previousActive->start_date, $timezone);
            $newEndDate = Carbon::parse($previousActive->end_date, $timezone)->copy()->addMonths($durationMonths);
        }

        DB::transaction(function () use ($subscription, $previousActive, $newStartDate, $newEndDate, $durationMonths, $packageConfig, $isUpgrade, $request) {
            // Tandai subscription sebelumnya sebagai diperbarui
            if ($previousActive) {
                $previousActive->update(['status' => 'diperbarui']);
            }

            // Aktifkan subscription ini
            $subscription->update([
                'start_date' => $newStartDate,
                'end_date' => $newEndDate,
                'status' => 'aktif',
                'payment_status' => 'diverifikasi',
                'rejection_reason' => null,
                'max_products' => $packageConfig['limits']['products'] ?? $subscription->max_products,
                'max_users' => $packageConfig['limits']['admins'] ?? $subscription->max_users,
            ]);

            // Re-aktifkan client jika ditangguhkan/suspended
            if ($subscription->client && $subscription->client->status !== 'aktif') {
                $subscription->client->update(['status' => 'aktif']);
            }

            // Catat log audit
            ActivityLog::create([
                'client_id' => $subscription->client_id,
                'user_id' => Auth::id(),
                'action' => $isUpgrade ? 'subscription_upgraded' : 'subscription_renewed',
                'module' => 'subscriptions',
                'description' => sprintf(
                    '%s langganan paket %s (%d bulan) telah diverifikasi dan diaktifkan. Ref: %s. Masa aktif: %s s/d %s.',
                    $isUpgrade ? 'Upgrade' : 'Perpanjangan',
                    ucfirst($subscription->plan_name),
                    $durationMonths,
                    $subscription->notes ?? '-',
                    $newStartDate->format('d/m/Y'),
                    $newEndDate->format('d/m/Y')
                ),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        });

        $actionType = $isUpgrade ? 'Upgrade' : 'Perpanjangan';
        return redirect()->route('owner.subscriptions.index')
            ->with('success', "{$actionType} paket '{$subscription->plan_name}' untuk toko '{$subscription->client?->business_name}' berhasil disetujui & diaktifkan hingga " . $newEndDate->translatedFormat('d F Y') . ".");
    }

    /**
     * Menolak pengajuan perpanjangan / upgrade dari Admin Tenant dengan alasan.
     */
    public function rejectPayment(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ], [
            'rejection_reason.required' => 'Alasan penolakan bukti pembayaran wajib diisi.',
            'rejection_reason.max' => 'Alasan penolakan maksimal 500 karakter.',
        ]);

        $subscription = Subscription::with('client')->findOrFail($id);

        $subscription->update([
            'status' => 'ditolak',
            'payment_status' => 'ditolak',
            'rejection_reason' => $request->rejection_reason,
        ]);

        ActivityLog::create([
            'client_id' => $subscription->client_id,
            'user_id' => Auth::id(),
            'action' => 'subscription_rejected',
            'module' => 'subscriptions',
            'description' => sprintf(
                'Pengajuan langganan paket %s ditolak oleh Owner. Alasan: %s',
                ucfirst($subscription->plan_name),
                $request->rejection_reason
            ),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('owner.subscriptions.index')
            ->with('success', "Pengajuan langganan toko '{$subscription->client?->business_name}' telah ditolak dengan alasan tersimpan.");
    }
}
