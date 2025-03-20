<?php

namespace App\Http\Controllers;

use App\Models\CapaianPembelajaran;
use App\Models\TujuanPembelajaran;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\DB;

class CapaianPembelajaranController extends Controller
{
    public function __construct()
    {
        $this->middleware("login");
    }

    public function index(Request $request)
    {
        $mapelId = $request->query('mapel_id');
        $sekolahId = $request->query('sekolah_id');
        
        $query = CapaianPembelajaran::with(['mataPelajaran', 'sekolah']);
        
        if ($mapelId) {
            $query->where('mapel_id', $mapelId);
        }
        
        if ($sekolahId) {
            $query->where('sekolah_id', $sekolahId);
        }
        
        $data = $query->orderBy('created_at', 'desc')->get();
        
        return ResponseBuilder::success(200, "Berhasil Mendapatkan Data", $data, true, false);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'kode_cp' => 'required|string|max:50',
            'deskripsi' => 'required|string',
            'mapel_id' => 'required|exists:mata_pelajaran,id',
            'sekolah_id' => 'required|exists:sekolah,id'
        ]);

        try {
            DB::beginTransaction();
            
            // Cek apakah kode CP sudah ada untuk mata pelajaran yang sama
            $exists = CapaianPembelajaran::where('kode_cp', $request->kode_cp)
                                       ->where('mapel_id', $request->mapel_id)
                                       ->exists();
            
            if ($exists) {
                return ResponseBuilder::error(400, "Kode CP sudah digunakan untuk mata pelajaran ini");
            }
            
            $cp = CapaianPembelajaran::create($request->all());
            
            DB::commit();
            
            $cp->load(['mataPelajaran', 'sekolah']);
            
            return ResponseBuilder::success(201, "Berhasil Menambahkan Data", $cp, true);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal Menambahkan Data: " . $e->getMessage());
        }
    }

    public function show($id)
    {
        $cp = CapaianPembelajaran::with(['mataPelajaran', 'sekolah'])->find($id);
        
        if (!$cp) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        return ResponseBuilder::success(200, "Berhasil Mendapatkan Data", $cp, true);
    }

    public function update(Request $request, $id)
    {
        $cp = CapaianPembelajaran::find($id);
        
        if (!$cp) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        $this->validate($request, [
            'kode_cp' => 'sometimes|required|string|max:50',
            'deskripsi' => 'sometimes|required|string',
            'mapel_id' => 'sometimes|required|exists:mata_pelajaran,id',
            'sekolah_id' => 'sometimes|required|exists:sekolah,id'
        ]);
        
        try {
            DB::beginTransaction();
            
            // Cek apakah kode CP sudah ada untuk mata pelajaran yang sama (kecuali diri sendiri)
            if (($request->has('kode_cp') && $request->kode_cp != $cp->kode_cp) || 
                ($request->has('mapel_id') && $request->mapel_id != $cp->mapel_id)) {
                
                $exists = CapaianPembelajaran::where('kode_cp', $request->kode_cp ?? $cp->kode_cp)
                                           ->where('mapel_id', $request->mapel_id ?? $cp->mapel_id)
                                           ->where('id', '!=', $id)
                                           ->exists();
                
                if ($exists) {
                    return ResponseBuilder::error(400, "Kode CP sudah digunakan untuk mata pelajaran ini");
                }
            }
            
            $cp->update($request->all());
            
            DB::commit();
            
            $cp->load(['mataPelajaran', 'sekolah']);
            
            return ResponseBuilder::success(200, "Berhasil Mengubah Data", $cp, true);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal Mengubah Data: " . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $cp = CapaianPembelajaran::find($id);
        
        if (!$cp) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        try {
            // Cek apakah CP masih memiliki tujuan pembelajaran
            if ($cp->tujuanPembelajaran()->count() > 0) {
                return ResponseBuilder::error(400, "Tidak dapat menghapus capaian pembelajaran yang masih memiliki tujuan pembelajaran");
            }
            
            $cp->delete();
            return ResponseBuilder::success(200, "Berhasil Menghapus Data", null, true);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal Menghapus Data: " . $e->getMessage());
        }
    }
    
    public function getTujuanPembelajaran($id)
    {
        $cp = CapaianPembelajaran::find($id);
        
        if (!$cp) {
            return ResponseBuilder::error(404, "Data Capaian Pembelajaran Tidak ada");
        }
        
        $tp = TujuanPembelajaran::where('cp_id', $id)
                               ->orderBy('created_at', 'desc')
                               ->get();
        
        return ResponseBuilder::success(200, "Berhasil Mendapatkan Data Tujuan Pembelajaran", $tp, true);
    }
} 