<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ClientRegistration;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $google = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Login dengan Google gagal. Silakan coba lagi.']);
        }

        $user = User::firstOrCreate(
            ['email' => $google->getEmail()],
            [
                'name' => Str::slug($google->getName() ?: Str::before($google->getEmail(), '@')) . rand(100, 999),
                'password' => Hash::make(Str::random(32)),
                'role' => 'admin_rental',
                'status' => 'pending_setup',
            ]
        );

        Auth::login($user, true);

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
