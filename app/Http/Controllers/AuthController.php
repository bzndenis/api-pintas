<?php

namespace App\Http\Controllers;

use App\Models\UserAuth;
use App\Models\Sekolah;
use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Http\Helper\ResponseBuilder;
use Ramsey\Uuid\Uuid;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $this->validate($request, [
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'required|unique:users|max:255',
            'no_telepon' => 'required|string|max:15',
            'sekolah' => 'required|string|max:255',
            'alamat_sekolah' => 'required|string',
            'password' => 'required|min:6',
            'konfirmasi_password' => 'required|same:password'
        ]);

        try {
            // Matikan foreign key check sementara untuk menghindari constraint issues
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            
            \Log::info('Memulai pendaftaran sekolah dan user');
            
            // Buat ID untuk sekolah dengan UUID
            $sekolahId = Uuid::uuid4()->toString();
            
            // Buat Sekolah dengan query builder langsung
            DB::table('sekolah')->insert([
                'id' => $sekolahId,
                'nama_sekolah' => $request->input('sekolah'),
                'npsn' => 'TMP' . rand(1000000, 9999999),
                'alamat' => $request->input('alamat_sekolah'),
                'kota' => 'Jakarta', 
                'provinsi' => 'DKI Jakarta',
                'kode_pos' => '12345',
                'no_telp' => $request->input('no_telepon'),
                'email' => $request->input('email'),
                'kepala_sekolah' => $request->input('nama_lengkap'),
                'is_active' => 1,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ]);
            
            \Log::info('Sekolah berhasil dibuat dengan ID: ' . $sekolahId);
            
            // Buat ID untuk user dengan UUID
            $userId = Uuid::uuid4()->toString();
            
            // Buat user dengan query builder langsung
            DB::table('users')->insert([
                'id' => $userId,
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'role' => 'admin',
                'sekolah_id' => $sekolahId,
                'nama_lengkap' => $request->input('nama_lengkap'),
                'no_telepon' => $request->input('no_telepon'),
                'alamat_sekolah' => $request->input('alamat_sekolah'),
                'is_active' => 1,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ]);
            
            // Aktifkan kembali foreign key check
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            
            \Log::info('User berhasil dibuat dengan ID: ' . $userId);
            
            // Ambil data lengkap untuk response
            $sekolah = Sekolah::find($sekolahId);
            $user = UserAuth::find($userId);
            
            return ResponseBuilder::success(201, "Pendaftaran berhasil. Sekolah dan akun admin telah terdaftar.", [
                'user' => $user,
                'sekolah' => $sekolah
            ]);
            
        } catch (\Exception $e) {
            // Pastikan foreign key check diaktifkan kembali jika terjadi error
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            
            \Log::error('Register error: ' . $e->getMessage());
            \Log::error('Trace: ' . $e->getTraceAsString());
            return ResponseBuilder::error(500, "Pendaftaran gagal: " . $e->getMessage());
        }
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required|min:6'
        ]);

        $email = $request->input("email");
        $password = $request->input("password");

        // Ambil user dengan relasi sekolah dan guru yang benar
        $user = UserAuth::with([
            'sekolah',
            'guru' // Gunakan relasi normal tanpa kondisi tambahan
        ])
        ->where('email', $email)
        ->first();

        if (!$user) {
            return ResponseBuilder::error(401, "Login Gagal", ["token" => null]);
        }

        if (!$user->is_active) {
            return ResponseBuilder::error(401, "Akun tidak aktif", ["token" => null]);
        }

        if (Hash::check($password, $user->password)) {
            $newToken = $this->generateRandomString();

            $user->update([
                'last_login' => \Carbon\Carbon::now(),
                'remember_token' => $newToken
            ]);
            
            // Catat sesi login di tabel user_sessions
            try {
                \DB::table('user_sessions')->insert([
                    'id' => Uuid::uuid4()->toString(),
                    'user_id' => $user->id,
                    'login_time' => \Carbon\Carbon::now(),
                    'last_activity' => \Carbon\Carbon::now(),
                    'status' => 'active',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                    'sekolah_id' => $user->sekolah_id,
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now()
                ]);
                
                // Catat aktivitas login di tabel user_activities
                \DB::table('user_activities')->insert([
                    'id' => Uuid::uuid4()->toString(),
                    'user_id' => $user->id,
                    'action' => 'login',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                    'sekolah_id' => $user->sekolah_id,
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now()
                ]);
            } catch (\Exception $e) {
                \Log::error('Gagal mencatat sesi login: ' . $e->getMessage());
            }

            $userData = [
                'id' => $user->id,
                'email' => $user->email,
                'nama_lengkap' => $user->nama_lengkap,
                'role' => $user->role,
                'sekolah_id' => $user->sekolah_id,
                'no_telepon' => $user->no_telepon,
                'alamat_sekolah' => $user->alamat_sekolah,
                'last_login' => $user->last_login,
                'sekolah' => $user->sekolah ? [
                    'id' => $user->sekolah->id,
                    'nama_sekolah' => $user->sekolah->nama_sekolah,
                    'npsn' => $user->sekolah->npsn,
                    'alamat' => $user->sekolah->alamat
                ] : null
            ];

            if ($user->role === 'guru' && $user->guru) {
                $userData['guru'] = [
                    'id' => $user->guru->id,
                    'nama' => $user->guru->nama,
                    'nip' => $user->guru->nip,
                    'email' => $user->guru->email,
                    'no_telp' => $user->guru->no_telp
                ];
            }

            return ResponseBuilder::success(200, "Login Berhasil", [
                "token" => $newToken,
                "user" => $userData
            ]);

        } else {
            return ResponseBuilder::error(401, "Login Gagal", ["token" => null]);
        }
    }
    
    public function logout(Request $request)
    {
        $token = $request->header('Authorization');
        
        if (!$token) {
            return ResponseBuilder::error(401, "Token tidak tersedia", null);
        }
        
        $user = UserAuth::where('remember_token', $token)->first();
        
        if (!$user) {
            return ResponseBuilder::error(404, "User tidak ditemukan atau token tidak valid", null);
        }
        
        $user->update([
            'remember_token' => null
        ]);
        
        // Catat aktivitas logout di tabel user_sessions
        try {
            // Update status sesi menjadi expired
            \DB::table('user_sessions')
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->update([
                    'status' => 'expired',
                    'last_activity' => \Carbon\Carbon::now(),
                    'duration' => \DB::raw('TIMESTAMPDIFF(SECOND, login_time, NOW())'),
                    'updated_at' => \Carbon\Carbon::now()
                ]);
                
            // Catat aktivitas logout di tabel user_activities
            \DB::table('user_activities')->insert([
                'id' => Uuid::uuid4()->toString(),
                'user_id' => $user->id,
                'action' => 'logout',
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'sekolah_id' => $user->sekolah_id,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ]);
        } catch (\Exception $e) {
            \Log::error('Gagal mencatat aktivitas logout: ' . $e->getMessage());
        }
        
        return ResponseBuilder::success(200, "Logout berhasil", null);
    }
    
    private function generateRandomString($length = 80)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}