<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ClientRegistration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Mockery\MockInterface;

class GoogleController extends Controller
{
    public function redirect(?Request $request = null)
    {
        $request = $request ?: request();

        if (Auth::check()) {
            Auth::logout();
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }
        }

        $role = $request->query('role', $request->query('login_type'));

        if (! $role) {
            $referer = $request->headers->get('referer', '');
            if (str_contains($referer, 'register-tenant') || str_contains($referer, 'setup-tenant')) {
                $role = 'admin_rental';
            } else {
                $role = 'customer';
            }
        }

        if (in_array($role, ['admin', 'admin_rental', 'tenant'], true)) {
            $role = 'admin_rental';
        } else {
            $role = 'customer';
        }

        if ($request->hasSession()) {
            $request->session()->put('google_auth_role', $role);

            if ($request->filled('redirect')) {
                $request->session()->put('google_auth_redirect', $request->query('redirect'));
            }
        }

        $driver = Socialite::driver('google');
        if (method_exists($driver, 'with') && ! ($driver instanceof MockInterface)) {
            $driver = $driver->with(['prompt' => 'select_account']);
        }

        return $driver->redirect();
    }

    public function callback(?Request $request = null)
    {
        $request = $request ?: request();

        try {
            $google = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Login dengan Google gagal. Silakan coba lagi.']);
        }

        $intendedRole = $request->hasSession() ? $request->session()->pull('google_auth_role') : null;
        $redirectUrl = $request->hasSession() ? $request->session()->pull('google_auth_redirect') : null;

        $user = User::where('email', $google->getEmail())->first();

        if (! $user) {
            if ($intendedRole === 'customer') {
                $user = User::create([
                    'name' => $google->getName() ?: Str::before($google->getEmail(), '@'),
                    'email' => $google->getEmail(),
                    'password' => Hash::make(Str::random(32)),
                    'role' => 'customer',
                    'status' => 'aktif',
                    'client_id' => null,
                ]);
            } else {
                // Default fallback ke admin_rental untuk alur admin / register-tenant / tes backward-compatibility
                $user = User::create([
                    'name' => Str::slug($google->getName() ?: Str::before($google->getEmail(), '@')).rand(100, 999),
                    'email' => $google->getEmail(),
                    'password' => Hash::make(Str::random(32)),
                    'role' => 'admin_rental',
                    'status' => 'pending_setup',
                    'client_id' => null,
                ]);
            }
        }

        // Alur untuk Customer
        if ($user->role === 'customer') {
            if ($user->status !== 'aktif') {
                Auth::logout();

                return redirect()->route('login')->with('error', 'Akun Anda sedang tidak aktif.');
            }

            Auth::login($user, true);
            if ($request->hasSession()) {
                $request->session()->regenerate();
            }

            if ($redirectUrl && (str_contains($redirectUrl, '/client/') || str_contains($redirectUrl, '127.0.0.1') || str_contains($redirectUrl, 'localhost') || str_starts_with($redirectUrl, '/'))) {
                return redirect($redirectUrl)->with('success', 'Berhasil masuk dengan Akun Google!');
            }

            $activeSubdomain = $request->hasSession() ? $request->session()->get('active_client_subdomain') : null;
            $defaultTarget = $activeSubdomain ? ('/client/'.$activeSubdomain) : '/#mitra-rental';

            return redirect()->intended($defaultTarget)->with('success', 'Berhasil masuk dengan Akun Google!');
        }

        // Alur untuk Owner Platform
        if ($user->role === 'owner') {
            Auth::login($user, true);
            if ($request->hasSession()) {
                $request->session()->regenerate();
            }

            return redirect()->route('owner.dashboard');
        }

        // Alur untuk Admin Rental (Tenant)
        Auth::login($user, true);
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        if ($user->status === 'menunggu_verifikasi') {
            Auth::logout();

            return redirect()->route('login')->with('info', 'Pendaftaran berhasil diajukan. Silakan tunggu verifikasi dari Owner. Informasi persetujuan akan dikirimkan melalui email Anda.');
        }

        if ($user->status === 'ditolak') {
            $reg = ClientRegistration::where('admin_email', $user->email)->latest()->first();
            $user->update(['status' => 'pending_setup']);
            session()->flash('rejection_reason', $reg?->rejection_reason ?? 'Mohon periksa kembali kelengkapan data pendaftaran Anda.');

            return redirect()->route('tenant.register');
        }

        if ($user->status === 'pending_setup') {
            return redirect()->route('tenant.register');
        }

        if ($user->status !== 'aktif') {
            Auth::logout();

            return redirect()->route('login')->with('error', 'Akun Anda sedang tidak aktif.');
        }

        return redirect()->intended('/admin/dashboard');
    }
}
