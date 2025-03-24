<?php

namespace App\Http\Controllers\Admin;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Guru;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KelasController extends BaseAdminController
{
    public function index(Request $request)
    {
        try {
            $query = Kelas::with(['tahunAjaran', 'guru', 'siswa'])
                ->where('sekolah_id', Auth::user()->sekolah_id);

            if ($request->tahun_ajaran_id) {
                $query->where('tahun_ajaran_id', $request->tahun_ajaran_id);
            }

            if ($request->tingkat) {
                $query->where('tingkat', $request->tingkat);
            }

            $kelas = $query->orderBy('nama_kelas', 'asc')->get();

            return ResponseBuilder::success(200, "Berhasil mendapatkan data kelas", $kelas);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'nama_kelas' => 'required|string|max:255',
            'tingkat' => 'required|string|max:255',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'guru_id' => 'required|exists:guru,id'
        ]);

        try {
            $data = $request->all();
            $data['sekolah_id'] = Auth::user()->sekolah_id;

            $kelas = Kelas::create($data);

            return ResponseBuilder::success(201, "Berhasil menambahkan kelas", $kelas);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal menambahkan data: " . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'nama_kelas' => 'required|string|max:255',
            'tingkat' => 'required|string|max:255',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'guru_id' => 'required|exists:guru,id'
        ]);

        try {
            $kelas = Kelas::where('sekolah_id', Auth::user()->sekolah_id)->find($id);
            
            if (!$kelas) {
                return ResponseBuilder::error(404, "Kelas tidak ditemukan");
            }
            
            $kelas->update($request->all());
            
            return ResponseBuilder::success(200, "Berhasil mengupdate kelas", $kelas);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mengupdate data: " . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $kelas = Kelas::where('sekolah_id', Auth::user()->sekolah_id)->find($id);
            
            if (!$kelas) {
                return ResponseBuilder::error(404, "Kelas tidak ditemukan");
            }
            
            // Cek apakah kelas masih memiliki siswa
            if ($kelas->siswa()->count() > 0) {
                return ResponseBuilder::error(400, "Tidak dapat menghapus kelas yang masih memiliki siswa");
            }
            
            $kelas->delete();
            
            return ResponseBuilder::success(200, "Berhasil menghapus kelas");
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal menghapus data: " . $e->getMessage());
        }
    }

    public function detail($id)
    {
        try {
            $kelas = Kelas::with(['tahunAjaran', 'guru', 'siswa'])
                ->where('sekolah_id', Auth::user()->sekolah_id)
                ->find($id);
            
            if (!$kelas) {
                return ResponseBuilder::error(404, "Kelas tidak ditemukan");
            }
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan detail kelas", $kelas);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }

    public function assignGuru(Request $request, $id)
    {
        $this->validate($request, [
            'guru_id' => 'required|exists:guru,id'
        ]);

        try {
            $kelas = Kelas::where('sekolah_id', Auth::user()->sekolah_id)->find($id);
            
            if (!$kelas) {
                return ResponseBuilder::error(404, "Kelas tidak ditemukan");
            }
            
            $guru = Guru::where('sekolah_id', Auth::user()->sekolah_id)
                ->find($request->guru_id);
            
            if (!$guru) {
                return ResponseBuilder::error(404, "Guru tidak ditemukan");
            }
            
            $kelas->guru_id = $guru->id;
            $kelas->save();
            
            return ResponseBuilder::success(200, "Berhasil menetapkan guru ke kelas", $kelas);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal menetapkan guru: " . $e->getMessage());
        }
    }

    public function assignSiswa(Request $request, $id)
    {
        $this->validate($request, [
            'siswa_ids' => 'required|array',
            'siswa_ids.*' => 'exists:siswa,id'
        ]);

        try {
            DB::beginTransaction();
            
            $kelas = Kelas::where('sekolah_id', Auth::user()->sekolah_id)->find($id);
            
            if (!$kelas) {
                return ResponseBuilder::error(404, "Kelas tidak ditemukan");
            }
            
            // Update kelas_id untuk semua siswa yang dipilih
            Siswa::whereIn('id', $request->siswa_ids)
                ->where('sekolah_id', Auth::user()->sekolah_id)
                ->update(['kelas_id' => $kelas->id]);
            
            DB::commit();
            
            return ResponseBuilder::success(200, "Berhasil menetapkan siswa ke kelas", [
                'kelas' => $kelas,
                'siswa_count' => count($request->siswa_ids)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal menetapkan siswa: " . $e->getMessage());
        }
    }
} 