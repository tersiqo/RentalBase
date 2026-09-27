<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Membatasi route berdasarkan role user.
     *
     * Contoh:
     * ->middleware(RoleMiddleware::class . ':owner')
     * ->middleware(RoleMiddleware::class . ':admin_rental')
     * ->middleware(RoleMiddleware::class . ':customer')
     */
    public function handle(
        Request $request,
        Closure $next,
        string ...$roles
    ): Response {
        if (! Auth::check()) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $user = Auth::user();

        if ($user->status !== 'aktif') {
            Auth::logout();

            return response()->json([
                'message' => 'Akun tidak aktif.',
            ], 403);
        }

        if (! in_array($user->role, $roles, true)) {
            return response()->json([
                'message' => 'Forbidden.',
            ], 403);
        }

        return $next($request);
    }
}