<?php

namespace App\Http\Controllers\Admin;

use App\Models\TujuanPembelajaran;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;

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
} 