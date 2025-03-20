<?php

namespace App\Http\Controllers;

use App\Models\UserActivity;
use App\Models\UserSession;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Storage;

// Import fungsi global
use function public_path;

class UserActivityController extends Controller
{
    /**
     * Mendapatkan semua aktivitas pengguna
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getAllActivities(Request $request)
    {
        try {
            $userId = $request->user_id;
            $sekolahId = $request->sekolah_id;
            
            // Mendapatkan parameter paginasi
            $limit = $request->input('limit', 10);
            $page = $request->input('page', 1);
            $offset = ($page - 1) * $limit;
            
            $query = UserActivity::with(['user', 'sekolah']);
            
            if ($request->input('user_id_filter')) {
                $query->where('user_id', $request->input('user_id_filter'));
            }
            
            if ($request->input('start_date') && $request->input('end_date')) {
                $query->whereBetween('created_at', [
                    $request->input('start_date') . ' 00:00:00', 
                    $request->input('end_date') . ' 23:59:59'
                ]);
            }
            
            $query->where('sekolah_id', $sekolahId);
            
            $total = $query->count();
            
            $activities = $query->orderBy('created_at', 'desc')
                ->limit($limit)
                ->offset($offset)
                ->get();

            $formattedActivities = $activities->map(function($activity) {
                return [
                    'id' => $activity->id,
                    'user' => [
                        'id' => $activity->user->id,
                        'nama_lengkap' => $activity->user->nama_lengkap,
                        'email' => $activity->user->email,
                        'role' => $activity->user->role
                    ],
                    'action' => $activity->action,
                    'ip_address' => $activity->ip_address,
                    'user_agent' => $activity->user_agent,
                    'created_at' => $activity->created_at->format('Y-m-d H:i:s'),
                    'sekolah' => [
                        'id' => $activity->sekolah->id,
                        'nama_sekolah' => $activity->sekolah->nama_sekolah
                    ]
                ];
            });

            return ResponseBuilder::success(200, "Berhasil mendapatkan data aktivitas", [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'data' => $formattedActivities
            ]);

        } catch (\Exception $e) {
            \Log::error('Gagal mengambil data aktivitas: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return ResponseBuilder::error(500, "Gagal mengambil data: " . $e->getMessage());
        }
    }
    
    /**
     * Mendapatkan semua sesi pengguna
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getAllSessions(Request $request)
    {
        try {
            $sekolahId = $request->sekolah_id;
            
            $limit = $request->input('limit', 10);
            $page = $request->input('page', 1);
            $offset = ($page - 1) * $limit;
            
            $query = UserSession::with(['user', 'sekolah']);
            
            if ($request->input('user_id_filter')) {
                $query->where('user_id', $request->input('user_id_filter'));
            }
            
            if ($request->input('start_date') && $request->input('end_date')) {
                $query->whereBetween('login_time', [
                    $request->input('start_date') . ' 00:00:00', 
                    $request->input('end_date') . ' 23:59:59'
                ]);
            }
            
            $query->where('sekolah_id', $sekolahId);
            
            $total = $query->count();
            
            $sessions = $query->orderBy('login_time', 'desc')
                ->limit($limit)
                ->offset($offset)
                ->get();

            $formattedSessions = $sessions->map(function($session) {
                $duration = $session->duration ?? 0;
                $hours = floor($duration / 3600);
                $minutes = floor(($duration % 3600) / 60);
                $seconds = $duration % 60;
                
                return [
                    'id' => $session->id,
                    'user' => [
                        'id' => $session->user->id,
                        'nama_lengkap' => $session->user->nama_lengkap,
                        'email' => $session->user->email,
                        'role' => $session->user->role
                    ],
                    'login_time' => $session->login_time->format('Y-m-d H:i:s'),
                    'last_activity' => $session->last_activity->format('Y-m-d H:i:s'),
                    'duration_formatted' => sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds),
                    'duration_seconds' => $duration,
                    'status' => $session->status,
                    'ip_address' => $session->ip_address,
                    'user_agent' => $session->user_agent,
                    'sekolah' => [
                        'id' => $session->sekolah->id,
                        'nama_sekolah' => $session->sekolah->nama_sekolah
                    ]
                ];
            });

            return ResponseBuilder::success(200, "Berhasil mendapatkan data sesi", [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'data' => $formattedSessions
            ]);

        } catch (\Exception $e) {
            \Log::error('Gagal mengambil data sesi: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return ResponseBuilder::error(500, "Gagal mengambil data: " . $e->getMessage());
        }
    }
    
    /**
     * Mendapatkan log aktivitas dari file JSON
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getActivityLogs(Request $request)
    {
        try {
            $userId = $request->input('user_id_filter', $request->user_id);
            $date = $request->input('date', Carbon::now()->format('Y-m-d'));
            
            $filename = base_path('public') . "/logs/activities/{$date}.json";
            
            if (!file_exists($filename)) {
                return ResponseBuilder::success(200, "Data Tidak Ditemukan", [
                    'logs' => [],
                    'date' => $date
                ]);
            }
            
            $allLogs = json_decode(file_get_contents($filename), true);
            
            // Filter log berdasarkan user_id
            $userLogs = array_filter($allLogs, function($log) use ($userId) {
                return $log['user_id'] === $userId;
            });
            
            return ResponseBuilder::success(200, "Berhasil Mendapatkan Data", [
                'logs' => array_values($userLogs),
                'date' => $date
            ]);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, $e->getMessage(), null);
        }
    }
    
    /**
     * Mendapatkan daftar tanggal yang memiliki log aktivitas
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getActivityLogDates(Request $request)
    {
        try {
            $userId = $request->input('user_id_filter', $request->user_id);
            $activitiesPath = base_path('public') . '/logs/activities';
            
            if (!is_dir($activitiesPath)) {
                return ResponseBuilder::success(200, "Data Tidak Ditemukan", [
                    'dates' => []
                ]);
            }
            
            $availableDates = [];
            $files = glob($activitiesPath . '/*.json');
            
            foreach ($files as $file) {
                $date = pathinfo($file, PATHINFO_FILENAME);
                $logs = json_decode(file_get_contents($file), true);
                
                // Cek apakah ada log untuk user ini
                foreach ($logs as $log) {
                    if ($log['user_id'] === $userId) {
                        $availableDates[] = $date;
                        break;
                    }
                }
            }
            
            // Urutkan tanggal dari yang terbaru
            rsort($availableDates);
            
            return ResponseBuilder::success(200, "Berhasil Mendapatkan Data", [
                'dates' => array_unique($availableDates)
            ]);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, $e->getMessage(), null);
        }
    }
    
    /**
     * Mendapatkan statistik aktivitas pengguna
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getActivityStatistics(Request $request)
    {
        try {
            $userId = $request->user_id;
            $sekolahId = $request->sekolah_id;
            
            // Parameter filter
            $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
            $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
            
            // Statistik aktivitas per hari
            $dailyActivities = DB::table('user_activities')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
                ->where('sekolah_id', $sekolahId)
                ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy(DB::raw('DATE(created_at)'))
                ->get();
            
            // Statistik durasi sesi per hari
            $dailySessions = DB::table('user_sessions')
                ->select(
                    DB::raw('DATE(login_time) as date'), 
                    DB::raw('SUM(duration) as total_duration'), 
                    DB::raw('COUNT(*) as session_count'),
                    DB::raw('AVG(duration) as avg_duration')
                )
                ->where('sekolah_id', $sekolahId)
                ->whereBetween(DB::raw('DATE(login_time)'), [$startDate, $endDate])
                ->groupBy(DB::raw('DATE(login_time)'))
                ->orderBy(DB::raw('DATE(login_time)'))
                ->get();
            
            // Format data untuk tampilan chart
            $dates = [];
            $activityCounts = [];
            $sessionDurations = [];
            $sessionCounts = [];
            
            // Buat array tanggal lengkap antara start_date dan end_date
            $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
            foreach ($period as $date) {
                $dateStr = $date->format('Y-m-d');
                $dates[] = $dateStr;
                
                // Default values
                $activityCounts[$dateStr] = 0;
                $sessionDurations[$dateStr] = 0;
                $sessionCounts[$dateStr] = 0;
            }
            
            // Isi data aktivitas
            foreach ($dailyActivities as $activity) {
                $activityCounts[$activity->date] = $activity->count;
            }
            
            // Isi data sesi
            foreach ($dailySessions as $session) {
                $sessionDurations[$session->date] = $session->total_duration;
                $sessionCounts[$session->date] = $session->session_count;
            }
            
            // Siapkan data untuk respons
            $chartData = [
                'labels' => $dates,
                'datasets' => [
                    [
                        'label' => 'Jumlah Aktivitas',
                        'data' => array_values($activityCounts)
                    ],
                    [
                        'label' => 'Durasi Sesi (detik)',
                        'data' => array_values($sessionDurations)
                    ],
                    [
                        'label' => 'Jumlah Sesi',
                        'data' => array_values($sessionCounts)
                    ]
                ]
            ];
            
            return ResponseBuilder::success(200, "Berhasil Mendapatkan Data", [
                'chart_data' => $chartData,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, $e->getMessage(), null);
        }
    }

    public function getUsageTime(Request $request)
    {
        try {
            $userId = $request->user_id;
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            
            // Query untuk mendapatkan total durasi dari sesi aktif
            $query = UserSession::where('user_id', $userId)
                ->where('status', 'expired'); // Hanya ambil sesi yang sudah selesai
                
            if ($startDate && $endDate) {
                $query->whereBetween('login_time', [
                    $startDate . ' 00:00:00',
                    $endDate . ' 23:59:59'
                ]);
            }
            
            // Hitung total durasi dalam detik
            $totalDuration = $query->sum('duration');
            
            // Hitung sesi yang masih aktif
            $activeSession = UserSession::where('user_id', $userId)
                ->where('status', 'active')
                ->first();
                
            $currentDuration = 0;
            if ($activeSession) {
                $currentDuration = Carbon::now()->diffInSeconds($activeSession->login_time);
            }
            
            // Format durasi ke dalam jam:menit:detik
            $totalSeconds = $totalDuration + $currentDuration;
            $hours = floor($totalSeconds / 3600);
            $minutes = floor(($totalSeconds % 3600) / 60);
            $seconds = $totalSeconds % 60;
            
            $formattedDuration = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan data waktu penggunaan", [
                'total_duration_seconds' => $totalSeconds,
                'formatted_duration' => $formattedDuration,
                'active_session' => $activeSession ? [
                    'login_time' => $activeSession->login_time,
                    'duration' => $currentDuration,
                    'formatted_current_duration' => sprintf(
                        '%02d:%02d:%02d',
                        floor($currentDuration / 3600),
                        floor(($currentDuration % 3600) / 60),
                        $currentDuration % 60
                    )
                ] : null
            ]);
            
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data waktu penggunaan: " . $e->getMessage());
        }
    }
} 