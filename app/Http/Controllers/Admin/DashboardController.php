<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\UserActivity;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $user = Auth::user();
            $sekolahId = $user->sekolah_id;
            $username = $user->username;
            // Mendapatkan informasi user
            // Mendapatkan informasi lengkap user
            $fullname = $user->fullname;
            $role = $user->role;
            $lastLogin = $user->last_login ? Carbon::parse($user->last_login)->format('d-m-Y H:i:s') : 'Belum pernah login';
            
            // Mendapatkan statistik aktivitas user
            $userActivities = UserActivity::where('user_id', $user->id)
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->count();
                
            $userSessions = \DB::table('user_sessions')
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->count();
            
            // Mendapatkan informasi sekolah
            $sekolah = $user->sekolah;
            $namaSekolah = $user->sekolah->nama_sekolah ?? 'Tidak ada sekolah';
            
            // Statistik dasar
            $totalGuru = Guru::where('sekolah_id', $sekolahId)->count();
            $totalSiswa = Siswa::where('sekolah_id', $sekolahId)->count();
            $totalKelas = Kelas::where('sekolah_id', $sekolahId)->count();
            
            // Aktivitas terbaru
            $recentActivities = UserActivity::with('user')
                ->where('sekolah_id', $sekolahId)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan data dashboard", [
                'user_info' => [
                    'username' => $username,
                    'fullname' => $fullname,
                    'nama_sekolah' => $namaSekolah
                ],
                'statistics' => [
                    'total_guru' => $totalGuru,
                    'total_siswa' => $totalSiswa,
                    'total_kelas' => $totalKelas
                ],
                'recent_activities' => $recentActivities
            ]);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data dashboard: " . $e->getMessage());
        }
    }
} 