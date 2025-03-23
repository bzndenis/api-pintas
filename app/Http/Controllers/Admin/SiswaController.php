<?php

namespace App\Http\Controllers\Admin;

use App\Models\Siswa;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

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

    public function storeBatch(Request $request)
    {
        $this->validate($request, [
            'siswa' => 'required|array|min:1',
            'siswa.*.nama' => 'required|string|max:255',
            'siswa.*.nis' => 'required|string|unique:siswa,nis',
            'siswa.*.nisn' => 'nullable|string|unique:siswa,nisn',
            'siswa.*.jenis_kelamin' => 'required|in:L,P',
            'siswa.*.kelas_id' => 'required|exists:kelas,id'
        ]);

        try {
            $admin = Auth::user();
            $siswaData = $request->siswa;
            $importedData = [];
            $errors = [];
            $imported = 0;
            
            DB::beginTransaction();
            
            foreach ($siswaData as $index => $data) {
                try {
                    // Generate password
                    $password = Str::random(8);
                    
                    // Buat user baru jika email disediakan
                    $userId = null;
                    if (isset($data['email']) && !empty($data['email'])) {
                        $user = User::create([
                            'name' => $data['nama'],
                            'email' => $data['email'],
                            'password' => Hash::make($password),
                            'role' => 'siswa',
                            'sekolah_id' => $admin->sekolah_id
                        ]);
                        $userId = $user->id;
                    }
                    
                    // Buat data siswa
                    $siswa = Siswa::create([
                        'nama' => $data['nama'],
                        'nis' => $data['nis'],
                        'nisn' => $data['nisn'] ?? null,
                        'jenis_kelamin' => $data['jenis_kelamin'],
                        'tempat_lahir' => $data['tempat_lahir'] ?? null,
                        'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
                        'alamat' => $data['alamat'] ?? null,
                        'nama_ortu' => $data['nama_ortu'] ?? null,
                        'no_telp_ortu' => $data['no_telp_ortu'] ?? null,
                        'kelas_id' => $data['kelas_id'],
                        'user_id' => $userId,
                        'sekolah_id' => $admin->sekolah_id
                    ]);
                    
                    $importedData[] = [
                        'id' => $siswa->id,
                        'nama' => $data['nama'],
                        'nis' => $data['nis'],
                        'nisn' => $data['nisn'] ?? null,
                        'kelas_id' => $data['kelas_id'],
                        'password' => $userId ? $password : null
                    ];
                    
                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = [
                        'row' => $index + 1,
                        'nama' => $data['nama'] ?? 'Unknown',
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            DB::commit();
            
            return ResponseBuilder::success(200, "Berhasil menambahkan $imported data siswa", [
                'imported' => $imported,
                'errors' => $errors,
                'data' => $importedData
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal menambahkan data: " . $e->getMessage());
        }
    }
} 