<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Client;
use App\Models\Subscription;
use App\Models\ClientRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class TenantRegistrationController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginField = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';
        
        $credentials = [
            $loginField => $request->email,
            'password'  => $request->password,
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();
            
            if ($user->status === 'menunggu_verifikasi') {
                return redirect()->route('tenant.waiting');
            }

            if ($user->status === 'pending_setup') {
                return redirect()->route('tenant.setup');
            }

            if ($user->status !== 'aktif') {
                Auth::logout();
                return back()->withInput()->with('error', 'Akun Anda sedang tidak aktif.');
            }

            $request->session()->regenerate();
            
            return redirect()->intended('/admin/dashboard');
        }

        return back()->withInput()->withErrors(['email' => 'Username/Email atau password salah.']);
    }

    public function showRegister(Request $request)
    {
        $plan = $request->query('plan', 'starter');
        return view('auth.register', compact('plan'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users,name|alpha_dash',
            'email'    => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'username.alpha_dash' => 'Username tidak boleh mengandung spasi.',
        ]);

        $user = User::create([
            'name'     => $request->username,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'admin_rental',
            'status'   => 'pending_setup',
        ]);

        Auth::login($user);
        $request->session()->put('registration_plan', $request->query('plan', 'starter'));

        return redirect()->route('tenant.setup');
    }

    public function showSetup(Request $request)
    {
        $user = Auth::user();
        if ($user->status !== 'pending_setup') {
            return redirect()->route('admin.dashboard');
        }

        $plan = $request->session()->get('registration_plan', 'starter');
        return view('auth.setup-tenant', compact('plan'));
    }

    public function setup(Request $request)
    {
        $user = Auth::user();
        if ($user->status !== 'pending_setup') {
            return redirect()->route('admin.dashboard');
        }

        $request->validate([
            'business_name' => 'required|string|max:255',
            'subdomain'     => 'required|string|max:255|unique:clients,subdomain|alpha_dash',
            'description'   => 'required|string',
            'logo'          => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'owner_name'    => 'required|string|max:255',
            'phone'         => 'required|string|max:20',
            'address'       => 'required|string',
            'plan_name'     => 'required|string|in:starter,business,professional',
            'terms'         => 'required|accepted',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('clients/logos', 'public');
        }

        DB::beginTransaction();

        try {
            $client = Client::create([
                'business_name' => $request->business_name,
                'description'   => $request->description,
                'logo'          => $logoPath,
                'subdomain'     => strtolower($request->subdomain),
                'status'        => 'menunggu_verifikasi',
            ]);

            $user->update([
                'name'      => $request->owner_name,
                'client_id' => $client->id,
                'phone'     => $request->phone,
                'address'   => $request->address,
                'status'    => 'menunggu_verifikasi',
            ]);

            Subscription::create([
                'client_id'      => $client->id,
                'plan_name'      => $request->plan_name,
                'max_products'   => $this->getMaxProducts($request->plan_name),
                'max_users'      => $this->getMaxUsers($request->plan_name),
                'price'          => $this->getPrice($request->plan_name),
                'billing_cycle'  => 'monthly',
                'start_date'     => now(),
                'end_date'       => now()->addMonth(),
                'status'         => 'menunggu_verifikasi',
                'payment_status' => 'menunggu',
            ]);

            ClientRegistration::create([
                'business_name'  => $request->business_name,
                'description'    => $request->description,
                'subdomain'      => strtolower($request->subdomain),
                'plan_name'      => $request->plan_name,
                'admin_name'     => $request->owner_name,
                'admin_email'    => $user->email,
                'admin_phone'    => $request->phone,
                'admin_password' => $user->password,
                'status'         => 'menunggu_verifikasi',
                'payment_status' => 'menunggu',
            ]);

            DB::commit();

            return redirect()->route('tenant.theme.setup');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan data toko. Silakan coba lagi.');
        }
    }

    private function getMaxProducts($plan)
    {
        return match($plan) {
            'starter' => 50,
            'business' => 200,
            'professional' => 1000,
            default => 50,
        };
    }

    private function getMaxUsers($plan)
    {
        return match($plan) {
            'starter' => 2,
            'business' => 5,
            'professional' => 15,
            default => 2,
        };
    }

    private function getPrice($plan)
    {
        return match($plan) {
            'starter' => 0,
            'business' => 99000,
            'professional' => 249000,
            default => 0,
        };
    }

    public function showThemeSetup()
    {
        $user = Auth::user();
        $client = Client::where('id', $user->client_id)->first();

        if ($user->status !== 'menunggu_verifikasi' || empty($client) || !empty($client->theme_color)) {
            return redirect()->route('login');
        }

        return view('auth.setup-theme');
    }

    public function setupTheme(Request $request)
    {
        $user = Auth::user();
        $client = Client::where('id', $user->client_id)->first();

        if ($user->status !== 'menunggu_verifikasi' || empty($client) || !empty($client->theme_color)) {
            return redirect()->route('login');
        }

        $request->validate([
            'theme_color' => 'required|string|regex:/^#[0-9a-fA-F]{6}$/i',
        ]);

        $client->update([
            'theme_color' => $request->theme_color,
        ]);

        return redirect()->route('tenant.waiting');
    }

    public function showWaiting()
    {
        $user = Auth::user();
        if ($user->status === 'aktif') {
            return redirect()->route('admin.dashboard');
        }
        
        if ($user->status !== 'menunggu_verifikasi') {
            return redirect()->route('login');
        }

        return view('auth.waiting');
    }
}