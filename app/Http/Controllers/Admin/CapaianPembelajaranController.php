<?php

namespace App\Http\Controllers\Admin;

use App\Models\CapaianPembelajaran;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CapaianPembelajaranController extends BaseAdminController
{
    public function index()
    {
        try {
            $cp = CapaianPembelajaran::where('sekolah_id', Auth::user()->sekolah_id)
                ->with(['mataPelajaran', 'tujuanPembelajaran'])
                ->get();
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan data capaian pembelajaran", $cp);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'kode_cp' => 'required|string|max:50',
            'deskripsi' => 'required|string',
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id'
        ]);

        try {
            $data = $request->all();
            $data['sekolah_id'] = Auth::user()->sekolah_id;
            
            $cp = CapaianPembelajaran::create($data);
            
            return ResponseBuilder::success(201, "Berhasil menambahkan capaian pembelajaran", $cp);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal menambahkan data: " . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'kode_cp' => 'required|string|max:50',
            'deskripsi' => 'required|string',
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id'
        ]);

        try {
            $cp = CapaianPembelajaran::where('sekolah_id', Auth::user()->sekolah_id)
                ->find($id);
            
            if (!$cp) {
                return ResponseBuilder::error(404, "Capaian pembelajaran tidak ditemukan");
            }
            
            $cp->update($request->all());
            
            return ResponseBuilder::success(200, "Berhasil mengupdate capaian pembelajaran", $cp);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mengupdate data: " . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $admin = Auth::user();
            
            $cp = CapaianPembelajaran::whereHas('mataPelajaran', function($q) use ($admin) {
                $q->where('sekolah_id', $admin->sekolah_id);
            })->find($id);
            
            if (!$cp) {
                return ResponseBuilder::error(404, "Data capaian pembelajaran tidak ditemukan");
            }
            
            DB::beginTransaction();
            
            // Cek apakah capaian pembelajaran masih digunakan oleh tujuan pembelajaran
            if ($cp->tujuanPembelajaran()->count() > 0) {
                return ResponseBuilder::error(400, "Tidak dapat menghapus capaian pembelajaran yang masih memiliki tujuan pembelajaran");
            }
            
            // Hapus capaian pembelajaran
            $cp->delete();
            
            DB::commit();
            
            return ResponseBuilder::success(200, "Berhasil menghapus data capaian pembelajaran");
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal menghapus data: " . $e->getMessage());
        }
    }
} 