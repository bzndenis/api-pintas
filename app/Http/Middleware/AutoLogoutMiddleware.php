<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Http\Helper\ResponseBuilder;
use App\Models\UserSession;
use Carbon\Carbon;

class AutoLogoutMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Cek apakah user sudah login
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        
        // Cek sesi aktif user
        $session = UserSession::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$session) {
            return $next($request);
        }

        // Cek apakah sesi sudah expired (misal: 30 menit)
        $lastActivity = Carbon::parse($session->last_activity);
        $now = Carbon::now();
        
        if ($now->diffInMinutes($lastActivity) > 30) {
            // Update status sesi menjadi expired
            $session->update([
                'status' => 'expired',
                'logged_out_at' => $now
            ]);
            
            // Hapus token
            $user->update(['remember_token' => null]);
            
            return ResponseBuilder::error(401, "Sesi Anda telah berakhir. Silakan login kembali.");
        }

        // Update last activity
        $session->update(['last_activity' => $now]);
        
        return $next($request);
    }
} 