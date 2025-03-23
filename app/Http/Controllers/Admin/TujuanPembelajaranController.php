<?php

namespace App\Http\Controllers\Admin;

use App\Models\TujuanPembelajaran;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TujuanPembelajaranController extends BaseAdminController
{
    public function index()
    {
        try {
            $tp = TujuanPembelajaran::where('sekolah_id', Auth::user()->sekolah_id)
                ->with(['capaianPembelajaran.mataPelajaran'])
                ->get();
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan data tujuan pembelajaran", $tp);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'kode_tp' => 'required|string|max:50',
            'deskripsi' => 'required|string',
            'capaian_pembelajaran_id' => 'required|exists:capaian_pembelajaran,id'
        ]);

        try {
            $data = $request->all();
            $data['sekolah_id'] = Auth::user()->sekolah_id;
            
            $tp = TujuanPembelajaran::create($data);
            
            return ResponseBuilder::success(201, "Berhasil menambahkan tujuan pembelajaran", $tp);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal menambahkan data: " . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'kode_tp' => 'required|string|max:50',
            'deskripsi' => 'required|string',
            'capaian_pembelajaran_id' => 'required|exists:capaian_pembelajaran,id'
        ]);

        try {
            $tp = TujuanPembelajaran::where('sekolah_id', Auth::user()->sekolah_id)
                ->find($id);
            
            if (!$tp) {
                return ResponseBuilder::error(404, "Tujuan pembelajaran tidak ditemukan");
            }
            
            $tp->update($request->all());
            
            return ResponseBuilder::success(200, "Berhasil mengupdate tujuan pembelajaran", $tp);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mengupdate data: " . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $admin = Auth::user();
            
            $tp = TujuanPembelajaran::whereHas('capaianPembelajaran.mataPelajaran', function($q) use ($admin) {
                $q->where('sekolah_id', $admin->sekolah_id);
            })->find($id);
            
            if (!$tp) {
                return ResponseBuilder::error(404, "Data tujuan pembelajaran tidak ditemukan");
            }
            
            DB::beginTransaction();
            
            // Cek apakah tujuan pembelajaran masih digunakan oleh nilai
            if ($tp->nilai()->count() > 0) {
                return ResponseBuilder::error(400, "Tidak dapat menghapus tujuan pembelajaran yang masih memiliki data nilai");
            }
            
            // Hapus tujuan pembelajaran
            $tp->delete();
            
            DB::commit();
            
            return ResponseBuilder::success(200, "Berhasil menghapus data tujuan pembelajaran");
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal menghapus data: " . $e->getMessage());
        }
    }
} 