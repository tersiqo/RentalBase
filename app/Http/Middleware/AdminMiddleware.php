<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Pastikan user sudah login dan memiliki role admin_rental atau owner.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect('/')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = Auth::user();

        if ($user->status === 'menunggu_verifikasi') {
            Auth::logout();

            return redirect()->route('login')->with('info', 'Pendaftaran berhasil diajukan. Silakan tunggu verifikasi dari Owner. Informasi persetujuan akan dikirimkan melalui email Anda.');
        }

        if ($user->status === 'pending_setup') {
            return redirect()->route('tenant.register');
        }

        if ($user->status !== 'aktif') {
            Auth::logout();
            return redirect('/')->with('error', 'Akun tidak aktif.');
        }

        if ($user->role === 'owner') {
            return redirect()->route('owner.dashboard')->with('error', 'Akses ditolak. Halaman tersebut khusus untuk pengelola toko (Admin Rental).');
        }

        if ($user->role !== 'admin_rental') {
            return redirect('/')->with('error', 'Akses ditolak. Halaman tersebut hanya dapat diakses oleh pengelola toko.');
        }

        return $next($request);
    }
}
