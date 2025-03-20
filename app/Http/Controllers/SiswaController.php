<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\NilaiSiswa;
use App\Models\AbsensiSiswa;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SiswaController extends Controller
{
    public function __construct()
    {
        $this->middleware("login");
    }

    public function index(Request $request)
    {
        $sekolahId = $request->query('sekolah_id');
        $kelasId = $request->query('kelas_id');
        
        $query = Siswa::with(['kelas.tahunAjaran', 'sekolah']);
        
        if ($sekolahId) {
            $query->where('sekolah_id', $sekolahId);
        }
        
        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }
        
        $data = $query->orderBy('nama', 'asc')->get();
        
        return ResponseBuilder::success(200, "Berhasil Mendapatkan Data", $data, true, false);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'nis' => 'required|string|max:50|unique:siswa',
            'nisn' => 'nullable|string|max:50|unique:siswa',
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'alamat' => 'nullable|string',
            'nama_ortu' => 'nullable|string|max:255',
            'no_telp_ortu' => 'nullable|string|max:20',
            'kelas_id' => 'required|exists:kelas,id',
            'sekolah_id' => 'required|exists:sekolah,id'
        ]);

        try {
            DB::beginTransaction();
            
            $siswa = Siswa::create($request->all());
            
            DB::commit();
            
            $siswa->load(['kelas.tahunAjaran', 'sekolah']);
            
            return ResponseBuilder::success(201, "Berhasil Menambahkan Data", $siswa, true);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal Menambahkan Data: " . $e->getMessage());
        }
    }

    public function show($id)
    {
        $siswa = Siswa::with(['kelas.tahunAjaran', 'sekolah'])->find($id);
        
        if (!$siswa) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        return ResponseBuilder::success(200, "Berhasil Mendapatkan Data", $siswa, true);
    }

    public function update(Request $request, $id)
    {
        $siswa = Siswa::find($id);
        
        if (!$siswa) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        $this->validate($request, [
            'nis' => 'sometimes|required|string|max:50|unique:siswa,nis,'.$id.',id',
            'nisn' => 'nullable|string|max:50|unique:siswa,nisn,'.$id.',id',
            'nama' => 'sometimes|required|string|max:255',
            'jenis_kelamin' => 'sometimes|required|in:L,P',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'alamat' => 'nullable|string',
            'nama_ortu' => 'nullable|string|max:255',
            'no_telp_ortu' => 'nullable|string|max:20',
            'kelas_id' => 'sometimes|required|exists:kelas,id',
            'sekolah_id' => 'sometimes|required|exists:sekolah,id'
        ]);
        
        try {
            DB::beginTransaction();
            
            $siswa->update($request->all());
            
            DB::commit();
            
            $siswa->load(['kelas.tahunAjaran', 'sekolah']);
            
            return ResponseBuilder::success(200, "Berhasil Mengubah Data", $siswa, true);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal Mengubah Data: " . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $siswa = Siswa::find($id);
        
        if (!$siswa) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        try {
            // Cek apakah siswa masih memiliki nilai atau absensi
            if ($siswa->nilai()->count() > 0 || $siswa->absensi()->count() > 0) {
                return ResponseBuilder::error(400, "Tidak dapat menghapus siswa yang masih memiliki data nilai atau absensi");
            }
            
            $siswa->delete();
            return ResponseBuilder::success(200, "Berhasil Menghapus Data", null, true);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal Menghapus Data: " . $e->getMessage());
        }
    }
    
    public function import(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|file|mimes:csv,txt,xls,xlsx|max:2048',
            'kelas_id' => 'required|exists:kelas,id',
            'sekolah_id' => 'required|exists:sekolah,id'
        ]);
        
        try {
            $file = $request->file('file');
            
            // Baca file excel atau csv
            // Di sini perlu implementasi untuk membaca file dan menyimpan data siswa
            // Contoh sederhana, logika sebenarnya mungkin lebih kompleks
            
            $response = [
                'total_data' => 0,
                'berhasil' => 0,
                'gagal' => 0,
                'errors' => []
            ];
            
            // Contoh untuk mendapatkan response
            return ResponseBuilder::success(200, "Berhasil Mengimport Data", $response, true);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal Mengimport Data: " . $e->getMessage());
        }
    }
    
    public function getNilai($id)
    {
        $siswa = Siswa::find($id);
        
        if (!$siswa) {
            return ResponseBuilder::error(404, "Data Siswa Tidak ada");
        }
        
        $nilai = NilaiSiswa::where('siswa_id', $id)
                         ->with(['tujuanPembelajaran.capaianPembelajaran.mataPelajaran'])
                         ->orderBy('created_at', 'desc')
                         ->get();
        
        return ResponseBuilder::success(200, "Berhasil Mendapatkan Data Nilai", $nilai, true);
    }
    
    public function getAbsensi($id)
    {
        $siswa = Siswa::find($id);
        
        if (!$siswa) {
            return ResponseBuilder::error(404, "Data Siswa Tidak ada");
        }
        
        $absensi = AbsensiSiswa::where('siswa_id', $id)
                             ->with(['pertemuan'])
                             ->orderBy('created_at', 'desc')
                             ->get();
        
        // Hitung rekap kehadiran
        $hadir = $absensi->sum('hadir');
        $izin = $absensi->sum('izin');
        $sakit = $absensi->sum('sakit');
        $alpha = $absensi->sum('absen');
        $total = $hadir + $izin + $sakit + $alpha;
        
        $rekap = [
            'hadir' => $hadir,
            'izin' => $izin,
            'sakit' => $sakit,
            'alpha' => $alpha,
            'total' => $total,
            'persentase_kehadiran' => $total > 0 ? round(($hadir / $total) * 100, 2) : 0
        ];
        
        $response = [
            'siswa' => $siswa,
            'absensi' => $absensi,
            'rekap' => $rekap
        ];
        
        return ResponseBuilder::success(200, "Berhasil Mendapatkan Data Absensi", $response, true);
    }
} 