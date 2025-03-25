<?php

namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\UserSession;
use App\Models\UserActivity;
use Ramsey\Uuid\Uuid;

class ActivityTrackerMiddleware
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
        // Jalankan request terlebih dahulu
        $response = $next($request);
        
        // Ambil token dari header
        $token = $request->header('Authorization');
        
        // Jika tidak ada token, tidak perlu melanjutkan
        if (!$token) {
            return $response;
        }
        
        // Hapus 'Bearer ' dari token jika ada
        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }
        
        // Cari user berdasarkan token
        $user = DB::table('users')
            ->where('remember_token', $token)
            ->where('is_active', true)
            ->first();
        
        // Jika user ditemukan, update aktivitas
        if ($user) {
            $userId = $user->id;
            $now = Carbon::now();
            
            // Update sesi pengguna
            $session = DB::table('user_sessions')
                ->where('user_id', $userId)
                ->where('status', 'active')
                ->first();
                
            if ($session) {
                // Update last_activity dan duration
                DB::table('user_sessions')
                    ->where('id', $session->id)
                    ->update([
                        'last_activity' => $now,
                        'duration' => DB::raw('TIMESTAMPDIFF(SECOND, login_time, NOW())'),
                        'updated_at' => $now
                    ]);
                
                // Buat atau perbarui catatan aktivitas untuk hari ini
                $today = $now->format('Y-m-d');
                $activityRecord = DB::table('user_activities')
                    ->where('user_id', $userId)
                    ->where('action', 'daily_usage')
                    ->whereDate('created_at', $today)
                    ->first();
                    
                if ($activityRecord) {
                    // Perbarui durasi jika sudah ada
                    DB::table('user_activities')
                        ->where('id', $activityRecord->id)
                        ->update([
                            'duration' => $activityRecord->duration + 60, // Tambah 1 menit (60 detik)
                            'updated_at' => $now
                        ]);
                } else {
                    // Buat catatan baru jika belum ada
                    DB::table('user_activities')->insert([
                        'id' => Uuid::uuid4()->toString(),
                        'user_id' => $userId,
                        'action' => 'daily_usage',
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->header('User-Agent'),
                        'duration' => 60, // Mulai dengan 1 menit
                        'sekolah_id' => $user->sekolah_id,
                        'created_at' => $now,
                        'updated_at' => $now
                    ]);
                }
            }
        }
        
        return $response;
    }
} 