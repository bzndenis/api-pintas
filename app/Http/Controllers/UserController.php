<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserAuth;
use App\Models\Guru;
use App\Models\Sekolah;
use App\Models\UserSession;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware("login");
    }

    public function index(Request $request)
    {
        try {
            $role = $request->query('role');
            $sekolahId = $request->sekolah_id; // Menggunakan sekolah_id dari middleware
            
            $query = UserAuth::with(['sekolah', 'guru']);
            
            // Filter berdasarkan role
            if ($role) {
                $query->where('role', $role);
            }
            
            // Filter berdasarkan sekolah
            if ($sekolahId) {
                $query->where('sekolah_id', $sekolahId);
            }
            
            // Ubah get() menjadi paginate() untuk mendapatkan collection yang bisa dihitung
            $users = $query->orderBy('created_at', 'desc')->get();
            
            // Format response data
            $formattedData = [
                'total' => $users->count(),
                'users' => $users->map(function($user) {
                    return [
                        'id' => $user->id,
                        'email' => $user->email,
                        'nama_lengkap' => $user->nama_lengkap,
                        'role' => $user->role,
                        'no_telepon' => $user->no_telepon,
                        'last_login' => $user->last_login,
                        'is_active' => $user->is_active,
                        'created_at' => $user->created_at,
                        'sekolah' => $user->sekolah ? [
                            'id' => $user->sekolah->id,
                            'nama_sekolah' => $user->sekolah->nama_sekolah,
                            'npsn' => $user->sekolah->npsn,
                            'alamat' => $user->sekolah->alamat
                        ] : null,
                        'guru' => $user->guru ? [
                            'id' => $user->guru->id,
                            'nama' => $user->guru->nama,
                            'nip' => $user->guru->nip,
                            'email' => $user->guru->email,
                            'no_telp' => $user->guru->no_telp
                        ] : null
                    ];
                })
            ];
            
            return ResponseBuilder::success(200, "Berhasil Mendapatkan Data", $formattedData);
        } catch (\Exception $e) {
            \Log::error('Gagal mengambil data user: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return ResponseBuilder::error(500, "Gagal mengambil data: " . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|unique:users|max:255',
            'password' => 'required|min:6',
            'role' => 'required|in:super_admin,admin,guru',
            'sekolah_id' => 'nullable|required_if:role,admin,guru|exists:sekolah,id',
            'nama_lengkap' => 'required|string|max:255',
            'no_telepon' => 'nullable|string|max:20',
            'alamat_sekolah' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();
            
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'sekolah_id' => $request->sekolah_id,
                'nama_lengkap' => $request->nama_lengkap,
                'no_telepon' => $request->no_telepon,
                'alamat_sekolah' => $request->alamat_sekolah,
                'is_active' => $request->input('is_active', true)
            ]);
            
            // Jika role adalah guru, buat data guru
            if ($request->role === 'guru' && $request->sekolah_id) {
                $user->guru()->create([
                    'nama' => $request->nama_lengkap,
                    'email' => $request->email,
                    'nip' => $request->input('nip'),
                    'no_telp' => $request->no_telepon,
                    'sekolah_id' => $request->sekolah_id
                ]);
            }
            
            DB::commit();
            
            return ResponseBuilder::success(201, "Berhasil Menambahkan Data", $user, true);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal Menambahkan Data: " . $e->getMessage());
        }
    }

    public function show($id)
    {
        $user = User::with(['sekolah', 'guru'])->find($id);
        
        if (!$user) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        return ResponseBuilder::success(200, "Berhasil Mendapatkan Data", $user, true);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        
        if (!$user) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        $this->validate($request, [
            'email' => 'sometimes|required|unique:users,email,'.$id.',id|max:255',
            'role' => 'sometimes|required|in:super_admin,admin,guru',
            'sekolah_id' => 'nullable|required_if:role,admin,guru|exists:sekolah,id',
            'nama_lengkap' => 'sometimes|required|string|max:255',
            'no_telepon' => 'nullable|string|max:20',
            'alamat_sekolah' => 'nullable|string'
        ]);
        
        try {
            DB::beginTransaction();
            
            $updateData = $request->only([
                'email', 'role', 'sekolah_id', 'is_active', 'nama_lengkap', 'no_telepon', 'alamat_sekolah'
            ]);
            
            // Update password jika ada
            if ($request->has('password') && $request->password) {
                $updateData['password'] = Hash::make($request->password);
            }
            
            $user->update($updateData);
            
            // Update atau buat data guru jika rolenya guru
            if ($user->role === 'guru' && $user->sekolah_id) {
                $guru = $user->guru;
                
                if ($guru) {
                    $guru->update([
                        'nama' => $request->nama_lengkap,
                        'email' => $user->email,
                        'nip' => $request->input('nip'),
                        'no_telp' => $request->no_telepon,
                        'sekolah_id' => $user->sekolah_id
                    ]);
                } else {
                    $user->guru()->create([
                        'nama' => $request->nama_lengkap,
                        'email' => $user->email,
                        'nip' => $request->input('nip'),
                        'no_telp' => $request->no_telepon,
                        'sekolah_id' => $user->sekolah_id
                    ]);
                }
            }
            
            DB::commit();
            
            return ResponseBuilder::success(200, "Berhasil Mengubah Data", $user, true);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal Mengubah Data: " . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $user = User::find($id);
        
        if (!$user) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        try {
            $user->delete();
            return ResponseBuilder::success(200, "Berhasil Menghapus Data", null, true);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal Menghapus Data: " . $e->getMessage());
        }
    }

    public function pagenationUser(Request $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $offset = ($page - 1) * $limit;
        $role = $request->input('role');
        $sekolahId = $request->input('sekolah_id');
        $search = $request->input('search');
        
        $query = User::with('sekolah');
        
        // Filter berdasarkan role jika ada
        if ($role) {
            $query->where('role', $role);
        }
        
        // Filter berdasarkan sekolah jika ada
        if ($sekolahId) {
            $query->where('sekolah_id', $sekolahId);
        }
        
        // Pencarian berdasarkan nama_lengkap atau email
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $users = $query->orderBy('created_at', 'desc')->paginate($limit);
        $total_user = $users->total();
        $total_pages = $users->lastPage();

        $data = $users->items();

        return response()->json([
            "isSuccess" => true,
            "statusCode" => 200,
            "responseMessage" => "Success",
            "query" => [
                "page" => $page,
                "limit" => $limit,
                "offset" => $offset,
                "count" => $total_user,
                "total_pages" => $total_pages
            ],
            "data" => $data,
            "recordsFiltered" => $total_user,
            "recordsTotal" => $total_user
        ]);
    }

    public function changePassword(Request $request)
    {
        try {
            $this->validate($request, [
                'password_lama' => 'required',
                'password_baru' => 'required|min:6',
                'konfirmasi_password' => 'required|same:password_baru'
            ]);

            $userId = $request->user_id; // Dari middleware
            $user = UserAuth::find($userId);

            if (!$user) {
                return ResponseBuilder::error(404, "User tidak ditemukan");
            }

            if (!Hash::check($request->password_lama, $user->password)) {
                return ResponseBuilder::error(401, "Password lama tidak sesuai");
            }

            DB::beginTransaction();
            
            // Update password
            $user->update([
                'password' => Hash::make($request->password_baru)
            ]);

            // Catat aktivitas
            DB::table('user_activities')->insert([
                'id' => Uuid::uuid4()->toString(),
                'user_id' => $user->id,
                'action' => 'change_password',
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'sekolah_id' => $user->sekolah_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            DB::commit();
            return ResponseBuilder::success(200, "Password berhasil diubah");

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Gagal mengubah password: ' . $e->getMessage());
            return ResponseBuilder::error(500, "Gagal mengubah password: " . $e->getMessage());
        }
    }
    
    public function getUserProfile(Request $request)
    {
        try {
            $token = $request->header('Authorization');
            
            if (!$token) {
                return ResponseBuilder::error(401, "Token tidak tersedia", null);
            }
            
            // Hapus 'Bearer ' dari token jika ada
            if (strpos($token, 'Bearer ') === 0) {
                $token = substr($token, 7);
            }
            
            $user = UserAuth::with(['sekolah', 'guru'])
                ->where('remember_token', $token)
                ->where('is_active', true)
                ->first();
            
            if (!$user) {
                return ResponseBuilder::error(404, "User tidak ditemukan atau token tidak valid", null);
            }

            // Format response data
            $userData = [
                'id' => $user->id,
                'email' => $user->email,
                'nama_lengkap' => $user->nama_lengkap,
                'role' => $user->role,
                'no_telepon' => $user->no_telepon,
                'last_login' => $user->last_login,
                'is_active' => $user->is_active,
                'sekolah' => $user->sekolah ? [
                    'id' => $user->sekolah->id,
                    'nama_sekolah' => $user->sekolah->nama_sekolah,
                    'npsn' => $user->sekolah->npsn,
                    'alamat' => $user->sekolah->alamat
                ] : null,
                'guru' => $user->guru ? [
                    'id' => $user->guru->id,
                    'nama' => $user->guru->nama,
                    'nip' => $user->guru->nip,
                    'email' => $user->guru->email,
                    'no_telp' => $user->guru->no_telp
                ] : null
            ];

            // Update last activity di user session
            UserSession::where('user_id', $user->id)
                ->where('status', 'active')
                ->update([
                    'last_activity' => Carbon::now(),
                    'duration' => DB::raw('TIMESTAMPDIFF(SECOND, login_time, NOW())')
                ]);
            
            // Catat aktivitas
            DB::table('user_activities')->insert([
                'id' => Uuid::uuid4()->toString(),
                'user_id' => $user->id,
                'action' => 'view_profile',
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'sekolah_id' => $user->sekolah_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            
            return ResponseBuilder::success(200, "Berhasil Mendapatkan Data", $userData);
            
        } catch (\Exception $e) {
            \Log::error('Gagal mengambil profile user: ' . $e->getMessage());
            return ResponseBuilder::error(500, "Gagal mengambil profile: " . $e->getMessage());
        }
    }
}