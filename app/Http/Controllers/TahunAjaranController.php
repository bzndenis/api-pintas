<?php

namespace App\Http\Controllers;

use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\DB;

class TahunAjaranController extends Controller
{
    public function __construct()
    {
        $this->middleware("login");
    }

    public function index(Request $request)
    {
        $sekolahId = $request->query('sekolah_id');
        $isActive = $request->query('is_active');
        
        $query = TahunAjaran::with('sekolah');
        
        if ($sekolahId) {
            $query->where('sekolah_id', $sekolahId);
        }
        
        if ($isActive !== null) {
            $query->where('is_active', $isActive == 'true' || $isActive == '1');
        }
        
        $data = $query->orderBy('created_at', 'desc')->get();
        
        return ResponseBuilder::success(200, "Berhasil Mendapatkan Data", $data, true, false);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'nama_tahun_ajaran' => 'required|string|max:255',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'sekolah_id' => 'required|exists:sekolah,id',
            'is_active' => 'nullable|boolean'
        ]);

        try {
            DB::beginTransaction();
            
            // Jika tahun ajaran baru diaktifkan, nonaktifkan tahun ajaran yang lain
            if ($request->input('is_active', false)) {
                TahunAjaran::where('sekolah_id', $request->sekolah_id)
                          ->where('is_active', true)
                          ->update(['is_active' => false]);
            }
            
            $tahunAjaran = TahunAjaran::create($request->all());
            
            DB::commit();
            
            return ResponseBuilder::success(201, "Berhasil Menambahkan Data", $tahunAjaran, true);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal Menambahkan Data: " . $e->getMessage());
        }
    }

    public function show($id)
    {
        $tahunAjaran = TahunAjaran::with('sekolah')->find($id);
        
        if (!$tahunAjaran) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        return ResponseBuilder::success(200, "Berhasil Mendapatkan Data", $tahunAjaran, true);
    }

    public function update(Request $request, $id)
    {
        $tahunAjaran = TahunAjaran::find($id);
        
        if (!$tahunAjaran) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        $this->validate($request, [
            'nama_tahun_ajaran' => 'sometimes|required|string|max:255',
            'tanggal_mulai' => 'sometimes|required|date',
            'tanggal_selesai' => 'sometimes|required|date|after:tanggal_mulai',
            'sekolah_id' => 'sometimes|required|exists:sekolah,id',
            'is_active' => 'nullable|boolean'
        ]);
        
        try {
            DB::beginTransaction();
            
            // Jika tahun ajaran diaktifkan, nonaktifkan tahun ajaran yang lain
            if ($request->has('is_active') && $request->is_active) {
                TahunAjaran::where('sekolah_id', $tahunAjaran->sekolah_id)
                          ->where('id', '!=', $id)
                          ->where('is_active', true)
                          ->update(['is_active' => false]);
            }
            
            $tahunAjaran->update($request->all());
            
            DB::commit();
            
            return ResponseBuilder::success(200, "Berhasil Mengubah Data", $tahunAjaran, true);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal Mengubah Data: " . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $tahunAjaran = TahunAjaran::find($id);
        
        if (!$tahunAjaran) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        try {
            // Cek apakah tahun ajaran masih memiliki kelas
            if ($tahunAjaran->kelas()->count() > 0) {
                return ResponseBuilder::error(400, "Tidak dapat menghapus tahun ajaran yang masih memiliki kelas");
            }
            
            $tahunAjaran->delete();
            return ResponseBuilder::success(200, "Berhasil Menghapus Data", null, true);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal Menghapus Data: " . $e->getMessage());
        }
    }
    
    public function activate($id)
    {
        $tahunAjaran = TahunAjaran::find($id);
        
        if (!$tahunAjaran) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        try {
            DB::beginTransaction();
            
            // Nonaktifkan semua tahun ajaran pada sekolah yang sama
            TahunAjaran::where('sekolah_id', $tahunAjaran->sekolah_id)
                      ->where('is_active', true)
                      ->update(['is_active' => false]);
            
            // Aktifkan tahun ajaran yang dipilih
            $tahunAjaran->update(['is_active' => true]);
            
            DB::commit();
            
            return ResponseBuilder::success(200, "Berhasil Mengaktifkan Tahun Ajaran", $tahunAjaran, true);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal Mengaktifkan Tahun Ajaran: " . $e->getMessage());
        }
    }
} 