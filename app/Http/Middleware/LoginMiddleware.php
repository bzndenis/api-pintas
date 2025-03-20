<?php

namespace App\Http\Middleware;

use App\Models\UserAuth;
use App\Models\UserSession;
use App\Models\UserActivity;
use Closure;
use Illuminate\Http\Response;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Helper\ResponseBuilder;

// Import fungsi global
use function public_path;

class LoginMiddleware
{
    public function handle($request, Closure $next)
    {
        $token = str_replace('Bearer ', '', $request->header('Authorization'));
        
        if (!$token) {
            return ResponseBuilder::error(401, "Token tidak ditemukan");
        }

        $user = User::where('remember_token', $token)->first();
        
        if (!$user) {
            return ResponseBuilder::error(401, "Token tidak valid");
        }

        // Set user ke Auth facade
        Auth::setUser($user);
        
        // Catat aktivitas pengguna
        $this->logUserActivity($user, $request);
        // Update session pengguna
        $this->updateUserSession($user, $request);

        // Tambahkan user id ke request agar bisa diakses di controller
        $request->merge(['user_id' => $user->id]);
        $request->merge(['sekolah_id' => $user->sekolah_id]);

        return $next($request);
    }

    /**
     * Mencatat aktivitas pengguna
     */
    private function logUserActivity($user, $request)
    {
        // Mendapatkan route dan method untuk mencatat sebagai action
        $method = $request->method();
        $path = $request->path();
        $action = "{$method}:{$path}";

        try {
            // Simpan di database
            UserActivity::create([
                'user_id' => $user->id,
                'action' => $action,
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'sekolah_id' => $user->sekolah_id
            ]);

            // Simpan log ke file JSON
            $this->saveActivityToJsonFile($user, $action, $request);
        } catch (\Exception $e) {
            \Log::error('Gagal mencatat aktivitas pengguna: ' . $e->getMessage());
        }
    }

    /**
     * Update sesi pengguna
     */
    private function updateUserSession($user, $request)
    {
        try {
            // Cari sesi aktif pengguna
            $session = UserSession::where('user_id', $user->id)
                ->where('status', 'active')
                ->first();

            if ($session) {
                // Update last_activity jika sesi ditemukan
                $session->update([
                    'last_activity' => Carbon::now(),
                    'duration' => Carbon::now()->diffInSeconds($session->login_time)
                ]);
            } else {
                // Buat sesi baru jika tidak ada sesi aktif
                UserSession::create([
                    'user_id' => $user->id,
                    'login_time' => Carbon::now(),
                    'last_activity' => Carbon::now(),
                    'status' => 'active',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                    'sekolah_id' => $user->sekolah_id
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Gagal memperbarui sesi pengguna: ' . $e->getMessage());
        }
    }

    /**
     * Menyimpan log aktivitas ke file JSON
     */
    private function saveActivityToJsonFile($user, $action, $request)
    {
        try {
            $today = Carbon::now()->format('Y-m-d');
            $filename = base_path('public') . "/logs/activities/{$today}.json";
            
            // Buat struktur data aktivitas
            $activityData = [
                'id' => Uuid::uuid4()->toString(),
                'user_id' => $user->id,
                'sekolah_id' => $user->sekolah_id,
                'action' => $action,
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'timestamp' => Carbon::now()->toIso8601String()
            ];
            
            // Periksa apakah file sudah ada
            if (file_exists($filename)) {
                // Baca file yang sudah ada
                $currentData = json_decode(file_get_contents($filename), true);
                if (!is_array($currentData)) {
                    $currentData = [];
                }
                
                // Tambahkan data baru
                $currentData[] = $activityData;
                
                // Simpan kembali file
                file_put_contents($filename, json_encode($currentData, JSON_PRETTY_PRINT));
            } else {
                // Buat direktori jika belum ada
                $directory = dirname($filename);
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }
                
                // Simpan data ke file baru
                file_put_contents($filename, json_encode([$activityData], JSON_PRETTY_PRINT));
            }
        } catch (\Exception $e) {
            \Log::error('Gagal menyimpan log ke file: ' . $e->getMessage());
        }
    }
}
