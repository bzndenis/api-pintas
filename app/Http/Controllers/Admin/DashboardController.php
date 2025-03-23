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
            $sekolahId = Auth::user()->sekolah_id;
            
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