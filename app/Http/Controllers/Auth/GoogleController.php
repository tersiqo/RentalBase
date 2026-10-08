<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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

        // Arahkan ke setup jika baru mendaftar atau status masih pending_setup
        if ($user->status === 'pending_setup') {
            return redirect()->route('tenant.setup');
        }

        return redirect()->intended('/admin/dashboard');
    }
}
