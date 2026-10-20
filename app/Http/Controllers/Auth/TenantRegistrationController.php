<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientRegistration;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class TenantRegistrationController extends Controller
{
    public function showLogin(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->role === 'owner') {
                return redirect()->route('owner.dashboard');
            }

            if ($user->role === 'admin_rental') {
                return redirect()->route('admin.dashboard');
            }

            return redirect('/');
        }

        $activeTab = $request->query('tab', 'login');

        return view('auth.login', compact('activeTab'));
    }

    public function registerCustomer(Request $request)
    {
        if ($request->filled('business_name')) {
            return $this->handleRegistration($request);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'phone.required' => 'Nomor WhatsApp aktif wajib diisi.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal terdiri dari 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        // Security rule: DO NOT accept role, status, or client_id from request
        $user = User::create([
            'client_id' => null,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'customer',
            'status' => 'aktif',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended('/client/babyrental')->with('success', 'Pendaftaran akun berhasil! Anda kini dapat bertransaksi di seluruh toko RentalBase.');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ], [
            'email.required' => 'Email atau username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $loginField = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        $credentials = [
            $loginField => $request->email,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            // Validasi kesesuaian role yang dipilih pada UI (Customer vs Admin)
            $loginType = $request->input('login_type');

            if ($loginType === 'customer' && $user->role !== 'customer') {
                Auth::logout();

                return back()->withInput()->withErrors([
                    'email' => 'Akun ini terdaftar sebagai Pengelola/Admin Toko. Silakan klik tab "Admin" di atas untuk masuk.',
                ]);
            }

            if (in_array($loginType, ['admin', 'tenant'], true) && $user->role === 'customer') {
                Auth::logout();

                return back()->withInput()->withErrors([
                    'email' => 'Akun ini terdaftar sebagai Customer. Silakan klik tab "Customer" di atas untuk masuk.',
                ]);
            }

            if ($user->status === 'menunggu_verifikasi') {
                Auth::logout();

                return back()->withInput()->with('info', 'Pendaftaran berhasil diajukan. Silakan tunggu verifikasi dari Owner. Informasi persetujuan akan dikirimkan melalui email Anda.');
            }

            if ($user->status === 'pending_setup') {
                return redirect()->route('tenant.register');
            }

            if ($user->status !== 'aktif') {
                Auth::logout();

                return back()->withInput()->with('error', 'Akun Anda sedang tidak aktif.');
            }

            $request->session()->regenerate();

            if ($user->role === 'owner') {
                return redirect()->intended(route('owner.dashboard'));
            }

            if ($user->role === 'admin_rental') {
                return redirect()->intended(route('admin.dashboard'));
            }

            if ($user->role === 'customer') {
                $redirectUrl = $request->input('redirect');
                if ($redirectUrl && (str_contains($redirectUrl, '/client/') || str_contains($redirectUrl, '127.0.0.1') || str_contains($redirectUrl, 'localhost'))) {
                    return redirect($redirectUrl);
                }

                $referer = $request->headers->get('referer');
                if ($referer && str_contains($referer, '/client/')) {
                    return redirect($referer);
                }

                return redirect()->intended('/client/babyrental');
            }

            return redirect('/');
        }

        return back()->withInput()->withErrors(['email' => 'Email atau password yang Anda masukkan salah.']);
    }

    public function logout(Request $request)
    {
        $referer = $request->headers->get('referer');
        $activeSubdomain = session('active_client_subdomain');
        $user = Auth::user();

        if (! $activeSubdomain && $user && $user->client_id) {
            $client = Client::find($user->client_id);
            $activeSubdomain = $client?->subdomain;
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->filled('redirect')) {
            return redirect($request->input('redirect'));
        }

        if ($referer && (str_contains($referer, '/my-orders') || str_contains($referer, '/checkout'))) {
            $query = parse_url($referer, PHP_URL_QUERY);
            if ($query) {
                parse_str($query, $queryParams);
                if (! empty($queryParams['client'])) {
                    $activeSubdomain = $queryParams['client'];
                }
            }
            $targetSubdomain = $activeSubdomain ?: 'babyrental';

            return redirect()->route('customer.home', ['subdomain' => $targetSubdomain]);
        }

        if ($referer && ! str_contains($referer, '/admin') && ! str_contains($referer, '/owner')) {
            return redirect($referer);
        }

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar.');
    }

    public function showRegister(Request $request)
    {
        if ($request->session()->has('registration_completed')) {
            return view('auth.waiting');
        }

        if (Auth::check()) {
            $user = Auth::user();

            if ($user->status === 'aktif') {
                return redirect()->route('admin.dashboard');
            }

            if ($user->status === 'menunggu_verifikasi') {
                Auth::logout();

                return redirect()->route('login')->with('info', 'Pendaftaran berhasil diajukan. Silakan tunggu verifikasi dari Owner. Informasi persetujuan akan dikirimkan melalui email Anda.');
            }

            if ($user->status === 'pending_setup') {
                $client = Client::where('id', $user->client_id)->first();
                $plan = $request->session()->get('registration_plan', 'starter');

                if (! $client || $request->query('step') === 'info') {
                    return view('auth.setup-tenant', compact('plan', 'client'));
                }

                if ($request->query('step') === 'theme' || ! $request->session()->get('registration_theme_selected')) {
                    return view('auth.setup-theme', compact('client'));
                }

                $subscription = Subscription::where('client_id', $client->id)->first();
                $planName = $subscription ? $subscription->plan_name : $plan;
                $price = $this->getPrice($planName);
                $planDetails = $this->getPlanDetails($planName);
                $paymentInfo = $this->getPlatformPaymentInfo();

                return view('auth.setup-payment', compact('client', 'planName', 'price', 'planDetails', 'paymentInfo'));
            }
        }

        $plan = $request->query('plan', 'starter');

        return view('auth.register-tenant', compact('plan'));
    }

    public function register(Request $request)
    {
        if ($request->filled('business_name')) {
            $request->validate([
                'username' => 'required|string|max:255|unique:users,name|alpha_dash',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'business_name' => 'required|string|max:255',
                'subdomain' => 'required|string|max:255|unique:clients,subdomain|unique:client_registrations,subdomain|alpha_dash',
                'description' => 'required|string',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'owner_name' => 'nullable|string|max:255',
                'phone' => 'required|string|max:20',
                'address' => 'required|string',
                'plan_name' => 'required|string|in:starter,business,professional',
                'terms' => 'required|accepted',
            ], [
                'username.alpha_dash' => 'Username tidak boleh mengandung spasi.',
                'business_name.required' => 'Nama usaha/toko wajib diisi.',
                'subdomain.required' => 'Subdomain toko wajib diisi.',
                'subdomain.unique' => 'Subdomain ini sudah digunakan, silakan pilih yang lain.',
                'subdomain.alpha_dash' => 'Subdomain hanya boleh berupa huruf, angka, dan tanda hubung (-).',
                'description.required' => 'Deskripsi usaha wajib diisi.',
                'phone.required' => 'Nomor WhatsApp aktif wajib diisi.',
                'address.required' => 'Alamat lengkap wajib diisi.',
                'plan_name.required' => 'Paket toko wajib dipilih.',
                'terms.required' => 'Syarat dan ketentuan wajib disetujui.',
                'terms.accepted' => 'Syarat dan ketentuan wajib disetujui.',
                'logo.max' => 'Ukuran file logo tidak boleh lebih dari 2MB.',
                'logo.image' => 'File logo harus berupa gambar.',
                'logo.mimes' => 'Format file logo harus berupa jpeg, png, jpg, atau gif.',
            ]);

            $logoPath = null;
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('clients/logos', 'public');
            }

            DB::beginTransaction();

            try {
                $ownerName = $request->filled('owner_name') ? $request->owner_name : $request->username;

                $user = User::create([
                    'name' => $ownerName,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'avatar' => $logoPath,
                    'role' => 'admin_rental',
                    'status' => 'menunggu_verifikasi',
                ]);

                $client = Client::create([
                    'business_name' => $request->business_name,
                    'description' => $request->description,
                    'logo' => $logoPath,
                    'subdomain' => strtolower($request->subdomain),
                    'status' => 'menunggu_verifikasi',
                ]);

                $user->update(['client_id' => $client->id]);

                Subscription::create([
                    'client_id' => $client->id,
                    'plan_name' => $request->plan_name,
                    'max_products' => $this->getMaxProducts($request->plan_name),
                    'max_users' => $this->getMaxUsers($request->plan_name),
                    'price' => $this->getPrice($request->plan_name),
                    'billing_cycle' => 'monthly',
                    'start_date' => now(),
                    'end_date' => now()->addMonth(),
                    'status' => 'menunggu_verifikasi',
                    'payment_status' => 'menunggu',
                ]);

                ClientRegistration::create([
                    'business_name' => $request->business_name,
                    'description' => $request->description,
                    'subdomain' => strtolower($request->subdomain),
                    'plan_name' => $request->plan_name,
                    'admin_name' => $ownerName,
                    'admin_email' => $user->email,
                    'admin_phone' => $request->phone,
                    'admin_password' => $user->password,
                    'status' => 'menunggu_verifikasi',
                    'payment_status' => 'menunggu',
                ]);

                DB::commit();

                return redirect()->route('login')->with('success', 'Pendaftaran berhasil diajukan. Silakan tunggu verifikasi dari Owner. Informasi persetujuan akan dikirimkan melalui email Anda.');

            } catch (\Exception $e) {
                DB::rollBack();

                return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan data pendaftaran. Silakan coba lagi.');
            }
        }

        $request->validate([
            'username' => 'required|string|max:255|unique:users,name|alpha_dash',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'username.alpha_dash' => 'Username tidak boleh mengandung spasi.',
        ]);

        $user = User::create([
            'name' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'admin_rental',
            'status' => 'pending_setup',
        ]);

        Auth::login($user);
        $request->session()->put('registration_plan', $request->query('plan', 'starter'));

        return redirect()->route('tenant.register');
    }

    public function showSetup(Request $request)
    {
        if (Auth::check() && Auth::user()->status === 'menunggu_verifikasi') {
            Auth::logout();

            return redirect()->route('login')->with('info', 'Pendaftaran berhasil diajukan. Silakan tunggu verifikasi dari Owner. Informasi persetujuan akan dikirimkan melalui email Anda.');
        }

        return redirect()->route('tenant.register');
    }

    public function setup(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->status !== 'pending_setup') {
            return redirect()->route('admin.dashboard');
        }

        $request->validate([
            'business_name' => 'required|string|max:255',
            'subdomain' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('clients', 'subdomain')->ignore($user->client_id),
                Rule::unique('client_registrations', 'subdomain')->ignore($user->email, 'admin_email'),
            ],
            'description' => 'required|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'owner_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'plan_name' => 'required|string|in:starter,business,professional',
            'terms' => 'required|accepted',
        ], [
            'business_name.required' => 'Nama usaha/toko wajib diisi.',
            'subdomain.required' => 'Subdomain toko wajib diisi.',
            'subdomain.unique' => 'Subdomain ini sudah digunakan, silakan pilih yang lain.',
            'subdomain.alpha_dash' => 'Subdomain hanya boleh berupa huruf, angka, dan tanda hubung (-).',
            'description.required' => 'Deskripsi usaha wajib diisi.',
            'owner_name.required' => 'Nama lengkap PIC wajib diisi.',
            'phone.required' => 'Nomor WhatsApp aktif wajib diisi.',
            'address.required' => 'Alamat lengkap wajib diisi.',
            'plan_name.required' => 'Paket toko wajib dipilih.',
            'terms.required' => 'Syarat dan ketentuan wajib disetujui.',
            'terms.accepted' => 'Syarat dan ketentuan wajib disetujui.',
            'logo.max' => 'Ukuran file logo tidak boleh lebih dari 2MB.',
            'logo.image' => 'File logo harus berupa gambar.',
            'logo.mimes' => 'Format file logo harus berupa jpeg, png, jpg, atau gif.',
        ]);

        $logoPath = $user->avatar;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('clients/logos', 'public');
        }

        DB::beginTransaction();

        try {
            $clientData = [
                'business_name' => $request->business_name,
                'description' => $request->description,
                'logo' => $logoPath,
                'subdomain' => strtolower($request->subdomain),
                'status' => 'menunggu_verifikasi',
            ];

            if ($user->client_id && ($existingClient = Client::find($user->client_id))) {
                $existingClient->update($clientData);
                $client = $existingClient;
            } else {
                $client = Client::create($clientData);
            }

            $user->update([
                'name' => $request->owner_name,
                'client_id' => $client->id,
                'phone' => $request->phone,
                'address' => $request->address,
                'avatar' => $logoPath,
                'status' => 'pending_setup',
            ]);

            Subscription::updateOrCreate(
                ['client_id' => $client->id],
                [
                    'plan_name' => $request->plan_name,
                    'max_products' => $this->getMaxProducts($request->plan_name),
                    'max_users' => $this->getMaxUsers($request->plan_name),
                    'price' => $this->getPrice($request->plan_name),
                    'billing_cycle' => 'monthly',
                    'start_date' => now(),
                    'end_date' => now()->addMonth(),
                    'status' => 'menunggu_verifikasi',
                    'payment_status' => 'menunggu',
                ]
            );

            ClientRegistration::updateOrCreate(
                ['admin_email' => $user->email],
                [
                    'business_name' => $request->business_name,
                    'description' => $request->description,
                    'subdomain' => strtolower($request->subdomain),
                    'plan_name' => $request->plan_name,
                    'admin_name' => $request->owner_name,
                    'admin_phone' => $request->phone,
                    'admin_password' => $user->password,
                    'status' => 'menunggu_verifikasi',
                    'payment_status' => 'menunggu',
                ]
            );

            DB::commit();

            return redirect()->route('tenant.register');

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);

            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan data pendaftaran. Silakan coba lagi.');
        }
    }

    private function getMaxProducts($plan)
    {
        return match ($plan) {
            'starter' => 50,
            'business' => 200,
            'professional' => 1000,
            default => 50,
        };
    }

    private function getMaxUsers($plan)
    {
        return match ($plan) {
            'starter' => 2,
            'business' => 5,
            'professional' => 15,
            default => 2,
        };
    }

    private function getPrice($plan)
    {
        return config("packages.{$plan}.price", match ($plan) {
            'starter' => 0,
            'business' => 99000,
            'professional' => 249000,
            default => 0,
        });
    }

    private function getPlanDetails($plan)
    {
        return match ($plan) {
            'business' => [
                'name' => 'Business',
                'description' => 'Untuk usaha rental dengan kebutuhan operasional yang lebih lengkap.',
                'duration' => '1 Bulan',
            ],
            'professional' => [
                'name' => 'Professional',
                'description' => 'Untuk penggunaan sistem dengan kapasitas dan fitur operasional maksimal.',
                'duration' => '1 Bulan',
            ],
            default => [
                'name' => 'Starter',
                'description' => 'Untuk usaha rental yang baru mulai menggunakan sistem.',
                'duration' => '1 Bulan',
            ],
        };
    }

    private function getPlatformPaymentInfo()
    {
        return [
            'bank_name' => config('services.platform_payment.bank_name', 'BCA (Bank Central Asia)'),
            'account_number' => config('services.platform_payment.account_number', '8830991122'),
            'account_holder' => config('services.platform_payment.account_holder', 'RentalBase Indonesia'),
            'qris_image' => config('services.platform_payment.qris_image', 'images/qris-platform.png'),
        ];
    }

    public function showThemeSetup()
    {
        return redirect()->route('tenant.register', ['step' => 'theme']);
    }

    public function setupTheme(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $client = Client::where('id', $user->client_id)->first();

        $request->validate([
            'theme_color' => 'required|string|regex:/^#[0-9a-fA-F]{6}$/i',
        ]);

        if ($client) {
            $client->update([
                'theme_color' => $request->theme_color,
            ]);
        }

        $request->session()->put('registration_theme_selected', true);

        return redirect()->route('tenant.register');
    }

    public function submitPayment(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->status !== 'pending_setup') {
            return redirect()->route('admin.dashboard');
        }

        $client = Client::where('id', $user->client_id)->first();
        if (! $client) {
            return redirect()->route('tenant.register', ['step' => 'info']);
        }

        $request->validate([
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'payment_proof.required' => 'Bukti pembayaran wajib diunggah sebelum mengirim pengajuan.',
            'payment_proof.image' => 'File bukti pembayaran harus berupa gambar.',
            'payment_proof.mimes' => 'Format file bukti pembayaran harus berupa JPG, JPEG, atau PNG.',
            'payment_proof.max' => 'Ukuran file bukti pembayaran tidak boleh lebih dari 2MB.',
        ]);

        $proofPath = $request->file('payment_proof')->store('subscriptions/proofs', 'public');

        DB::beginTransaction();

        try {
            // Update subscription dengan bukti bayar
            Subscription::where('client_id', $client->id)->update([
                'payment_proof' => $proofPath,
                'payment_status' => 'menunggu',
                'status' => 'menunggu_verifikasi',
            ]);

            // Update client_registrations dengan bukti bayar
            ClientRegistration::where('admin_email', $user->email)->update([
                'payment_proof' => $proofPath,
                'payment_status' => 'menunggu',
                'status' => 'menunggu_verifikasi',
            ]);

            // Set user dan client ke status menunggu_verifikasi
            $user->update([
                'status' => 'menunggu_verifikasi',
            ]);

            $client->update([
                'status' => 'menunggu_verifikasi',
            ]);

            DB::commit();

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $request->session()->put('registration_completed', true);

            return redirect()->route('tenant.register');

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);

            return back()->with('error', 'Terjadi kesalahan saat memproses bukti pembayaran. Silakan coba lagi.');
        }
    }

    public function showWaiting()
    {
        return redirect()->route('tenant.register');
    }

    public function handleRegistration(Request $request)
    {
        if (! Auth::check()) {
            return $this->register($request);
        }

        $user = Auth::user();

        if ($user->status === 'pending_setup') {
            if ($request->hasFile('payment_proof') || $request->input('action_step') === 'payment') {
                return $this->submitPayment($request);
            }

            if ($request->filled('theme_color')) {
                return $this->setupTheme($request);
            }

            return $this->setup($request);
        }

        if ($user->status === 'menunggu_verifikasi') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('info', 'Pendaftaran berhasil diajukan. Silakan tunggu verifikasi dari Owner. Informasi persetujuan akan dikirimkan melalui email Anda.');
        }

        return redirect()->route('tenant.register');
    }
}
