<?php

namespace App\Http\Controllers;

use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TahunAjaranController extends Controller
{
    public function __construct()
    {
        $this->middleware("login");
    }

    public function index(Request $request)
    {
        try {
            $query = TahunAjaran::query();
            
            if ($request->has('active')) {
                $query->where('is_active', $request->active);
            }

            $tahunAjaran = $query->orderBy('tahun_mulai', 'desc')->get();

            return ResponseBuilder::success(200, "Berhasil mendapatkan data", $tahunAjaran);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mengambil data: " . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $tahunAjaran = TahunAjaran::create([
                'tahun_mulai' => $request->tahun_mulai,
                'tahun_selesai' => $request->tahun_selesai,
                'semester' => $request->semester,
                'is_active' => $request->is_active ?? false
            ]);

            if ($request->is_active) {
                // Nonaktifkan tahun ajaran lain
                TahunAjaran::where('id', '!=', $tahunAjaran->id)
                    ->update(['is_active' => false]);
            }

            DB::commit();
            return ResponseBuilder::success(200, "Berhasil menambah data", $tahunAjaran);
        } catch (\Exception $e) {
            DB::rollback();
            return ResponseBuilder::error(500, "Gagal menambah data: " . $e->getMessage());
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
        try {
            DB::beginTransaction();

            $tahunAjaran = TahunAjaran::find($id);
            if (!$tahunAjaran) {
                return ResponseBuilder::error(404, "Data tidak ditemukan");
            }

            // Nonaktifkan semua tahun ajaran
            TahunAjaran::query()->update(['is_active' => false]);

            // Aktifkan tahun ajaran yang dipilih
            $tahunAjaran->update(['is_active' => true]);

            DB::commit();
            return ResponseBuilder::success(200, "Berhasil mengaktifkan tahun ajaran", $tahunAjaran);
        } catch (\Exception $e) {
            DB::rollback();
            return ResponseBuilder::error(500, "Gagal mengaktifkan: " . $e->getMessage());
        }
    }
} 