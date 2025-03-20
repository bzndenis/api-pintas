<?php

namespace App\Http\Controllers;

use App\Models\PertemuanBulanan;
use App\Models\Kelas;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PertemuanBulananController extends Controller
{
    public function __construct()
    {
        $this->middleware("login");
    }

    public function index(Request $request)
    {
        $kelasId = $request->query('kelas_id');
        $bulan = $request->query('bulan');
        $tahun = $request->query('tahun');
        $sekolahId = $request->query('sekolah_id');
        
        $query = PertemuanBulanan::with(['kelas.tahunAjaran', 'createdBy', 'sekolah']);
        
        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }
        
        if ($bulan) {
            $query->where('bulan', $bulan);
        }
        
        if ($tahun) {
            $query->where('tahun', $tahun);
        }
        
        if ($sekolahId) {
            $query->where('sekolah_id', $sekolahId);
        }
        
        $data = $query->orderBy('tahun', 'desc')
                      ->orderBy('bulan', 'desc')
                      ->get();
        
        return ResponseBuilder::success(200, "Berhasil Mendapatkan Data", $data, true, false);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'kelas_id' => 'required|exists:kelas,id',
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000|max:2100',
            'total_pertemuan' => 'required|integer|min:1'
        ]);

        try {
            DB::beginTransaction();
            
            // Cek apakah pertemuan pada bulan dan tahun yang sama sudah ada
            $exists = PertemuanBulanan::where('kelas_id', $request->kelas_id)
                                    ->where('bulan', $request->bulan)
                                    ->where('tahun', $request->tahun)
                                    ->exists();
            
            if ($exists) {
                return ResponseBuilder::error(400, "Pertemuan untuk bulan dan tahun ini sudah ada");
            }
            
            // Dapatkan data kelas
            $kelas = Kelas::find($request->kelas_id);
            
            if (!$kelas) {
                return ResponseBuilder::error(404, "Data Kelas Tidak ada");
            }
            
            // Buat pertemuan bulanan
            $pertemuan = PertemuanBulanan::create([
                'kelas_id' => $request->kelas_id,
                'bulan' => $request->bulan,
                'tahun' => $request->tahun,
                'total_pertemuan' => $request->total_pertemuan,
                'created_by' => Auth::id(),
                'sekolah_id' => $kelas->sekolah_id
            ]);
            
            DB::commit();
            
            $pertemuan->load(['kelas.tahunAjaran', 'createdBy', 'sekolah']);
            
            return ResponseBuilder::success(201, "Berhasil Menambahkan Data", $pertemuan, true);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal Menambahkan Data: " . $e->getMessage());
        }
    }

    public function show($id)
    {
        $pertemuan = PertemuanBulanan::with(['kelas.tahunAjaran', 'createdBy', 'sekolah'])->find($id);
        
        if (!$pertemuan) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        return ResponseBuilder::success(200, "Berhasil Mendapatkan Data", $pertemuan, true);
    }

    public function update(Request $request, $id)
    {
        $pertemuan = PertemuanBulanan::find($id);
        
        if (!$pertemuan) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        $this->validate($request, [
            'kelas_id' => 'sometimes|required|exists:kelas,id',
            'bulan' => 'sometimes|required|integer|min:1|max:12',
            'tahun' => 'sometimes|required|integer|min:2000|max:2100',
            'total_pertemuan' => 'sometimes|required|integer|min:1'
        ]);
        
        try {
            DB::beginTransaction();
            
            // Cek apakah pertemuan pada bulan dan tahun yang sama sudah ada (kecuali diri sendiri)
            if (($request->has('bulan') && $request->bulan != $pertemuan->bulan) || 
                ($request->has('tahun') && $request->tahun != $pertemuan->tahun) || 
                ($request->has('kelas_id') && $request->kelas_id != $pertemuan->kelas_id)) {
                
                $exists = PertemuanBulanan::where('kelas_id', $request->kelas_id ?? $pertemuan->kelas_id)
                                        ->where('bulan', $request->bulan ?? $pertemuan->bulan)
                                        ->where('tahun', $request->tahun ?? $pertemuan->tahun)
                                        ->where('id', '!=', $id)
                                        ->exists();
                
                if ($exists) {
                    return ResponseBuilder::error(400, "Pertemuan untuk bulan dan tahun ini sudah ada");
                }
            }
            
            // Update pertemuan
            $pertemuan->update($request->only(['kelas_id', 'bulan', 'tahun', 'total_pertemuan']));
            
            DB::commit();
            
            $pertemuan->load(['kelas.tahunAjaran', 'createdBy', 'sekolah']);
            
            return ResponseBuilder::success(200, "Berhasil Mengubah Data", $pertemuan, true);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal Mengubah Data: " . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $pertemuan = PertemuanBulanan::find($id);
        
        if (!$pertemuan) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        try {
            // Cek apakah pertemuan masih memiliki absensi
            if ($pertemuan->absensiSiswa()->count() > 0) {
                return ResponseBuilder::error(400, "Tidak dapat menghapus pertemuan yang masih memiliki data absensi");
            }
            
            $pertemuan->delete();
            return ResponseBuilder::success(200, "Berhasil Menghapus Data", null, true);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal Menghapus Data: " . $e->getMessage());
        }
    }
} 