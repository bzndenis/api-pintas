<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\DB;

class KelasController extends Controller
{
    public function __construct()
    {
        $this->middleware("login");
    }

    public function index(Request $request)
    {
        $sekolahId = $request->query('sekolah_id');
        $tahunAjaranId = $request->query('tahun_ajaran_id');
        $guruId = $request->query('guru_id');
        
        $query = Kelas::with(['tahunAjaran', 'guru', 'sekolah']);
        
        if ($sekolahId) {
            $query->where('sekolah_id', $sekolahId);
        }
        
        if ($tahunAjaranId) {
            $query->where('tahun_ajaran_id', $tahunAjaranId);
        }
        
        if ($guruId) {
            $query->where('guru_id', $guruId);
        }
        
        $data = $query->orderBy('created_at', 'desc')->get();
        
        return ResponseBuilder::success(200, "Berhasil Mendapatkan Data", $data, true, false);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'nama_kelas' => 'required|string|max:255',
            'tingkat' => 'required|string|max:20',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'guru_id' => 'required|exists:guru,id',
            'sekolah_id' => 'required|exists:sekolah,id'
        ]);

        try {
            DB::beginTransaction();
            
            $kelas = Kelas::create($request->all());
            
            DB::commit();
            
            $kelas->load(['tahunAjaran', 'guru', 'sekolah']);
            
            return ResponseBuilder::success(201, "Berhasil Menambahkan Data", $kelas, true);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal Menambahkan Data: " . $e->getMessage());
        }
    }

    public function show($id)
    {
        $kelas = Kelas::with(['tahunAjaran', 'guru', 'sekolah'])->find($id);
        
        if (!$kelas) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        return ResponseBuilder::success(200, "Berhasil Mendapatkan Data", $kelas, true);
    }

    public function update(Request $request, $id)
    {
        $kelas = Kelas::find($id);
        
        if (!$kelas) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        $this->validate($request, [
            'nama_kelas' => 'sometimes|required|string|max:255',
            'tingkat' => 'sometimes|required|string|max:20',
            'tahun_ajaran_id' => 'sometimes|required|exists:tahun_ajaran,id',
            'guru_id' => 'sometimes|required|exists:guru,id',
            'sekolah_id' => 'sometimes|required|exists:sekolah,id'
        ]);
        
        try {
            DB::beginTransaction();
            
            $kelas->update($request->all());
            
            DB::commit();
            
            $kelas->load(['tahunAjaran', 'guru', 'sekolah']);
            
            return ResponseBuilder::success(200, "Berhasil Mengubah Data", $kelas, true);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal Mengubah Data: " . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $kelas = Kelas::find($id);
        
        if (!$kelas) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        try {
            // Cek apakah kelas masih memiliki siswa
            if ($kelas->siswa()->count() > 0) {
                return ResponseBuilder::error(400, "Tidak dapat menghapus kelas yang masih memiliki siswa");
            }
            
            $kelas->delete();
            return ResponseBuilder::success(200, "Berhasil Menghapus Data", null, true);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal Menghapus Data: " . $e->getMessage());
        }
    }
    
    public function getSiswa($id)
    {
        $kelas = Kelas::find($id);
        
        if (!$kelas) {
            return ResponseBuilder::error(404, "Data Kelas Tidak ada");
        }
        
        $siswa = Siswa::where('kelas_id', $id)
                      ->orderBy('nama', 'asc')
                      ->get();
        
        return ResponseBuilder::success(200, "Berhasil Mendapatkan Data Siswa", $siswa, true);
    }
} 