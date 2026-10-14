<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class OwnerMiddleware
{
    /**
     * Pastikan user sudah login dan memiliki role owner.
     * Owner hanya bisa mengakses panel pusat /owner/*
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = Auth::user();

        if ($user->status !== 'aktif') {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Akun tidak aktif.');
        }

        if ($user->role !== 'owner') {
            if ($user->role === 'admin_rental') {
                return redirect()->route('admin.dashboard')->with('error', 'Akses ditolak. Halaman ini hanya dapat diakses oleh Owner Platform.');
            }

            return redirect()->route('login')->with('error', 'Akses ditolak. Halaman ini hanya dapat diakses oleh Owner.');
        }

        return $next($request);
    }
}
