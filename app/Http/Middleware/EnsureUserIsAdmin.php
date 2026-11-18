<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     * Only allow authenticated users with role 'admin' to proceed.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu untuk mengakses halaman administrator.');
        }

        if ($request->user()->role !== 'admin') {
            abort(403, 'Akses Ditolak. Halaman /admin hanya dapat diakses oleh akun dengan role administrator.');
        }

        return $next($request);
    }
}
