<?php

namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Helper\ResponseBuilder;

class AutoLogoutMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  int  $minutes  Waktu idle maksimum dalam menit
     * @return mixed
     */
    public function handle($request, Closure $next, $minutes = 15)
    {
        // Ambil token dari header
        $token = $request->header('Authorization');
        
        // Jika tidak ada token, lanjutkan request
        if (!$token) {
            return $next($request);
        }
        
        // Hapus 'Bearer ' dari token jika ada
        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }
        
        // Cari sesi aktif pengguna
        $session = DB::table('user_sessions')
            ->join('users', 'users.id', '=', 'user_sessions.user_id')
            ->where('users.remember_token', $token)
            ->where('user_sessions.status', 'active')
            ->select('user_sessions.*')
            ->first();
        
        if ($session) {
            // Hitung selisih waktu terakhir aktivitas dengan waktu sekarang
            $lastActivity = Carbon::parse($session->last_activity);
            $now = Carbon::now();
            $idleTime = $now->diffInMinutes($lastActivity);
            
            // Jika idle time melebihi batas, lakukan logout otomatis
            if ($idleTime >= $minutes) {
                // Update status sesi menjadi expired
                DB::table('user_sessions')
                    ->where('id', $session->id)
                    ->update([
                        'status' => 'expired',
                        'last_activity' => $now,
                        'duration' => DB::raw('TIMESTAMPDIFF(SECOND, login_time, NOW())'),
                        'updated_at' => $now
                    ]);
                
                // Hapus token dari user
                DB::table('users')
                    ->where('remember_token', $token)
                    ->update([
                        'remember_token' => null,
                        'updated_at' => $now
                    ]);
                
                // Catat aktivitas logout otomatis
                DB::table('user_activities')->insert([
                    'id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
                    'user_id' => $session->user_id,
                    'action' => 'auto_logout',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                    'duration' => DB::raw('TIMESTAMPDIFF(SECOND, "' . $session->login_time . '", NOW())'),
                    'sekolah_id' => $session->sekolah_id,
                    'created_at' => $now,
                    'updated_at' => $now
                ]);
                
                return ResponseBuilder::error(401, "Sesi Anda telah berakhir karena tidak aktif selama {$minutes} menit. Silakan login kembali.");
            }
        }
        
        return $next($request);
    }
} 