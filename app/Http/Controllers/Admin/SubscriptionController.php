<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Client;
use App\Models\EquipmentUnit;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class SubscriptionController extends Controller
{
    /**
     * Tampilkan detail status langganan, progress kuota, dan opsi perpanjang/upgrade.
     */
    public function index()
    {
        $client = $this->resolveClient();
        $timezone = config('app.timezone', 'Asia/Jakarta');
        $now = Carbon::now($timezone);

        $subscription = $client->activeSubscription()->first() ?? $client->latestSubscription;
        $history = Subscription::where('client_id', $client->id)
            ->orderByDesc('created_at')
            ->get();

        $pendingRequest = Subscription::where('client_id', $client->id)
            ->whereIn('status', ['menunggu_konfirmasi', 'menunggu'])
            ->latest('id')
            ->first();

        // Penggunaan kuota aktual toko
        $usedProducts = Product::where('client_id', $client->id)->count();
        $usedUnits = EquipmentUnit::where('client_id', $client->id)->count();
        $usedCategories = Category::where('client_id', $client->id)->count();
        $usedAdmins = User::where('client_id', $client->id)->where('role', 'admin_rental')->count();

        $packagesConfig = collect(config('packages', []))->filter(function ($pkg, $k) {
            return is_array($pkg) && isset($pkg['name']) && $k !== 'reserved_subdomains';
        })->all();
        $currentPlanKey = strtolower($subscription?->plan_name ?? 'starter');
        $currentPackage = $packagesConfig[$currentPlanKey] ?? ($packagesConfig['starter'] ?? []);

        // Hitung sisa hari aktif
        $daysRemaining = 0;
        $isExpired = false;
        if ($subscription && $subscription->end_date) {
            $endDate = Carbon::parse($subscription->end_date, $timezone);
            if ($endDate->isPast()) {
                $isExpired = true;
                $daysRemaining = 0;
            } else {
                $daysRemaining = (int) $now->diffInDays($endDate, false);
            }
        }

        return view('admin.subscriptions.index', compact(
            'client',
            'subscription',
            'history',
            'pendingRequest',
            'usedProducts',
            'usedUnits',
            'usedCategories',
            'usedAdmins',
            'packagesConfig',
            'currentPlanKey',
            'currentPackage',
            'daysRemaining',
            'isExpired'
        ));
    }

    /**
     * Mengajukan perpanjangan paket yang sama ke Owner.
     */
    public function requestRenew(Request $request)
    {
        $client = $this->resolveClient();
        $this->authorizePrimaryAdmin();

        $request->validate([
            'payment_proof' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'notes'          => 'nullable|string|max:500',
        ], [
            'payment_proof.required' => 'Bukti transfer pembayaran perpanjangan wajib diunggah.',
            'payment_proof.mimes'    => 'Format bukti pembayaran harus JPG, PNG, atau PDF.',
            'payment_proof.max'      => 'Ukuran file bukti pembayaran maksimal 5MB.',
        ]);

        $currentSub = $client->activeSubscription()->first() ?? $client->latestSubscription;
        $planKey = strtolower($currentSub?->plan_name ?? 'starter');
        $packageConfig = config("packages.{$planKey}", config('packages.starter'));
        $durationMonths = $packageConfig['duration_months'] ?? 3;
        $price = (float) ($packageConfig['price'] ?? 0);

        // Upload bukti pembayaran
        $disk = config('filesystems.default', 'public');
        $proofPath = $request->file('payment_proof')->store('subscriptions/proofs', $disk);

        $newSubscription = Subscription::create([
            'client_id'      => $client->id,
            'plan_name'      => $packageConfig['name'] ?? ucfirst($planKey),
            'max_products'   => $packageConfig['limits']['products'] ?? 10,
            'max_users'      => $packageConfig['limits']['admins'] ?? 1,
            'price'          => $price,
            'billing_cycle'  => 'monthly',
            'start_date'     => now(),
            'end_date'       => now()->addMonths($durationMonths),
            'status'         => 'menunggu_konfirmasi',
            'payment_status' => 'menunggu',
            'payment_proof'  => $proofPath,
            'notes'          => $request->notes,
        ]);

        ActivityLog::create([
            'client_id'   => $client->id,
            'user_id'     => Auth::id(),
            'action'      => 'subscription_renewal_requested',
            'module'      => 'subscriptions',
            'description' => sprintf(
                'Mengajukan perpanjangan paket %s (%d bulan). Menunggu verifikasi Owner Platform.',
                $newSubscription->plan_name,
                $durationMonths
            ),
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
        ]);

        if (class_exists(\App\Events\SubscriptionRenewalUploaded::class)) {
            event(new \App\Events\SubscriptionRenewalUploaded($newSubscription));
        }

        return redirect()->route('admin.subscriptions.index')
            ->with('success', 'Pengajuan perpanjangan langganan berhasil dikirim. Tim Owner akan segera memverifikasi pembayaran Anda.');
    }

    /**
     * Mengajukan upgrade ke paket yang lebih tinggi ke Owner.
     */
    public function requestUpgrade(Request $request)
    {
        $client = $this->resolveClient();
        $this->authorizePrimaryAdmin();

        $request->validate([
            'plan_name'     => 'required|string|in:business,professional',
            'payment_proof' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'notes'         => 'nullable|string|max:500',
        ], [
            'plan_name.required'     => 'Silakan pilih paket tujuan upgrade.',
            'plan_name.in'           => 'Paket tujuan hanya boleh Business atau Professional.',
            'payment_proof.required' => 'Bukti transfer pembayaran upgrade wajib diunggah.',
            'payment_proof.mimes'    => 'Format bukti pembayaran harus JPG, PNG, atau PDF.',
            'payment_proof.max'      => 'Ukuran file bukti pembayaran maksimal 5MB.',
        ]);

        $targetPlanKey = strtolower($request->plan_name);
        $packageConfig = config("packages.{$targetPlanKey}");

        if (! $packageConfig) {
            throw ValidationException::withMessages([
                'plan_name' => 'Paket langganan tidak valid.',
            ]);
        }

        $durationMonths = $packageConfig['duration_months'] ?? 6;
        $price = (float) ($packageConfig['price'] ?? 0);

        // Upload bukti pembayaran
        $disk = config('filesystems.default', 'public');
        $proofPath = $request->file('payment_proof')->store('subscriptions/proofs', $disk);

        $newSubscription = Subscription::create([
            'client_id'      => $client->id,
            'plan_name'      => $packageConfig['name'] ?? ucfirst($targetPlanKey),
            'max_products'   => $packageConfig['limits']['products'] ?? null,
            'max_users'      => $packageConfig['limits']['admins'] ?? 3,
            'price'          => $price,
            'billing_cycle'  => 'monthly',
            'start_date'     => now(),
            'end_date'       => now()->addMonths($durationMonths),
            'status'         => 'menunggu_konfirmasi',
            'payment_status' => 'menunggu',
            'payment_proof'  => $proofPath,
            'notes'          => $request->notes,
        ]);

        ActivityLog::create([
            'client_id'   => $client->id,
            'user_id'     => Auth::id(),
            'action'      => 'subscription_upgrade_requested',
            'module'      => 'subscriptions',
            'description' => sprintf(
                'Mengajukan upgrade ke paket %s (%d bulan). Menunggu verifikasi Owner Platform.',
                $newSubscription->plan_name,
                $durationMonths
            ),
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
        ]);

        if (class_exists(\App\Events\SubscriptionRenewalUploaded::class)) {
            event(new \App\Events\SubscriptionRenewalUploaded($newSubscription));
        }

        return redirect()->route('admin.subscriptions.index')
            ->with('success', "Pengajuan upgrade ke paket {$newSubscription->plan_name} berhasil dikirim. Kuota paket baru akan langsung aktif setelah disetujui Owner.");
    }

    private function resolveClient(): Client
    {
        $user = Auth::user();

        if (! $user) {
            $user = User::whereNotNull('client_id')->first();
        }

        $client = $user?->client;

        if (! $client) {
            abort(403, 'Akses ditolak: Akun tidak terhubung ke toko rental manapun.');
        }

        return $client;
    }

    private function authorizePrimaryAdmin(): void
    {
        $user = Auth::user();
        if ($user && method_exists($user, 'isPrimaryAdmin') && ! $user->isPrimaryAdmin()) {
            abort(403, 'Akses ditolak: Hanya Admin Utama yang memiliki wewenang untuk mengajukan perpanjangan atau upgrade langganan toko.');
        }
    }
}
