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

        // Filter berdasarkan Status (active, expiring_soon, expired, suspended)
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

        $packagesConfig = config('packages', []);
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

        return view('owner.subscriptions.index', compact(
            'subscriptions',
            'metrics',
            'planFilter',
            'statusFilter',
            'search',
            'packagesConfig',
            'pendaftaranMenunggu'
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

        // Simpan file bukti pembayaran ke storage publik
        $file = $request->file('payment_proof');
        $fileName = 'proof_' . $subscription->id . '_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $storedPath = $file->storeAs('subscriptions/proofs', $fileName, 'public');

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
}
