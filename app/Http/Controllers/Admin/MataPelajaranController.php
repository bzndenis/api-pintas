<?php

namespace App\Http\Controllers\Admin;

use App\Models\MataPelajaran;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;

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
} 