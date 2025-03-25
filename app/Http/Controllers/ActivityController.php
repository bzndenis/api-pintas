<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class ActivityController extends Controller
{
    /**
     * Endpoint untuk heartbeat dari client
     * Dipanggil secara periodik untuk memperbarui aktivitas pengguna
     */
    public function heartbeat(Request $request)
    {
        try {
            // Ambil token dari header
            $token = $request->header('Authorization');
            
            // Hapus 'Bearer ' dari token jika ada
            if (strpos($token, 'Bearer ') === 0) {
                $token = substr($token, 7);
            }
            
            // Cari user berdasarkan token
            $user = DB::table('users')
                ->where('remember_token', $token)
                ->where('is_active', true)
                ->first();
                
            if (!$user) {
                return ResponseBuilder::error(401, "Token tidak valid", null);
            }
            
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
                
                // Update atau buat catatan aktivitas harian
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
                            'duration' => $activityRecord->duration + 60, // Tambah 1 menit
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
                
                return ResponseBuilder::success(200, "Heartbeat berhasil diperbarui", null);
            }
            
            return ResponseBuilder::error(404, "Sesi tidak ditemukan", null);
            
        } catch (\Exception $e) {
            \Log::error('Heartbeat error: ' . $e->getMessage());
            return ResponseBuilder::error(500, "Gagal memperbarui heartbeat: " . $e->getMessage());
        }
    }
} 