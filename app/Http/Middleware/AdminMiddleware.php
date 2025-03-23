<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return ResponseBuilder::error(401, "Silakan login terlebih dahulu");
        }

        // Cek role user (admin atau super_admin diizinkan)
        if ($user->role === 'admin' || $user->role === 'super_admin') {
            return $next($request);
        }

        return ResponseBuilder::error(403, "Akses ditolak. Hanya admin yang diizinkan.");
    }
} 