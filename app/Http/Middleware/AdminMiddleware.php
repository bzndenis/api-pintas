<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Pastikan user sudah login dan dapatkan data user
        $user = Auth::user();
        
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