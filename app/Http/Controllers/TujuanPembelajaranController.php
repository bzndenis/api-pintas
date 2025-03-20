<?php

namespace App\Http\Controllers;

use App\Models\TujuanPembelajaran;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\DB;

class TujuanPembelajaranController extends Controller
{
    public function __construct()
    {
        $this->middleware("login");
    }

    public function index(Request $request)
    {
        $cpId = $request->query('cp_id');
        $sekolahId = $request->query('sekolah_id');
        
        $query = TujuanPembelajaran::with(['capaianPembelajaran.mataPelajaran', 'sekolah']);
        
        if ($cpId) {
            $query->where('cp_id', $cpId);
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
            'kode_tp' => 'required|string|max:50',
            'deskripsi' => 'required|string',
            'bobot' => 'required|numeric|min:0',
            'cp_id' => 'required|exists:capaian_pembelajaran,id',
            'sekolah_id' => 'required|exists:sekolah,id'
        ]);

        try {
            DB::beginTransaction();
            
            // Cek apakah kode TP sudah ada untuk CP yang sama
            $exists = TujuanPembelajaran::where('kode_tp', $request->kode_tp)
                                       ->where('cp_id', $request->cp_id)
                                       ->exists();
            
            if ($exists) {
                return ResponseBuilder::error(400, "Kode TP sudah digunakan untuk capaian pembelajaran ini");
            }
            
            $tp = TujuanPembelajaran::create($request->all());
            
            DB::commit();
            
            $tp->load(['capaianPembelajaran.mataPelajaran', 'sekolah']);
            
            return ResponseBuilder::success(201, "Berhasil Menambahkan Data", $tp, true);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal Menambahkan Data: " . $e->getMessage());
        }
    }

    public function show($id)
    {
        $tp = TujuanPembelajaran::with(['capaianPembelajaran.mataPelajaran', 'sekolah'])->find($id);
        
        if (!$tp) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        return ResponseBuilder::success(200, "Berhasil Mendapatkan Data", $tp, true);
    }

    public function update(Request $request, $id)
    {
        $tp = TujuanPembelajaran::find($id);
        
        if (!$tp) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        $this->validate($request, [
            'kode_tp' => 'sometimes|required|string|max:50',
            'deskripsi' => 'sometimes|required|string',
            'bobot' => 'sometimes|required|numeric|min:0',
            'cp_id' => 'sometimes|required|exists:capaian_pembelajaran,id',
            'sekolah_id' => 'sometimes|required|exists:sekolah,id'
        ]);
        
        try {
            DB::beginTransaction();
            
            // Cek apakah kode TP sudah ada untuk CP yang sama (kecuali diri sendiri)
            if (($request->has('kode_tp') && $request->kode_tp != $tp->kode_tp) || 
                ($request->has('cp_id') && $request->cp_id != $tp->cp_id)) {
                
                $exists = TujuanPembelajaran::where('kode_tp', $request->kode_tp ?? $tp->kode_tp)
                                           ->where('cp_id', $request->cp_id ?? $tp->cp_id)
                                           ->where('id', '!=', $id)
                                           ->exists();
                
                if ($exists) {
                    return ResponseBuilder::error(400, "Kode TP sudah digunakan untuk capaian pembelajaran ini");
                }
            }
            
            $tp->update($request->all());
            
            DB::commit();
            
            $tp->load(['capaianPembelajaran.mataPelajaran', 'sekolah']);
            
            return ResponseBuilder::success(200, "Berhasil Mengubah Data", $tp, true);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal Mengubah Data: " . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $tp = TujuanPembelajaran::find($id);
        
        if (!$tp) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        try {
            // Cek apakah TP masih memiliki nilai siswa
            if ($tp->nilaiSiswa()->count() > 0) {
                return ResponseBuilder::error(400, "Tidak dapat menghapus tujuan pembelajaran yang masih memiliki nilai siswa");
            }
            
            $tp->delete();
            return ResponseBuilder::success(200, "Berhasil Menghapus Data", null, true);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal Menghapus Data: " . $e->getMessage());
        }
    }
} 