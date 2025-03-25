<?php

namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\UserSession;
use App\Models\UserActivity;

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
        $response = $next($request);
        
        // Hanya jalankan jika user sudah login
        if ($request->user_id) {
            $userId = $request->user_id;
            $now = Carbon::now();
            
            // Update sesi pengguna
            UserSession::where('user_id', $userId)
                ->where('status', 'active')
                ->update([
                    'last_activity' => $now,
                    'duration' => DB::raw('TIMESTAMPDIFF(SECOND, login_time, NOW())')
                ]);
            
            // Buat atau perbarui catatan aktivitas untuk hari ini
            $today = $now->format('Y-m-d');
            $activityRecord = UserActivity::where('user_id', $userId)
                ->where('action', 'daily_usage')
                ->whereDate('created_at', $today)
                ->first();
                
            if ($activityRecord) {
                // Perbarui durasi jika sudah ada
                $activityRecord->update([
                    'duration' => DB::raw('duration + 60'), // Tambah 1 menit (60 detik)
                    'updated_at' => $now
                ]);
            } else {
                // Buat catatan baru jika belum ada
                UserActivity::create([
                    'user_id' => $userId,
                    'action' => 'daily_usage',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                    'duration' => 60, // Mulai dengan 1 menit
                    'sekolah_id' => $request->sekolah_id,
                    'created_at' => $now,
                    'updated_at' => $now
                ]);
            }
        }
        
        return $response;
    }
} 