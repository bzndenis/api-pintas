<?php

namespace App\Http\Controllers\Admin;

use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TahunAjaranController extends BaseAdminController
{
    public function index()
    {
        try {
            $tahunAjaran = TahunAjaran::where('sekolah_id', Auth::user()->sekolah_id)
                ->orderBy('tanggal_mulai', 'desc')
                ->get();
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan data tahun ajaran", $tahunAjaran);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'nama_tahun_ajaran' => 'required|string|max:255',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai'
        ]);

        try {
            $data = $request->all();
            $data['sekolah_id'] = Auth::user()->sekolah_id;
            
            $tahunAjaran = TahunAjaran::create($data);
            
            return ResponseBuilder::success(201, "Berhasil menambahkan tahun ajaran", $tahunAjaran);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal menambahkan data: " . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'nama_tahun_ajaran' => 'required|string|max:255',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai'
        ]);

        try {
            $tahunAjaran = TahunAjaran::where('sekolah_id', Auth::user()->sekolah_id)
                ->find($id);
            
            if (!$tahunAjaran) {
                return ResponseBuilder::error(404, "Tahun ajaran tidak ditemukan");
            }
            
            $tahunAjaran->update($request->all());
            
            return ResponseBuilder::success(200, "Berhasil mengupdate tahun ajaran", $tahunAjaran);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mengupdate data: " . $e->getMessage());
        }
    }

    public function activate($id)
    {
        try {
            $sekolahId = Auth::user()->sekolah_id;
            
            // Non-aktifkan semua tahun ajaran
            TahunAjaran::where('sekolah_id', $sekolahId)
                ->update(['is_active' => false]);
            
            // Aktifkan tahun ajaran yang dipilih
            $tahunAjaran = TahunAjaran::where('sekolah_id', $sekolahId)
                ->find($id);
                
            if (!$tahunAjaran) {
                return ResponseBuilder::error(404, "Tahun ajaran tidak ditemukan");
            }
            
            $tahunAjaran->update(['is_active' => true]);
            
            return ResponseBuilder::success(200, "Berhasil mengaktifkan tahun ajaran", $tahunAjaran);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mengaktifkan tahun ajaran: " . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $admin = Auth::user();
            
            $tahunAjaran = TahunAjaran::where('sekolah_id', $admin->sekolah_id)->find($id);
            
            if (!$tahunAjaran) {
                return ResponseBuilder::error(404, "Data tahun ajaran tidak ditemukan");
            }
            
            // Cek apakah tahun ajaran sedang aktif
            if ($tahunAjaran->is_active) {
                return ResponseBuilder::error(400, "Tidak dapat menghapus tahun ajaran yang sedang aktif");
            }
            
            // Cek apakah tahun ajaran masih digunakan oleh kelas
            if ($tahunAjaran->kelas()->count() > 0) {
                return ResponseBuilder::error(400, "Tidak dapat menghapus tahun ajaran yang masih digunakan oleh kelas");
            }
            
            // Hapus tahun ajaran
            $tahunAjaran->delete();
            
            return ResponseBuilder::success(200, "Berhasil menghapus data tahun ajaran");
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal menghapus data: " . $e->getMessage());
        }
    }
} 