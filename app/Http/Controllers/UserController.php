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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware("login");
    }

    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $role = $request->query('role');
            
            $query = UserAuth::with(['sekolah', 'guru']);
            
            // Super admin dapat melihat semua user
            if ($user->role === 'super_admin') {
                // Filter berdasarkan role
                if ($role) {
                    $query->where('role', $role);
                }
                
                // Filter berdasarkan sekolah
                if ($request->has('sekolah_id')) {
                    $query->where('sekolah_id', $request->sekolah_id);
                }
            } else {
                // Admin dan guru hanya dapat melihat user di sekolah mereka
                $query->where('sekolah_id', $user->sekolah_id);
                
                // Filter berdasarkan role
                if ($role) {
                    $query->where('role', $role);
                }
            }
            
            $users = $query->orderBy('created_at', 'desc')->get();
            
            // Format response data
            $formattedData = [
                'total' => $users->count(),
                'users' => $users->map(function($user) {
                    return [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'fullname' => $user->fullname,
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
            return ResponseBuilder::error(500, "Gagal Mendapatkan Data: " . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Validasi input
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|unique:users,username',
            'password' => 'required|string|min:6',
            'fullname' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:super_admin,admin,guru',
            'sekolah_id' => 'nullable|required_if:role,admin,guru|exists:sekolah,id',
            'no_telepon' => 'nullable|string|max:20',
        ]);
        
        if ($validator->fails()) {
            return ResponseBuilder::error(400, "Validasi gagal", $validator->errors());
        }
        
        try {
            DB::beginTransaction();
            
            // Super admin hanya dapat dibuat oleh super admin
            if ($request->role === 'super_admin' && $user->role !== 'super_admin') {
                return ResponseBuilder::error(403, "Anda tidak memiliki izin untuk membuat super admin");
            }
            
            // Admin hanya dapat membuat user di sekolahnya sendiri
            if ($user->role === 'admin' && $request->sekolah_id !== $user->sekolah_id) {
                return ResponseBuilder::error(403, "Anda hanya dapat membuat user di sekolah Anda sendiri");
            }
            
            $userData = [
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'fullname' => $request->fullname,
                'email' => $request->email,
                'role' => $request->role,
                'sekolah_id' => $request->sekolah_id,
                'no_telepon' => $request->no_telepon,
                'is_active' => $request->input('is_active', true)
            ];
            
            $newUser = User::create($userData);
            
            // Jika role adalah guru, buat data guru
            if ($request->role === 'guru' && $request->sekolah_id) {
                $guru = Guru::create([
                    'nama' => $request->fullname,
                    'email' => $request->email,
                    'nip' => $request->input('nip'),
                    'no_telp' => $request->no_telepon,
                    'user_id' => $newUser->id,
                    'sekolah_id' => $request->sekolah_id
                ]);
                
                // Assign mata pelajaran jika ada
                if ($request->has('mata_pelajaran') && is_array($request->mata_pelajaran)) {
                    $guru->mataPelajaran()->attach($request->mata_pelajaran);
                }
            }
            
            DB::commit();
            
            $newUser->load(['sekolah', 'guru']);
            
            return ResponseBuilder::success(201, "Berhasil membuat user baru", $newUser);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal membuat user: " . $e->getMessage());
        }
    }

    public function show($id)
    {
        $user = Auth::user();
        
        try {
            $userData = User::with(['sekolah', 'guru'])->find($id);
            
            if (!$userData) {
                return ResponseBuilder::error(404, "User tidak ditemukan");
            }
            
            // Super admin dapat melihat semua user
            if ($user->role !== 'super_admin') {
                // Admin dan guru hanya dapat melihat user di sekolah mereka
                if ($userData->sekolah_id !== $user->sekolah_id) {
                    return ResponseBuilder::error(403, "Anda tidak memiliki izin untuk melihat user ini");
                }
            }
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan detail user", $userData);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        
        try {
            $userData = User::find($id);
            
            if (!$userData) {
                return ResponseBuilder::error(404, "User tidak ditemukan");
            }
            
            // Super admin dapat mengupdate semua user
            if ($user->role === 'super_admin') {
                $validator = Validator::make($request->all(), [
                    'username' => 'sometimes|required|string|unique:users,username,'.$id,
                    'password' => 'nullable|string|min:6',
                    'fullname' => 'sometimes|required|string|max:255',
                    'email' => 'sometimes|required|email|unique:users,email,'.$id,
                    'role' => 'sometimes|required|in:super_admin,admin,guru',
                    'sekolah_id' => 'nullable|required_if:role,admin,guru|exists:sekolah,id',
                    'no_telepon' => 'nullable|string|max:20',
                    'is_active' => 'nullable|boolean'
                ]);
                
                if ($validator->fails()) {
                    return ResponseBuilder::error(400, "Validasi gagal", $validator->errors());
                }
                
                DB::beginTransaction();
                
                $updateData = $request->only([
                    'username', 'fullname', 'email', 'role', 'sekolah_id', 'no_telepon', 'is_active'
                ]);
                
                // Update password jika ada
                if ($request->has('password') && $request->password) {
                    $updateData['password'] = Hash::make($request->password);
                }
                
                $userData->update($updateData);
                
                // Update atau buat data guru jika rolenya guru
                if ($userData->role === 'guru') {
                    $guru = Guru::where('user_id', $userData->id)->first();
                    
                    if ($guru) {
                        $guru->update([
                            'nama' => $userData->fullname,
                            'email' => $userData->email,
                            'no_telp' => $userData->no_telepon,
                            'sekolah_id' => $userData->sekolah_id
                        ]);
                        
                        // Update NIP jika ada
                        if ($request->has('nip')) {
                            $guru->update(['nip' => $request->nip]);
                        }
                        
                        // Update mata pelajaran jika ada
                        if ($request->has('mata_pelajaran')) {
                            $guru->mataPelajaran()->sync($request->mata_pelajaran);
                        }
                    } else {
                        $guru = Guru::create([
                            'nama' => $userData->fullname,
                            'email' => $userData->email,
                            'nip' => $request->input('nip'),
                            'no_telp' => $userData->no_telepon,
                            'user_id' => $userData->id,
                            'sekolah_id' => $userData->sekolah_id
                        ]);
                        
                        // Assign mata pelajaran jika ada
                        if ($request->has('mata_pelajaran') && is_array($request->mata_pelajaran)) {
                            $guru->mataPelajaran()->attach($request->mata_pelajaran);
                        }
                    }
                }
                
                DB::commit();
            } else if ($user->role === 'admin') {
                // Admin hanya dapat mengupdate user di sekolahnya sendiri
                if ($userData->sekolah_id !== $user->sekolah_id) {
                    return ResponseBuilder::error(403, "Anda hanya dapat mengupdate user di sekolah Anda sendiri");
                }
                
                $validator = Validator::make($request->all(), [
                    'username' => 'sometimes|required|string|unique:users,username,'.$id,
                    'password' => 'nullable|string|min:6',
                    'fullname' => 'sometimes|required|string|max:255',
                    'email' => 'sometimes|required|email|unique:users,email,'.$id,
                    'role' => 'sometimes|required|in:admin,guru',
                    'no_telepon' => 'nullable|string|max:20',
                    'is_active' => 'nullable|boolean'
                ]);
                
                if ($validator->fails()) {
                    return ResponseBuilder::error(400, "Validasi gagal", $validator->errors());
                }
                
                DB::beginTransaction();
                
                $updateData = $request->only([
                    'username', 'fullname', 'email', 'role', 'no_telepon', 'is_active'
                ]);
                
                // Admin tidak dapat mengubah sekolah_id
                $updateData['sekolah_id'] = $userData->sekolah_id;
                
                // Update password jika ada
                if ($request->has('password') && $request->password) {
                    $updateData['password'] = Hash::make($request->password);
                }
                
                $userData->update($updateData);
                
                // Update atau buat data guru jika rolenya guru
                if ($userData->role === 'guru') {
                    $guru = Guru::where('user_id', $userData->id)->first();
                    
                    if ($guru) {
                        $guru->update([
                            'nama' => $userData->fullname,
                            'email' => $userData->email,
                            'no_telp' => $userData->no_telepon
                        ]);
                        
                        // Update NIP jika ada
                        if ($request->has('nip')) {
                            $guru->update(['nip' => $request->nip]);
                        }
                        
                        // Update mata pelajaran jika ada
                        if ($request->has('mata_pelajaran')) {
                            $guru->mataPelajaran()->sync($request->mata_pelajaran);
                        }
                    } else {
                        $guru = Guru::create([
                            'nama' => $userData->fullname,
                            'email' => $userData->email,
                            'nip' => $request->input('nip'),
                            'no_telp' => $userData->no_telepon,
                            'user_id' => $userData->id,
                            'sekolah_id' => $userData->sekolah_id
                        ]);
                        
                        // Assign mata pelajaran jika ada
                        if ($request->has('mata_pelajaran') && is_array($request->mata_pelajaran)) {
                            $guru->mataPelajaran()->attach($request->mata_pelajaran);
                        }
                    }
                }
                
                DB::commit();
            } else {
                // Guru hanya dapat mengupdate dirinya sendiri
                if ($user->id != $id) {
                    return ResponseBuilder::error(403, "Anda hanya dapat mengupdate data diri sendiri");
                }
                
                $validator = Validator::make($request->all(), [
                    'username' => 'sometimes|required|string|unique:users,username,'.$id,
                    'password' => 'nullable|string|min:6',
                    'fullname' => 'sometimes|required|string|max:255',
                    'email' => 'sometimes|required|email|unique:users,email,'.$id,
                    'no_telepon' => 'nullable|string|max:20'
                ]);
                
                if ($validator->fails()) {
                    return ResponseBuilder::error(400, "Validasi gagal", $validator->errors());
                }
                
                DB::beginTransaction();
                
                $updateData = $request->only([
                    'username', 'fullname', 'email', 'no_telepon'
                ]);
                
                // Guru tidak dapat mengubah role dan sekolah_id
                $updateData['role'] = $userData->role;
                $updateData['sekolah_id'] = $userData->sekolah_id;
                $updateData['is_active'] = $userData->is_active;
                
                // Update password jika ada
                if ($request->has('password') && $request->password) {
                    $updateData['password'] = Hash::make($request->password);
                }
                
                $userData->update($updateData);
                
                // Update data guru
                $guru = Guru::where('user_id', $userData->id)->first();
                
                if ($guru) {
                    $guru->update([
                        'nama' => $userData->fullname,
                        'email' => $userData->email,
                        'no_telp' => $userData->no_telepon
                    ]);
                }
                
                DB::commit();
            }
            
            $userData->load(['sekolah', 'guru']);
            
            return ResponseBuilder::success(200, "Berhasil mengupdate user", $userData);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal mengupdate user: " . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $user = Auth::user();
        
        try {
            $userData = User::find($id);
            
            if (!$userData) {
                return ResponseBuilder::error(404, "User tidak ditemukan");
            }
            
            // Super admin dapat menghapus semua user kecuali dirinya sendiri
            if ($user->role === 'super_admin') {
                if ($user->id == $id) {
                    return ResponseBuilder::error(400, "Anda tidak dapat menghapus akun Anda sendiri");
                }
            } else if ($user->role === 'admin') {
                // Admin hanya dapat menghapus user di sekolahnya sendiri dan bukan dirinya sendiri
                if ($userData->sekolah_id !== $user->sekolah_id) {
                    return ResponseBuilder::error(403, "Anda hanya dapat menghapus user di sekolah Anda sendiri");
                }
                
                if ($user->id == $id) {
                    return ResponseBuilder::error(400, "Anda tidak dapat menghapus akun Anda sendiri");
                }
                
                // Admin tidak dapat menghapus super admin
                if ($userData->role === 'super_admin') {
                    return ResponseBuilder::error(403, "Anda tidak memiliki izin untuk menghapus super admin");
                }
            } else {
                // Guru tidak dapat menghapus user
                return ResponseBuilder::error(403, "Anda tidak memiliki izin untuk menghapus user");
            }
            
            DB::beginTransaction();
            
            // Hapus data guru jika ada
            if ($userData->guru) {
                $userData->guru->delete();
            }
            
            // Hapus user
            $userData->delete();
            
            DB::commit();
            
            return ResponseBuilder::success(200, "Berhasil menghapus user");
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal menghapus user: " . $e->getMessage());
        }
    }

    public function profile(Request $request)
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
                'username' => $user->username,
                'email' => $user->email,
                'fullname' => $user->fullname,
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