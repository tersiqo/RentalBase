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
            return redirect()->route('tenant.waiting');
        }

        if ($user->status === 'pending_setup') {
            return redirect()->route('tenant.setup');
        }

        if ($user->status !== 'aktif') {
            Auth::logout();
            return redirect('/')->with('error', 'Akun tidak aktif.');
        }

        if (! in_array($user->role, ['admin_rental', 'owner'], true)) {
            abort(403, 'Akses ditolak.');
        }

        return $next($request);
    }
}
