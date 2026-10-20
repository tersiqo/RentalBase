<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminStaffController extends Controller
{
    /**
     * Tampilkan halaman daftar staf admin dan indikator kuota paket.
     */
    public function index()
    {
        if (! Auth::user()?->isPrimaryAdmin()) {
            abort(403, 'Akses ditolak: Hanya Admin Utama yang berwenang mengelola akun staf toko.');
        }

        $client = $this->resolveClient();
        $currentUser = Auth::user();

        $subscription = $client->activeSubscription()->first();
        $planInfo = $this->resolvePlanInfo($subscription);

        $maxAdmins = $planInfo['maxAdmins'];
        $planName  = $planInfo['planName'];

        // Ambil seluruh akun admin milik tenant ini (hanya role admin_rental)
        $admins = User::where('client_id', $client->id)
            ->where('role', 'admin_rental')
            ->orderBy('id', 'asc')
            ->get();

        $activeAdminsCount = $admins->where('status', 'aktif')->count();
        $totalAdminsCount  = $admins->count();
        $remainingQuota    = max(0, $maxAdmins - $activeAdminsCount);
        $isQuotaFull       = $activeAdminsCount >= $maxAdmins;
        $usagePercentage   = $maxAdmins > 0 ? min(100, round(($activeAdminsCount / $maxAdmins) * 100)) : 100;

        return view('admin.admins.index', compact(
            'client',
            'currentUser',
            'subscription',
            'admins',
            'planName',
            'maxAdmins',
            'activeAdminsCount',
            'totalAdminsCount',
            'remainingQuota',
            'isQuotaFull',
            'usagePercentage'
        ));
    }

    /**
     * Simpan akun staf Admin Rental baru.
     */
    public function store(Request $request)
    {
        if (! Auth::user()?->isPrimaryAdmin()) {
            abort(403, 'Akses ditolak: Hanya Admin Utama yang berwenang menambah akun staf toko.');
        }

        $client = $this->resolveClient();
        $subscription = $client->activeSubscription()->first();
        $planInfo = $this->resolvePlanInfo($subscription);

        $maxAdmins = $planInfo['maxAdmins'];
        $planName  = $planInfo['planName'];

        // Validasi kuota backend: tolak jika kuota admin aktif sudah penuh
        $activeAdminsCount = User::where('client_id', $client->id)
            ->where('role', 'admin_rental')
            ->where('status', 'aktif')
            ->count();

        if ($activeAdminsCount >= $maxAdmins) {
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('error', "Kuota Admin aktif untuk paket {$planName} telah mencapai batas ({$maxAdmins} akun aktif). Nonaktifkan staf yang tidak aktif atau hubungi Owner untuk upgrade paket.");
        }

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required'     => 'Nama lengkap Admin wajib diisi.',
            'name.max'          => 'Nama lengkap Admin maksimal 255 karakter.',
            'email.required'    => 'Alamat email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Email ini sudah terdaftar dalam sistem RentalBase. Silakan gunakan email lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal harus 8 karakter.',
            'password.confirmed'=> 'Konfirmasi password tidak cocok dengan password yang dimasukkan.',
        ]);

        // Buat akun staf admin baru dengan role admin_rental terikat ke client_id toko login
        try {
            User::create([
                'client_id' => $client->id,
                'name'      => trim($validated['name']),
                'email'     => strtolower(trim($validated['email'])),
                'password'  => Hash::make($validated['password']),
                'role'      => 'admin_rental',
                'status'    => 'aktif',
            ]);
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            // Jika sequence PostgreSQL tertinggal (users_pkey), sinkronkan sequence ke MAX(id) lalu coba kembali
            if (str_contains($e->getMessage(), 'users_pkey')) {
                \Illuminate\Support\Facades\DB::statement("SELECT setval(pg_get_serial_sequence('users', 'id'), COALESCE((SELECT MAX(id) FROM users), 1))");

                User::create([
                    'client_id' => $client->id,
                    'name'      => trim($validated['name']),
                    'email'     => strtolower(trim($validated['email'])),
                    'password'  => Hash::make($validated['password']),
                    'role'      => 'admin_rental',
                    'status'    => 'aktif',
                ]);
            } else {
                throw $e;
            }
        }

        return redirect()
            ->route('admin.admins.index')
            ->with('success', "Akun Admin '{$validated['name']}' berhasil dibuat dan telah aktif.");
    }

    /**
     * Aktifkan atau nonaktifkan akun Admin.
     */
    public function toggleStatus(Request $request, User $user)
    {
        if (! Auth::user()?->isPrimaryAdmin()) {
            abort(403, 'Akses ditolak: Hanya Admin Utama yang berwenang mengubah status akun staf toko.');
        }

        $client = $this->resolveClient();
        $currentUser = Auth::user();

        // 1. Otorisasi ketat: user target harus milik tenant yang sama dan ber-role admin_rental
        if ((int) $user->client_id !== (int) $client->id || $user->role !== 'admin_rental') {
            abort(403, 'Akses ditolak: Anda tidak memiliki wewenang untuk mengelola akun ini.');
        }

        // 2. Cegah penonaktifan akun Admin Utama
        if ($user->isPrimaryAdmin()) {
            return back()->with('error', 'Akun Admin Utama tidak dapat dinonaktifkan.');
        }

        // 3. Cegah penonaktifan akun sendiri
        if ((int) $user->id === (int) $currentUser->id) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Admin Anda sendiri demi keamanan sesi Anda.');
        }

        $subscription = $client->activeSubscription()->first();
        $planInfo = $this->resolvePlanInfo($subscription);
        $maxAdmins = $planInfo['maxAdmins'];
        $planName  = $planInfo['planName'];

        $targetStatus = $request->input('status');
        if ($targetStatus && !in_array($targetStatus, ['aktif', 'nonaktif'], true)) {
            return back()->with('error', 'Status akun yang diminta tidak valid.');
        }

        // Jika tidak dispesifikasikan, lakukan toggle berdasarkan status saat ini
        $newStatus = $targetStatus ?: ($user->status === 'aktif' ? 'nonaktif' : 'aktif');

        if ($newStatus === 'nonaktif') {
            $user->update(['status' => 'nonaktif']);

            return redirect()
                ->route('admin.admins.index')
                ->with('success', "Akun Admin '{$user->name}' berhasil dinonaktifkan. Akun ini tidak lagi menggunakan kuota aktif dan tidak dapat login ke sistem.");
        }

        // Jika mengaktifkan kembali, periksa kuota admin aktif
        $activeAdminsCount = User::where('client_id', $client->id)
            ->where('role', 'admin_rental')
            ->where('status', 'aktif')
            ->count();

        if ($activeAdminsCount >= $maxAdmins) {
            return redirect()
                ->route('admin.admins.index')
                ->with('error', "Gagal mengaktifkan akun '{$user->name}'. Kuota Admin aktif untuk paket {$planName} telah penuh ({$maxAdmins} akun). Nonaktifkan staf lain terlebih dahulu sebelum mengaktifkan akun ini.");
        }

        $user->update(['status' => 'aktif']);

        return redirect()
            ->route('admin.admins.index')
            ->with('success', "Akun Admin '{$user->name}' berhasil diaktifkan kembali.");
    }

    /**
     * Dapatkan informasi limit kuota admin dari config/packages.php.
     *
     * @return array{planKey: string, planName: string, maxAdmins: int}
     */
    private function resolvePlanInfo(?Subscription $subscription): array
    {
        $rawPlan = $subscription ? (string) $subscription->plan_name : 'Starter';
        $planLower = mb_strtolower(trim($rawPlan));

        if (str_contains($planLower, 'pro')) {
            $planKey = 'professional';
        } elseif (str_contains($planLower, 'bus')) {
            $planKey = 'business';
        } elseif (str_contains($planLower, 'start')) {
            $planKey = 'starter';
        } elseif (config("packages.{$planLower}")) {
            $planKey = $planLower;
        } else {
            $planKey = 'starter';
        }

        $planConfig = config("packages.{$planKey}") ?? config('packages.starter');
        $planName   = $subscription?->plan_name ?: ($planConfig['name'] ?? ucfirst($planKey));
        $maxAdmins  = (int) ($planConfig['limits']['admins'] ?? 1);

        return [
            'planKey'   => $planKey,
            'planName'  => $planName,
            'maxAdmins' => $maxAdmins,
        ];
    }

    /**
     * Pastikan user yang login terhubung ke tenant/client.
     */
    private function resolveClient(): Client
    {
        $user = Auth::user();

        if (! $user) {
            $user = User::whereNotNull('client_id')->first();
        }

        $client = $user?->client;

        if (! $client) {
            abort(403, 'Akses ditolak: Akun Anda tidak terhubung ke entitas toko / tenant mana pun.');
        }

        return $client;
    }
}
