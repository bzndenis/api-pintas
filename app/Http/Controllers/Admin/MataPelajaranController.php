<?php

namespace App\Http\Controllers\Admin;

use App\Models\MataPelajaran;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MataPelajaranController extends BaseAdminController
{
    public function index()
    {
        try {
            $mapel = MataPelajaran::where('sekolah_id', Auth::user()->sekolah_id)
                ->with(['guru', 'capaianPembelajaran'])
                ->get();
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan data mata pelajaran", $mapel);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'nama_mapel' => 'required|string|max:255',
            'kode_mapel' => 'required|string|max:50',
            'guru_id' => 'required|exists:guru,id'
        ]);

        try {
            $data = $request->all();
            $data['sekolah_id'] = Auth::user()->sekolah_id;
            
            $mapel = MataPelajaran::create($data);
            
            return ResponseBuilder::success(201, "Berhasil menambahkan mata pelajaran", $mapel);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal menambahkan data: " . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'nama_mapel' => 'required|string|max:255',
            'kode_mapel' => 'required|string|max:50',
            'guru_id' => 'required|exists:guru,id'
        ]);

        try {
            $mapel = MataPelajaran::where('sekolah_id', Auth::user()->sekolah_id)
                ->find($id);
            
            if (!$mapel) {
                return ResponseBuilder::error(404, "Mata pelajaran tidak ditemukan");
            }
            
            $mapel->update($request->all());
            
            return ResponseBuilder::success(200, "Berhasil mengupdate mata pelajaran", $mapel);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mengupdate data: " . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $admin = Auth::user();
            
            $mapel = MataPelajaran::where('sekolah_id', $admin->sekolah_id)->find($id);
            
            if (!$mapel) {
                return ResponseBuilder::error(404, "Data mata pelajaran tidak ditemukan");
            }
            
            DB::beginTransaction();
            
            // Cek apakah mata pelajaran masih digunakan oleh guru
            if ($mapel->guru()->count() > 0) {
                return ResponseBuilder::error(400, "Tidak dapat menghapus mata pelajaran yang masih diajarkan oleh guru");
            }
            
            // Cek apakah mata pelajaran masih digunakan oleh capaian pembelajaran
            if ($mapel->capaianPembelajaran()->count() > 0) {
                return ResponseBuilder::error(400, "Tidak dapat menghapus mata pelajaran yang masih memiliki capaian pembelajaran");
            }
            
            // Hapus mata pelajaran
            $mapel->delete();
            
            DB::commit();
            
            return ResponseBuilder::success(200, "Berhasil menghapus data mata pelajaran");
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal menghapus data: " . $e->getMessage());
        }
    }
} 