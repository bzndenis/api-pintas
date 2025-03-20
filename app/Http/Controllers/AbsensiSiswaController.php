<?php

namespace App\Http\Controllers;

use App\Models\AbsensiSiswa;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\PertemuanBulanan;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AbsensiSiswaController extends Controller
{
    public function __construct()
    {
        $this->middleware("login");
    }

    public function index(Request $request)
    {
        $siswaId = $request->query('siswa_id');
        $pertemuanId = $request->query('pertemuan_id');
        $kelasId = $request->query('kelas_id');
        $sekolahId = $request->query('sekolah_id');
        
        $query = AbsensiSiswa::with(['siswa', 'pertemuan.kelas', 'createdBy', 'sekolah']);
        
        if ($siswaId) {
            $query->where('siswa_id', $siswaId);
        }
        
        if ($pertemuanId) {
            $query->where('pertemuan_id', $pertemuanId);
        }
        
        if ($kelasId) {
            $query->whereHas('pertemuan', function ($q) use ($kelasId) {
                $q->where('kelas_id', $kelasId);
            });
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
            'siswa_id' => 'required|exists:siswa,id',
            'pertemuan_id' => 'required|exists:pertemuan_bulanan,id',
            'hadir' => 'required|integer|min:0|max:1',
            'izin' => 'required|integer|min:0|max:1',
            'sakit' => 'required|integer|min:0|max:1',
            'absen' => 'required|integer|min:0|max:1',
            'keterangan' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();
            
            // Cek apakah status kehadiran valid (hanya satu yang boleh bernilai 1)
            $totalStatus = $request->hadir + $request->izin + $request->sakit + $request->absen;
            
            if ($totalStatus != 1) {
                return ResponseBuilder::error(400, "Status kehadiran tidak valid, hanya satu status yang boleh dipilih");
            }
            
            // Cek apakah siswa sudah memiliki absensi pada pertemuan ini
            $exists = AbsensiSiswa::where('siswa_id', $request->siswa_id)
                                ->where('pertemuan_id', $request->pertemuan_id)
                                ->exists();
            
            if ($exists) {
                return ResponseBuilder::error(400, "Siswa sudah memiliki absensi pada pertemuan ini");
            }
            
            // Dapatkan data siswa
            $siswa = Siswa::find($request->siswa_id);
            
            if (!$siswa) {
                return ResponseBuilder::error(404, "Data Siswa Tidak ada");
            }
            
            // Buat absensi
            $absensi = AbsensiSiswa::create([
                'siswa_id' => $request->siswa_id,
                'pertemuan_id' => $request->pertemuan_id,
                'hadir' => $request->hadir,
                'izin' => $request->izin,
                'sakit' => $request->sakit,
                'absen' => $request->absen,
                'keterangan' => $request->keterangan,
                'created_by' => Auth::id(),
                'sekolah_id' => $siswa->sekolah_id
            ]);
            
            DB::commit();
            
            $absensi->load(['siswa', 'pertemuan.kelas', 'createdBy', 'sekolah']);
            
            return ResponseBuilder::success(201, "Berhasil Menambahkan Data", $absensi, true);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal Menambahkan Data: " . $e->getMessage());
        }
    }

    public function show($id)
    {
        $absensi = AbsensiSiswa::with(['siswa', 'pertemuan.kelas', 'createdBy', 'sekolah'])->find($id);
        
        if (!$absensi) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        return ResponseBuilder::success(200, "Berhasil Mendapatkan Data", $absensi, true);
    }

    public function update(Request $request, $id)
    {
        $absensi = AbsensiSiswa::find($id);
        
        if (!$absensi) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        $this->validate($request, [
            'hadir' => 'sometimes|required|integer|min:0|max:1',
            'izin' => 'sometimes|required|integer|min:0|max:1',
            'sakit' => 'sometimes|required|integer|min:0|max:1',
            'absen' => 'sometimes|required|integer|min:0|max:1',
            'keterangan' => 'nullable|string'
        ]);
        
        try {
            DB::beginTransaction();
            
            // Cek apakah status kehadiran valid (hanya satu yang boleh bernilai 1)
            $hadir = $request->has('hadir') ? $request->hadir : $absensi->hadir;
            $izin = $request->has('izin') ? $request->izin : $absensi->izin;
            $sakit = $request->has('sakit') ? $request->sakit : $absensi->sakit;
            $absen = $request->has('absen') ? $request->absen : $absensi->absen;
            
            $totalStatus = $hadir + $izin + $sakit + $absen;
            
            if ($totalStatus != 1) {
                return ResponseBuilder::error(400, "Status kehadiran tidak valid, hanya satu status yang boleh dipilih");
            }
            
            // Update absensi
            $absensi->update([
                'hadir' => $hadir,
                'izin' => $izin,
                'sakit' => $sakit,
                'absen' => $absen,
                'keterangan' => $request->keterangan
            ]);
            
            DB::commit();
            
            $absensi->load(['siswa', 'pertemuan.kelas', 'createdBy', 'sekolah']);
            
            return ResponseBuilder::success(200, "Berhasil Mengubah Data", $absensi, true);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal Mengubah Data: " . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $absensi = AbsensiSiswa::find($id);
        
        if (!$absensi) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        try {
            $absensi->delete();
            return ResponseBuilder::success(200, "Berhasil Menghapus Data", null, true);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal Menghapus Data: " . $e->getMessage());
        }
    }
    
    public function import(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|file|mimes:csv,txt,xls,xlsx|max:2048',
            'pertemuan_id' => 'required|exists:pertemuan_bulanan,id'
        ]);
        
        try {
            $file = $request->file('file');
            
            // Baca file excel atau csv
            // Di sini perlu implementasi untuk membaca file dan menyimpan data absensi
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
    
    public function reportBySiswa($siswaId)
    {
        $siswa = Siswa::with('kelas')->find($siswaId);
        
        if (!$siswa) {
            return ResponseBuilder::error(404, "Data Siswa Tidak ada");
        }
        
        $absensi = AbsensiSiswa::where('siswa_id', $siswaId)
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
    
    public function reportByKelas($kelasId)
    {
        $kelas = Kelas::with(['tahunAjaran', 'guru', 'sekolah'])->find($kelasId);
        
        if (!$kelas) {
            return ResponseBuilder::error(404, "Data Kelas Tidak ada");
        }
        
        $siswa = Siswa::where('kelas_id', $kelasId)
                      ->orderBy('nama', 'asc')
                      ->get();
        
        $siswaIds = $siswa->pluck('id')->toArray();
        
        $absensi = AbsensiSiswa::whereIn('siswa_id', $siswaIds)
                             ->with(['pertemuan'])
                             ->get();
        
        // Kelompokkan absensi berdasarkan siswa
        $report = [];
        
        foreach ($siswa as $s) {
            $absensiSiswa = $absensi->where('siswa_id', $s->id);
            
            // Hitung rekap kehadiran
            $hadir = $absensiSiswa->sum('hadir');
            $izin = $absensiSiswa->sum('izin');
            $sakit = $absensiSiswa->sum('sakit');
            $alpha = $absensiSiswa->sum('absen');
            $total = $hadir + $izin + $sakit + $alpha;
            
            $report[] = [
                'siswa' => $s,
                'absensi' => $absensiSiswa->values(),
                'rekap' => [
                    'hadir' => $hadir,
                    'izin' => $izin,
                    'sakit' => $sakit,
                    'alpha' => $alpha,
                    'total' => $total,
                    'persentase_kehadiran' => $total > 0 ? round(($hadir / $total) * 100, 2) : 0
                ]
            ];
        }
        
        $response = [
            'kelas' => $kelas,
            'report' => $report
        ];
        
        return ResponseBuilder::success(200, "Berhasil Mendapatkan Data Absensi", $response, true);
    }
} 