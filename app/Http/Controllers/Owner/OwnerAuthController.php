<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OwnerAuthController extends Controller
{
    /**
     * Menampilkan formulir login khusus Owner Platform.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->role === 'owner') {
                return redirect()->route('owner.dashboard');
            }

            if ($user->role === 'admin_rental') {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('customer.home', ['subdomain' => session('active_client_subdomain', 'babyrental')]);
        }

        return view('owner.owner-login');
    }

    /**
     * Memproses autentikasi Owner Platform.
     */
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

            // Hanya user dengan role 'owner' yang diperbolehkan masuk melalui portal ini
            if ($user->role !== 'owner') {
                Auth::logout();

                return back()->withInput()->withErrors([
                    'email' => 'Akses ditolak. Portal ini khusus untuk Owner Platform. Jika Anda Admin Toko atau Customer, silakan masuk melalui halaman login utama.',
                ]);
            }

            if ($user->status !== 'aktif') {
                Auth::logout();

                return back()->withInput()->withErrors([
                    'email' => 'Akun Owner Anda sedang dinonaktifkan.',
                ]);
            }

            $request->session()->regenerate();

            return redirect()->intended(route('owner.dashboard'))->with('success', 'Selamat datang kembali, Owner Platform!');
        }

        return back()->withInput()->withErrors([
            'email' => 'Email/username atau password yang Anda masukkan salah.',
        ]);
    }

    /**
     * Logout khusus Owner Platform.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('owner.login')->with('success', 'Anda telah berhasil keluar dari Portal Owner.');
    }
}
