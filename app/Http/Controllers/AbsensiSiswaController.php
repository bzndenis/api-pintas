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
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class AbsensiSiswaController extends Controller
{
    public function __construct()
    {
        $this->middleware("login");
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $sekolahId = $user->sekolah_id;
        
        $query = AbsensiSiswa::with(['siswa', 'pertemuan'])
            ->where('sekolah_id', $sekolahId);
            
        // Filter berdasarkan pertemuan_id jika ada
        if ($request->has('pertemuan_id')) {
            $query->where('pertemuan_id', $request->pertemuan_id);
        }
        
        // Filter berdasarkan siswa_id jika ada
        if ($request->has('siswa_id')) {
            $query->where('siswa_id', $request->siswa_id);
        }
        
        $absensi = $query->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $absensi
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $sekolahId = $user->sekolah_id;
        
        $validator = Validator::make($request->all(), [
            'siswa_id' => 'required|exists:siswa,id',
            'pertemuan_id' => 'required|exists:pertemuan_bulanan,id',
            'hadir' => 'required|integer|min:0',
            'izin' => 'required|integer|min:0',
            'sakit' => 'required|integer|min:0',
            'absen' => 'required|integer|min:0',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }
        
        // Cek apakah siswa dan pertemuan berada di sekolah yang sama dengan user
        $siswa = Siswa::find($request->siswa_id);
        $pertemuan = PertemuanBulanan::find($request->pertemuan_id);
        
        if ($siswa->sekolah_id != $sekolahId || $pertemuan->sekolah_id != $sekolahId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Siswa atau pertemuan tidak ditemukan di sekolah ini'
            ], 403);
        }
        
        // Cek apakah sudah ada absensi untuk siswa dan pertemuan ini
        $existingAbsensi = AbsensiSiswa::where('siswa_id', $request->siswa_id)
            ->where('pertemuan_id', $request->pertemuan_id)
            ->where('sekolah_id', $sekolahId)
            ->first();
            
        if ($existingAbsensi) {
            return response()->json([
                'status' => 'error',
                'message' => 'Absensi untuk siswa dan pertemuan ini sudah ada'
            ], 400);
        }
        
        $absensi = new AbsensiSiswa();
        $absensi->siswa_id = $request->siswa_id;
        $absensi->pertemuan_id = $request->pertemuan_id;
        $absensi->hadir = $request->hadir;
        $absensi->izin = $request->izin;
        $absensi->sakit = $request->sakit;
        $absensi->absen = $request->absen;
        $absensi->created_by = $user->id;
        $absensi->sekolah_id = $sekolahId;
        $absensi->save();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Absensi berhasil disimpan',
            'data' => $absensi
        ], 201);
    }

    public function show($id)
    {
        $user = Auth::user();
        $sekolahId = $user->sekolah_id;
        
        $absensi = AbsensiSiswa::with(['siswa', 'pertemuan'])
            ->where('id', $id)
            ->where('sekolah_id', $sekolahId)
            ->first();
            
        if (!$absensi) {
            return response()->json([
                'status' => 'error',
                'message' => 'Absensi tidak ditemukan'
            ], 404);
        }
        
        return response()->json([
            'status' => 'success',
            'data' => $absensi
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $sekolahId = $user->sekolah_id;
        
        $validator = Validator::make($request->all(), [
            'hadir' => 'required|integer|min:0',
            'izin' => 'required|integer|min:0',
            'sakit' => 'required|integer|min:0',
            'absen' => 'required|integer|min:0',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }
        
        $absensi = AbsensiSiswa::where('id', $id)
            ->where('sekolah_id', $sekolahId)
            ->first();
            
        if (!$absensi) {
            return response()->json([
                'status' => 'error',
                'message' => 'Absensi tidak ditemukan'
            ], 404);
        }
        
        $absensi->hadir = $request->hadir;
        $absensi->izin = $request->izin;
        $absensi->sakit = $request->sakit;
        $absensi->absen = $request->absen;
        $absensi->save();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Absensi berhasil diperbarui',
            'data' => $absensi
        ]);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $sekolahId = $user->sekolah_id;
        
        $absensi = AbsensiSiswa::where('id', $id)
            ->where('sekolah_id', $sekolahId)
            ->first();
            
        if (!$absensi) {
            return response()->json([
                'status' => 'error',
                'message' => 'Absensi tidak ditemukan'
            ], 404);
        }
        
        $absensi->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Absensi berhasil dihapus'
        ]);
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

    public function getRekapAbsensi(Request $request)
    {
        try {
            $kelasId = $request->kelas_id;
            $bulan = $request->bulan;
            $tahun = $request->tahun;
            
            $kelas = Kelas::with('siswa')->find($kelasId);
            
            if (!$kelas) {
                return ResponseBuilder::error(404, "Data kelas tidak ditemukan");
            }

            $pertemuan = PertemuanBulanan::where('kelas_id', $kelasId)
                ->where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->first();

            if (!$pertemuan) {
                return ResponseBuilder::error(404, "Data pertemuan tidak ditemukan");
            }

            $report = [];
            foreach ($kelas->siswa as $siswa) {
                $absensi = AbsensiSiswa::where('siswa_id', $siswa->id)
                    ->where('pertemuan_id', $pertemuan->id)
                    ->get();

                $hadir = $absensi->where('status', 'hadir')->count();
                $izin = $absensi->where('status', 'izin')->count();
                $sakit = $absensi->where('status', 'sakit')->count();
                $alpha = $absensi->where('status', 'alpha')->count();
                $total = $absensi->count();

                $report[] = [
                    'siswa' => [
                        'id' => $siswa->id,
                        'nama' => $siswa->nama,
                        'nis' => $siswa->nis
                    ],
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

            return ResponseBuilder::success(200, "Berhasil mendapatkan rekap absensi", [
                'kelas' => [
                    'id' => $kelas->id,
                    'nama_kelas' => $kelas->nama_kelas
                ],
                'pertemuan' => [
                    'id' => $pertemuan->id,
                    'bulan' => $pertemuan->bulan,
                    'tahun' => $pertemuan->tahun,
                    'total_pertemuan' => $pertemuan->total_pertemuan
                ],
                'report' => $report
            ]);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mengambil rekap: " . $e->getMessage());
        }
    }

    public function getByPertemuan($pertemuanId)
    {
        $user = Auth::user();
        $sekolahId = $user->sekolah_id;
        
        $pertemuan = PertemuanBulanan::where('id', $pertemuanId)
            ->where('sekolah_id', $sekolahId)
            ->first();
            
        if (!$pertemuan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pertemuan tidak ditemukan'
            ], 404);
        }
        
        $absensi = AbsensiSiswa::with(['siswa'])
            ->where('pertemuan_id', $pertemuanId)
            ->where('sekolah_id', $sekolahId)
            ->get();
            
        return response()->json([
            'status' => 'success',
            'data' => [
                'pertemuan' => $pertemuan,
                'absensi' => $absensi
            ]
        ]);
    }
    
    public function getBySiswa($siswaId)
    {
        $user = Auth::user();
        $sekolahId = $user->sekolah_id;
        
        $siswa = Siswa::where('id', $siswaId)
            ->where('sekolah_id', $sekolahId)
            ->first();
            
        if (!$siswa) {
            return response()->json([
                'status' => 'error',
                'message' => 'Siswa tidak ditemukan'
            ], 404);
        }
        
        $absensi = AbsensiSiswa::with(['pertemuan'])
            ->where('siswa_id', $siswaId)
            ->where('sekolah_id', $sekolahId)
            ->get();
            
        return response()->json([
            'status' => 'success',
            'data' => [
                'siswa' => $siswa,
                'absensi' => $absensi
            ]
        ]);
    }
} 