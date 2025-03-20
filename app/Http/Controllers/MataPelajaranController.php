<?php

namespace App\Http\Controllers;

use App\Models\MataPelajaran;
use App\Models\CapaianPembelajaran;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\DB;

class MataPelajaranController extends Controller
{
    public function __construct()
    {
        $this->middleware("login");
    }

    public function index(Request $request)
    {
        $sekolahId = $request->query('sekolah_id');
        $tingkat = $request->query('tingkat');
        
        $query = MataPelajaran::with('sekolah');
        
        if ($sekolahId) {
            $query->where('sekolah_id', $sekolahId);
        }
        
        if ($tingkat) {
            $query->where('tingkat', $tingkat);
        }
        
        $data = $query->orderBy('created_at', 'desc')->get();
        
        return ResponseBuilder::success(200, "Berhasil Mendapatkan Data", $data, true, false);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'kode_mapel' => 'required|string|max:20',
            'nama_mapel' => 'required|string|max:255',
            'tingkat' => 'required|string|max:20',
            'sekolah_id' => 'required|exists:sekolah,id'
        ]);

        try {
            DB::beginTransaction();
            
            // Cek apakah kode mapel sudah ada untuk sekolah yang sama
            $exists = MataPelajaran::where('kode_mapel', $request->kode_mapel)
                                 ->where('sekolah_id', $request->sekolah_id)
                                 ->exists();
            
            if ($exists) {
                return ResponseBuilder::error(400, "Kode mapel sudah digunakan di sekolah ini");
            }
            
            $mapel = MataPelajaran::create($request->all());
            
            DB::commit();
            
            $mapel->load('sekolah');
            
            return ResponseBuilder::success(201, "Berhasil Menambahkan Data", $mapel, true);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal Menambahkan Data: " . $e->getMessage());
        }
    }

    public function show($id)
    {
        $mapel = MataPelajaran::with('sekolah')->find($id);
        
        if (!$mapel) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        return ResponseBuilder::success(200, "Berhasil Mendapatkan Data", $mapel, true);
    }

    public function update(Request $request, $id)
    {
        $mapel = MataPelajaran::find($id);
        
        if (!$mapel) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        $this->validate($request, [
            'kode_mapel' => 'sometimes|required|string|max:20',
            'nama_mapel' => 'sometimes|required|string|max:255',
            'tingkat' => 'sometimes|required|string|max:20',
            'sekolah_id' => 'sometimes|required|exists:sekolah,id'
        ]);
        
        try {
            DB::beginTransaction();
            
            // Cek apakah kode mapel sudah ada untuk sekolah yang sama (kecuali diri sendiri)
            if ($request->has('kode_mapel') && $request->kode_mapel != $mapel->kode_mapel) {
                $exists = MataPelajaran::where('kode_mapel', $request->kode_mapel)
                                     ->where('sekolah_id', $request->sekolah_id ?? $mapel->sekolah_id)
                                     ->where('id', '!=', $id)
                                     ->exists();
                
                if ($exists) {
                    return ResponseBuilder::error(400, "Kode mapel sudah digunakan di sekolah ini");
                }
            }
            
            $mapel->update($request->all());
            
            DB::commit();
            
            $mapel->load('sekolah');
            
            return ResponseBuilder::success(200, "Berhasil Mengubah Data", $mapel, true);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal Mengubah Data: " . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $mapel = MataPelajaran::find($id);
        
        if (!$mapel) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        try {
            // Cek apakah mapel masih memiliki capaian pembelajaran
            if ($mapel->capaianPembelajaran()->count() > 0) {
                return ResponseBuilder::error(400, "Tidak dapat menghapus mata pelajaran yang masih memiliki capaian pembelajaran");
            }
            
            $mapel->delete();
            return ResponseBuilder::success(200, "Berhasil Menghapus Data", null, true);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal Menghapus Data: " . $e->getMessage());
        }
    }
    
    public function getCapaianPembelajaran($id)
    {
        $mapel = MataPelajaran::find($id);
        
        if (!$mapel) {
            return ResponseBuilder::error(404, "Data Mata Pelajaran Tidak ada");
        }
        
        $cp = CapaianPembelajaran::where('mapel_id', $id)
                               ->orderBy('created_at', 'desc')
                               ->get();
        
        return ResponseBuilder::success(200, "Berhasil Mendapatkan Data Capaian Pembelajaran", $cp, true);
    }
} 