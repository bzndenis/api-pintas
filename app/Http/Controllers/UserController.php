<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserAuth;
use App\Models\Guru;
use App\Models\Sekolah;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware("login");
    }

    public function index(Request $request)
    {
        $role = $request->query('role');
        $sekolahId = $request->query('sekolah_id');
        
        $query = User::with('sekolah');
        
        // Filter berdasarkan role jika ada
        if ($role) {
            $query->where('role', $role);
        }
        
        // Filter berdasarkan sekolah jika ada
        if ($sekolahId) {
            $query->where('sekolah_id', $sekolahId);
        }
        
        $data = $query->orderBy('created_at', 'desc')->get();
        
        return ResponseBuilder::success(200, "Berhasil Mendapatkan Data", $data, true, false);
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
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required|min:6',
            'new_password' => 'required|min:6',
        ]);

        $email = $request->input("email");
        $password = $request->input("password");
        $newPassword = $request->input("new_password");

        $user = User::where('email', $email)->first();

        if (!$user) {
            $out = [
                "message" => "User tidak ditemukan",
                "code" => 404,
            ];
            return response()->json($out, $out['code']);
        }

        if (Hash::check($password, $user->password)) {
            $hashNewPwd = Hash::make($newPassword);

            $user->update([
                'password' => $hashNewPwd,
            ]);

            $out = [
                "message" => "Password berhasil diperbarui",
                "code" => 200,
            ];
        } else {
            $out = [
                "message" => "Password lama salah",
                "code" => 401,
            ];
        }

        return response()->json($out, $out['code']);
    }
    
    public function getUserProfile(Request $request)
    {
        $token = $request->header('Authorization');
        
        if (!$token) {
            return response()->json([
                'message' => 'Token tidak tersedia',
                'code' => 401,
            ], 401);
        }
        
        $user = User::with(['sekolah', 'guru'])
            ->where('remember_token', $token)
            ->where('is_active', true)
            ->first();
        
        if (!$user) {
            return response()->json([
                'message' => 'User tidak ditemukan atau token tidak valid',
                'code' => 404,
            ], 404);
        }
        
        return response()->json([
            'message' => 'Berhasil Mendapatkan Data',
            'code' => 200,
            'data' => $user,
        ], 200);
    }
}