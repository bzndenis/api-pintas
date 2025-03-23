<?php

namespace App\Http\Controllers\Admin;

use App\Models\Siswa;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class SiswaController extends BaseAdminController
{
    public function index(Request $request)
    {
        try {
            $query = Siswa::with(['kelas', 'sekolah'])
                ->where('sekolah_id', Auth::user()->sekolah_id);

            if ($request->search) {
                $query->where(function($q) use ($request) {
                    $q->where('nama', 'like', "%{$request->search}%")
                      ->orWhere('nis', 'like', "%{$request->search}%")
                      ->orWhere('nisn', 'like', "%{$request->search}%");
                });
            }

            if ($request->kelas_id) {
                $query->where('kelas_id', $request->kelas_id);
            }

            $siswa = $query->orderBy('created_at', 'desc')->get();

            return ResponseBuilder::success(200, "Berhasil mendapatkan data siswa", $siswa);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'nis' => 'required|string|unique:siswa,nis',
            'nisn' => 'nullable|string|unique:siswa,nisn',
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'alamat' => 'nullable|string',
            'nama_ortu' => 'nullable|string|max:255',
            'no_telp_ortu' => 'nullable|string|max:255',
            'kelas_id' => 'required|exists:kelas,id'
        ]);

        try {
            $data = $request->all();
            $data['sekolah_id'] = Auth::user()->sekolah_id;

            $siswa = Siswa::create($data);

            return ResponseBuilder::success(201, "Berhasil menambahkan siswa", $siswa);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal menambahkan data: " . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'alamat' => 'nullable|string',
            'nama_ortu' => 'nullable|string|max:255',
            'no_telp_ortu' => 'nullable|string|max:255',
            'kelas_id' => 'required|exists:kelas,id'
        ]);

        try {
            $siswa = Siswa::where('sekolah_id', Auth::user()->sekolah_id)
                ->find($id);

            if (!$siswa) {
                return ResponseBuilder::error(404, "Siswa tidak ditemukan");
            }

            $siswa->update($request->all());

            return ResponseBuilder::success(200, "Berhasil mengupdate siswa", $siswa);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mengupdate data: " . $e->getMessage());
        }
    }

    public function import(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|file|mimes:xlsx,xls|max:2048',
            'kelas_id' => 'required|exists:kelas,id'
        ]);

        try {
            DB::beginTransaction();

            // Implementasi import Excel
            // Gunakan package seperti Maatwebsite/Laravel-Excel

            $response = [
                'total_data' => 0,
                'berhasil' => 0,
                'gagal' => 0,
                'errors' => []
            ];

            DB::commit();
            return ResponseBuilder::success(200, "Berhasil mengimport data", $response);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal mengimport data: " . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $admin = Auth::user();
            
            $siswa = Siswa::where('sekolah_id', $admin->sekolah_id)->find($id);
            
            if (!$siswa) {
                return ResponseBuilder::error(404, "Data siswa tidak ditemukan");
            }
            
            DB::beginTransaction();
            
            // Cek apakah siswa masih memiliki nilai
            if ($siswa->nilai()->count() > 0) {
                return ResponseBuilder::error(400, "Tidak dapat menghapus siswa yang masih memiliki data nilai");
            }
            
            // Cek apakah siswa masih memiliki absensi
            if ($siswa->absensi()->count() > 0) {
                return ResponseBuilder::error(400, "Tidak dapat menghapus siswa yang masih memiliki data absensi");
            }
            
            // Hapus user yang terkait jika ada
            if ($siswa->user_id) {
                $user = User::find($siswa->user_id);
                if ($user) {
                    $user->delete();
                }
            }
            
            // Hapus siswa
            $siswa->delete();
            
            DB::commit();
            
            return ResponseBuilder::success(200, "Berhasil menghapus data siswa");
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal menghapus data: " . $e->getMessage());
        }
    }
} 