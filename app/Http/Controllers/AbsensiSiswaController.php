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

class AbsensiSiswaController extends Controller
{
    public function __construct()
    {
        $this->middleware("login");
    }

    public function index(Request $request)
    {
        try {
            $query = AbsensiSiswa::with([
                'siswa.kelas',
                'pertemuan',
                'sekolah'
            ]);
            
            if ($request->siswa_id) {
                $query->where('siswa_id', $request->siswa_id);
            }

            if ($request->pertemuan_id) {
                $query->where('pertemuan_id', $request->pertemuan_id);
            }

            if ($request->sekolah_id) {
                $query->where('sekolah_id', $request->sekolah_id);
            }

            $absensi = $query->orderBy('created_at', 'desc')->get();

            $formattedData = [
                'total' => $absensi->count(),
                'absensi_siswa' => $absensi->map(function($item) {
                    return [
                        'id' => $item->id,
                        'status' => $item->status,
                        'keterangan' => $item->keterangan,
                        'siswa' => [
                            'id' => $item->siswa->id,
                            'nama' => $item->siswa->nama,
                            'kelas' => [
                                'id' => $item->siswa->kelas->id,
                                'nama_kelas' => $item->siswa->kelas->nama_kelas
                            ]
                        ],
                        'pertemuan' => [
                            'id' => $item->pertemuan->id,
                            'tanggal' => $item->pertemuan->tanggal,
                            'pertemuan_ke' => $item->pertemuan->pertemuan_ke
                        ],
                        'created_at' => $item->created_at->format('Y-m-d H:i:s')
                    ];
                })
            ];

            return ResponseBuilder::success(200, "Berhasil mendapatkan data", $formattedData);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mengambil data: " . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'siswa_id' => 'required|exists:siswa,id',
            'pertemuan_id' => 'required|exists:pertemuan_bulanan,id',
            'status' => 'required|in:hadir,izin,sakit,alpha',
            'keterangan' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();
            
            // Cek apakah sudah ada absensi untuk siswa dan pertemuan ini
            $exists = AbsensiSiswa::where('siswa_id', $request->siswa_id)
                                ->where('pertemuan_id', $request->pertemuan_id)
                                ->exists();
            
            if ($exists) {
                return ResponseBuilder::error(400, "Absensi untuk siswa ini sudah ada");
            }
            
            // Ambil data siswa untuk mendapatkan sekolah_id
            $siswa = Siswa::find($request->siswa_id);
            
            $absensi = AbsensiSiswa::create([
                'siswa_id' => $request->siswa_id,
                'pertemuan_id' => $request->pertemuan_id,
                'status' => $request->status,
                'keterangan' => $request->keterangan,
                'sekolah_id' => $siswa->sekolah_id
            ]);
            
            DB::commit();
            
            $absensi->load(['siswa.kelas', 'pertemuan', 'sekolah']);
            
            return ResponseBuilder::success(201, "Berhasil menambah data", $absensi);
        } catch (\Exception $e) {
            DB::rollback();
            return ResponseBuilder::error(500, "Gagal menambah data: " . $e->getMessage());
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
            return ResponseBuilder::error(404, "Data tidak ditemukan");
        }

        $this->validate($request, [
            'status' => 'required|in:hadir,izin,sakit,alpha',
            'keterangan' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();
            
            $absensi->update($request->only(['status', 'keterangan']));
            
            DB::commit();
            
            $absensi->load(['siswa.kelas', 'pertemuan', 'sekolah']);
            
            return ResponseBuilder::success(200, "Berhasil mengubah data", $absensi);
        } catch (\Exception $e) {
            DB::rollback();
            return ResponseBuilder::error(500, "Gagal mengubah data: " . $e->getMessage());
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
} 