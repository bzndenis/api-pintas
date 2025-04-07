<?php

namespace App\Http\Controllers\Guru;

use App\Models\Siswa;
use App\Models\Kelas;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class SiswaController extends BaseGuruController
{
    /**
     * Mendapatkan semua siswa berdasarkan sekolah
     */
    public function index(Request $request)
    {
        try {
            $guru = Auth::user()->guru;
            $sekolahId = Auth::user()->sekolah_id;
            
            // Ambil siswa berdasarkan kelas yang diajar oleh guru
            $query = Siswa::with(['kelas'])
                ->where('sekolah_id', $sekolahId)
                ->whereHas('kelas', function($q) use ($guru) {
                    $q->where('guru_id', $guru->id);
                });
            
            // Filter berdasarkan nama (jika ada)
            if ($request->search) {
                $query->where(function($q) use ($request) {
                    $q->where('nama', 'like', "%{$request->search}%")
                      ->orWhere('nisn', 'like', "%{$request->search}%");
                });
            }
            
            $siswa = $query->orderBy('nama', 'asc')->get();
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan data siswa", $siswa);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }
    
    /**
     * Mendapatkan semua siswa berdasarkan kelas
     */
    public function getSiswaByKelas(Request $request, $kelasId)
    {
        try {
            // Validasi format UUID
            if (!Uuid::isValid($kelasId)) {
                return ResponseBuilder::error(400, "Format ID tidak valid");
            }
            
            $guru = Auth::user()->guru;
            $sekolahId = Auth::user()->sekolah_id;
            
            // Debug: Log ID guru dan kelas
            \Log::info('Checking access:', [
                'guru_id' => $guru->id,
                'kelas_id' => $kelasId
            ]);
            
            // Periksa apakah kelas tersebut diajar oleh guru yang login
            $query = Kelas::with(['mataPelajaran'])
                ->where('guru_id', $guru->id)
                ->where('kelas.id', $kelasId)
                ->whereNull('kelas.deleted_at');
            
            // Debug: Log query
            \Log::info('Query:', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);
            
            $kelas = $query->first();
            
            if (!$kelas) {
                // Debug: Log pertemuan yang ada
                $pertemuanGuru = DB::table('pertemuan')
                    ->where('guru_id', $guru->id)
                    ->get();
                \Log::info('Pertemuan guru:', $pertemuanGuru->toArray());
                
                return ResponseBuilder::error(404, "Data kelas tidak ditemukan atau Anda tidak memiliki akses");
            }
            
            // Ambil siswa berdasarkan kelas
            $query = Siswa::with(['kelas'])
                ->where('kelas_id', $kelasId)
                ->where('sekolah_id', $sekolahId);
            
            // Filter berdasarkan nama (jika ada)
            if ($request->search) {
                $query->where(function($q) use ($request) {
                    $q->where('nama', 'like', "%{$request->search}%")
                      ->orWhere('nisn', 'like', "%{$request->search}%");
                });
            }
            
            $siswa = $query->orderBy('nama', 'asc')->get();
            
            // Debug: Log hasil
            \Log::info('Result:', [
                'kelas' => $kelas->toArray(),
                'total_siswa' => $siswa->count()
            ]);
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan data siswa kelas", [
                'kelas' => $kelas,
                'siswa' => $siswa
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getSiswaByKelas: ' . $e->getMessage());
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }
    
    /**
     * Mendapatkan detail siswa berdasarkan ID
     */
    public function show($id)
    {
        try {
            // Validasi format UUID
            if (!Uuid::isValid($id)) {
                return ResponseBuilder::error(400, "Format ID tidak valid");
            }
            
            $guru = Auth::user()->guru;
            $sekolahId = Auth::user()->sekolah_id;
            
            $siswa = Siswa::with(['kelas'])
                ->where('id', $id)
                ->where('sekolah_id', $sekolahId)
                ->whereHas('kelas', function($q) use ($guru) {
                    $q->where('guru_id', $guru->id);
                })
                ->first();
            
            if (!$siswa) {
                return ResponseBuilder::error(404, "Data siswa tidak ditemukan atau Anda tidak memiliki akses");
            }
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan detail siswa", $siswa);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }
    
    /**
     * Menambahkan siswa ke kelas
     */
    public function addSiswaToKelas(Request $request)
    {
        $this->validate($request, [
            'siswa_id' => 'required|exists:siswa,id',
            'kelas_id' => 'required|exists:kelas,id'
        ]);
        
        try {
            DB::beginTransaction();
            
            $guru = Auth::user()->guru;
            $sekolahId = Auth::user()->sekolah_id;
            
            // Debug: Log ID guru dan kelas
            \Log::info('Adding siswa to kelas:', [
                'guru_id' => $guru->id,
                'kelas_id' => $request->kelas_id,
                'siswa_id' => $request->siswa_id
            ]);
            
            // Periksa apakah kelas tersebut diajar oleh guru yang login
            $query = Kelas::where('guru_id', $guru->id)
                ->where('id', $request->kelas_id)
                ->whereNull('deleted_at');
            
            // Debug: Log query
            \Log::info('Query:', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);
            
            $kelas = $query->first();
            
            if (!$kelas) {
                // Debug: Log pertemuan yang ada
                $pertemuanGuru = DB::table('pertemuan')
                    ->where('guru_id', $guru->id)
                    ->get();
                \Log::info('Pertemuan guru:', $pertemuanGuru->toArray());
                
                return ResponseBuilder::error(404, "Data kelas tidak ditemukan atau Anda tidak memiliki akses");
            }
            
            // Periksa apakah siswa tersebut ada di sekolah yang sama
            $siswa = Siswa::where('id', $request->siswa_id)
                ->where('sekolah_id', $sekolahId)
                ->first();
            
            if (!$siswa) {
                return ResponseBuilder::error(404, "Data siswa tidak ditemukan");
            }
            
            // Update kelas siswa
            $siswa->kelas_id = $kelas->id;
            $siswa->save();
            
            DB::commit();
            
            return ResponseBuilder::success(200, "Berhasil menambahkan siswa ke kelas", [
                'kelas' => $kelas,
                'siswa' => $siswa
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in addSiswaToKelas: ' . $e->getMessage());
            return ResponseBuilder::error(500, "Gagal menambahkan siswa ke kelas: " . $e->getMessage());
        }
    }
    
    /**
     * Mengubah kelas siswa
     */
    public function updateSiswaKelas(Request $request, $id)
    {
        $this->validate($request, [
            'kelas_id' => 'required|exists:kelas,id'
        ]);
        
        try {
            DB::beginTransaction();
            
            // Validasi format UUID
            if (!Uuid::isValid($id)) {
                return ResponseBuilder::error(400, "Format ID tidak valid");
            }
            
            $guru = Auth::user()->guru;
            $sekolahId = Auth::user()->sekolah_id;
            
            // Debug: Log ID guru dan kelas
            \Log::info('Updating siswa kelas:', [
                'guru_id' => $guru->id,
                'kelas_id' => $request->kelas_id,
                'siswa_id' => $id
            ]);
            
            // Periksa apakah kelas tersebut diajar oleh guru yang login
            $query = Kelas::where('guru_id', $guru->id)
                ->where('id', $request->kelas_id)
                ->whereNull('deleted_at');
            
            // Debug: Log query
            \Log::info('Query:', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);
            
            $kelas = $query->first();
            
            if (!$kelas) {
                // Debug: Log pertemuan yang ada
                $pertemuanGuru = DB::table('pertemuan')
                    ->where('guru_id', $guru->id)
                    ->get();
                \Log::info('Pertemuan guru:', $pertemuanGuru->toArray());
                
                return ResponseBuilder::error(404, "Data kelas tidak ditemukan atau Anda tidak memiliki akses");
            }
            
            // Periksa apakah siswa tersebut ada di sekolah yang sama
            $siswa = Siswa::where('id', $id)
                ->where('sekolah_id', $sekolahId)
                ->first();
            
            if (!$siswa) {
                return ResponseBuilder::error(404, "Data siswa tidak ditemukan");
            }
            
            // Update kelas siswa
            $siswa->kelas_id = $kelas->id;
            $siswa->save();
            
            DB::commit();
            
            return ResponseBuilder::success(200, "Berhasil mengubah kelas siswa", [
                'kelas' => $kelas,
                'siswa' => $siswa
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in updateSiswaKelas: ' . $e->getMessage());
            return ResponseBuilder::error(500, "Gagal mengubah kelas siswa: " . $e->getMessage());
        }
    }
    
    /**
     * Menghapus siswa dari kelas
     */
    public function removeSiswaFromKelas($id)
    {
        try {
            DB::beginTransaction();
            
            // Validasi format UUID
            if (!Uuid::isValid($id)) {
                return ResponseBuilder::error(400, "Format ID tidak valid");
            }
            
            $guru = Auth::user()->guru;
            $sekolahId = Auth::user()->sekolah_id;
            
            // Periksa apakah siswa tersebut ada di sekolah yang sama dan diajar oleh guru
            $siswa = Siswa::where('id', $id)
                ->where('sekolah_id', $sekolahId)
                ->whereHas('kelas', function($q) use ($guru) {
                    $q->where('guru_id', $guru->id);
                })
                ->first();
            
            if (!$siswa) {
                return ResponseBuilder::error(404, "Data siswa tidak ditemukan atau Anda tidak memiliki akses");
            }
            
            // Set kelas_id menjadi null (menghapus dari kelas)
            $siswa->kelas_id = null;
            $siswa->save();
            
            DB::commit();
            
            return ResponseBuilder::success(200, "Berhasil menghapus siswa dari kelas", $siswa);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal menghapus siswa dari kelas: " . $e->getMessage());
        }
    }
} 