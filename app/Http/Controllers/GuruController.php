<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class GuruController extends Controller
{
    public function __construct()
    {
        $this->middleware("login");
    }

    public function index(Request $request)
    {
        $sekolahId = $request->query('sekolah_id');
        
        $query = Guru::with(['sekolah', 'user']);
        
        if ($sekolahId) {
            $query->where('sekolah_id', $sekolahId);
        }
        
        $data = $query->orderBy('created_at', 'desc')->get();
        
        return ResponseBuilder::success(200, "Berhasil Mendapatkan Data", $data, true, false);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'nama' => 'required|string|max:255',
            'nip' => 'nullable|string|max:50',
            'email' => 'required|email|max:100|unique:guru',
            'no_telp' => 'nullable|string|max:20',
            'sekolah_id' => 'required|exists:sekolah,id',
            'username' => 'required|string|max:50|unique:users',
            'password' => 'required|string|min:6'
        ]);

        try {
            DB::beginTransaction();
            
            // Buat user terlebih dahulu
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'guru',
                'sekolah_id' => $request->sekolah_id,
                'is_active' => true
            ]);
            
            // Buat data guru
            $guru = Guru::create([
                'nama' => $request->nama,
                'nip' => $request->nip,
                'email' => $request->email,
                'no_telp' => $request->no_telp,
                'user_id' => $user->id,
                'sekolah_id' => $request->sekolah_id
            ]);
            
            DB::commit();
            
            $guru->load(['sekolah', 'user']);
            
            return ResponseBuilder::success(201, "Berhasil Menambahkan Data", $guru, true);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal Menambahkan Data: " . $e->getMessage());
        }
    }

    public function show($id)
    {
        $guru = Guru::with(['sekolah', 'user'])->find($id);
        
        if (!$guru) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        return ResponseBuilder::success(200, "Berhasil Mendapatkan Data", $guru, true);
    }

    public function update(Request $request, $id)
    {
        $guru = Guru::find($id);
        
        if (!$guru) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        $this->validate($request, [
            'nama' => 'sometimes|required|string|max:255',
            'nip' => 'nullable|string|max:50',
            'email' => 'sometimes|required|email|max:100|unique:guru,email,'.$id.',id',
            'no_telp' => 'nullable|string|max:20',
            'sekolah_id' => 'sometimes|required|exists:sekolah,id',
        ]);
        
        try {
            DB::beginTransaction();
            
            $guru->update($request->only([
                'nama', 'nip', 'email', 'no_telp', 'sekolah_id'
            ]));
            
            // Update user jika ada
            if ($guru->user_id) {
                $user = User::find($guru->user_id);
                if ($user) {
                    $user->update([
                        'email' => $guru->email,
                        'sekolah_id' => $guru->sekolah_id
                    ]);
                    
                    // Update password jika ada
                    if ($request->has('password') && $request->password) {
                        $user->update([
                            'password' => Hash::make($request->password)
                        ]);
                    }
                }
            }
            
            DB::commit();
            
            $guru->load(['sekolah', 'user']);
            
            return ResponseBuilder::success(200, "Berhasil Mengubah Data", $guru, true);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal Mengubah Data: " . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $guru = Guru::find($id);
        
        if (!$guru) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        try {
            DB::beginTransaction();
            
            // Cek apakah guru masih memiliki kelas
            if ($guru->kelas()->count() > 0) {
                return ResponseBuilder::error(400, "Tidak dapat menghapus guru yang masih mengajar kelas");
            }
            
            // Hapus user yang terkait
            if ($guru->user_id) {
                $user = User::find($guru->user_id);
                if ($user) {
                    $user->delete();
                }
            }
            
            // Hapus guru
            $guru->delete();
            
            DB::commit();
            
            return ResponseBuilder::success(200, "Berhasil Menghapus Data", null, true);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal Menghapus Data: " . $e->getMessage());
        }
    }
} 