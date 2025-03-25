<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Carbon\Carbon;
use App\Models\UserSession;
use App\Models\UserActivity;

class ActivityController extends Controller
{
    /**
     * Endpoint untuk heartbeat dari client
     * Dipanggil secara periodik untuk memperbarui aktivitas pengguna
     */
    public function heartbeat(Request $request)
    {
        try {
            $userId = $request->user_id;
            $now = Carbon::now();
            
            // Update sesi pengguna
            $session = UserSession::where('user_id', $userId)
                ->where('status', 'active')
                ->first();
                
            if ($session) {
                $session->update([
                    'last_activity' => $now,
                    'duration' => Carbon::now()->diffInSeconds($session->login_time)
                ]);
                
                // Update atau buat catatan aktivitas harian
                $today = $now->format('Y-m-d');
                $activityRecord = UserActivity::where('user_id', $userId)
                    ->where('action', 'daily_usage')
                    ->whereDate('created_at', $today)
                    ->first();
                    
                if ($activityRecord) {
                    // Perbarui durasi jika sudah ada
                    $activityRecord->update([
                        'duration' => $activityRecord->duration + 60, // Tambah 1 menit
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
                
                return ResponseBuilder::success(200, "Heartbeat berhasil diperbarui", null);
            }
            
            return ResponseBuilder::error(404, "Sesi tidak ditemukan", null);
            
        } catch (\Exception $e) {
            \Log::error('Heartbeat error: ' . $e->getMessage());
            return ResponseBuilder::error(500, "Gagal memperbarui heartbeat: " . $e->getMessage());
        }
    }
} 