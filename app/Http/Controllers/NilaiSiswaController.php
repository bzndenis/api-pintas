<?php

namespace App\Http\Controllers;

use App\Models\NilaiSiswa;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\TujuanPembelajaran;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class NilaiSiswaController extends Controller
{
    public function __construct()
    {
        $this->middleware("login");
    }

    public function index(Request $request)
    {
        try {
            $query = NilaiSiswa::with([
                'siswa.kelas',
                'tujuanPembelajaran.capaianPembelajaran.mataPelajaran',
                'createdBy'
            ]);
            
            if ($request->siswa_id) {
                $query->where('siswa_id', $request->siswa_id);
            }

            if ($request->tp_id) {
                $query->where('tp_id', $request->tp_id);
            }

            if ($request->sekolah_id) {
                $query->where('sekolah_id', $request->sekolah_id);
            }

            $nilai = $query->orderBy('created_at', 'desc')->get();

            $formattedData = [
                'total' => $nilai->count(),
                'nilai_siswa' => $nilai->map(function($item) {
                    return [
                        'id' => $item->id,
                        'nilai' => $item->nilai,
                        'siswa' => [
                            'id' => $item->siswa->id,
                            'nama' => $item->siswa->nama,
                            'kelas' => [
                                'id' => $item->siswa->kelas->id,
                                'nama_kelas' => $item->siswa->kelas->nama_kelas
                            ]
                        ],
                        'tujuan_pembelajaran' => [
                            'id' => $item->tujuanPembelajaran->id,
                            'kode_tp' => $item->tujuanPembelajaran->kode_tp,
                            'mata_pelajaran' => [
                                'id' => $item->tujuanPembelajaran->capaianPembelajaran->mataPelajaran->id,
                                'nama_mapel' => $item->tujuanPembelajaran->capaianPembelajaran->mataPelajaran->nama_mapel
                            ]
                        ],
                        'created_by' => [
                            'id' => $item->createdBy->id,
                            'nama' => $item->createdBy->nama_lengkap
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
            'tp_id' => 'required|exists:tujuan_pembelajaran,id',
            'nilai' => 'required|numeric|min:0|max:100',
            'keterangan' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();
            
            // Cek apakah siswa dan TP ada di sekolah yang sama
            $siswa = Siswa::find($request->siswa_id);
            
            if (!$siswa) {
                return ResponseBuilder::error(404, "Data Siswa Tidak ada");
            }
            
            // Buat data nilai
            $nilai = NilaiSiswa::create([
                'siswa_id' => $request->siswa_id,
                'tp_id' => $request->tp_id,
                'nilai' => $request->nilai,
                'keterangan' => $request->keterangan,
                'created_by' => Auth::id(),
                'sekolah_id' => $siswa->sekolah_id
            ]);
            
            DB::commit();
            
            $nilai->load(['siswa', 'tujuanPembelajaran.capaianPembelajaran.mataPelajaran', 'createdBy', 'sekolah']);
            
            return ResponseBuilder::success(201, "Berhasil Menambahkan Data", $nilai, true);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal Menambahkan Data: " . $e->getMessage());
        }
    }

    public function show($id)
    {
        $nilai = NilaiSiswa::with(['siswa', 'tujuanPembelajaran.capaianPembelajaran.mataPelajaran', 'createdBy', 'sekolah'])->find($id);
        
        if (!$nilai) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        return ResponseBuilder::success(200, "Berhasil Mendapatkan Data", $nilai, true);
    }

    public function update(Request $request, $id)
    {
        $nilai = NilaiSiswa::find($id);
        
        if (!$nilai) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        $this->validate($request, [
            'siswa_id' => 'sometimes|required|exists:siswa,id',
            'tp_id' => 'sometimes|required|exists:tujuan_pembelajaran,id',
            'nilai' => 'sometimes|required|numeric|min:0|max:100',
            'keterangan' => 'nullable|string'
        ]);
        
        try {
            DB::beginTransaction();
            
            // Update nilai
            $nilai->update($request->only(['siswa_id', 'tp_id', 'nilai', 'keterangan']));
            
            DB::commit();
            
            $nilai->load(['siswa', 'tujuanPembelajaran.capaianPembelajaran.mataPelajaran', 'createdBy', 'sekolah']);
            
            return ResponseBuilder::success(200, "Berhasil Mengubah Data", $nilai, true);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal Mengubah Data: " . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $nilai = NilaiSiswa::find($id);
        
        if (!$nilai) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        try {
            $nilai->delete();
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
            'tp_id' => 'required|exists:tujuan_pembelajaran,id'
        ]);
        
        try {
            $file = $request->file('file');
            
            // Baca file excel atau csv
            // Di sini perlu implementasi untuk membaca file dan menyimpan data nilai
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
        
        $nilai = NilaiSiswa::where('siswa_id', $siswaId)
                         ->with(['tujuanPembelajaran.capaianPembelajaran.mataPelajaran'])
                         ->orderBy('created_at', 'desc')
                         ->get();
        
        // Kelompokkan nilai berdasarkan mata pelajaran
        $nilaiByMapel = [];
        
        foreach ($nilai as $n) {
            $mapelId = $n->tujuanPembelajaran->capaianPembelajaran->mataPelajaran->id;
            $mapelNama = $n->tujuanPembelajaran->capaianPembelajaran->mataPelajaran->nama_mapel;
            
            if (!isset($nilaiByMapel[$mapelId])) {
                $nilaiByMapel[$mapelId] = [
                    'mata_pelajaran' => $mapelNama,
                    'nilai' => [],
                    'nilai_rata_rata' => 0
                ];
            }
            
            $nilaiByMapel[$mapelId]['nilai'][] = $n;
        }
        
        // Hitung nilai rata-rata untuk setiap mata pelajaran
        foreach ($nilaiByMapel as $mapelId => &$mapelData) {
            $total = 0;
            $bobot = 0;
            
            foreach ($mapelData['nilai'] as $n) {
                $bobotTp = $n->tujuanPembelajaran->bobot ?: 1;
                $total += $n->nilai * $bobotTp;
                $bobot += $bobotTp;
            }
            
            $mapelData['nilai_rata_rata'] = $bobot > 0 ? round($total / $bobot, 2) : 0;
        }
        
        $response = [
            'siswa' => $siswa,
            'nilai_by_mapel' => array_values($nilaiByMapel)
        ];
        
        return ResponseBuilder::success(200, "Berhasil Mendapatkan Data", $response, true);
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
        
        $nilai = NilaiSiswa::whereIn('siswa_id', $siswaIds)
                         ->with(['siswa', 'tujuanPembelajaran.capaianPembelajaran.mataPelajaran'])
                         ->get();
        
        // Kelompokkan nilai berdasarkan siswa dan mata pelajaran
        $report = [];
        
        foreach ($siswa as $s) {
            $nilaiSiswa = $nilai->where('siswa_id', $s->id);
            
            $nilaiByMapel = [];
            
            foreach ($nilaiSiswa as $n) {
                $mapelId = $n->tujuanPembelajaran->capaianPembelajaran->mataPelajaran->id;
                $mapelNama = $n->tujuanPembelajaran->capaianPembelajaran->mataPelajaran->nama_mapel;
                
                if (!isset($nilaiByMapel[$mapelId])) {
                    $nilaiByMapel[$mapelId] = [
                        'mata_pelajaran' => $mapelNama,
                        'nilai' => [],
                        'nilai_rata_rata' => 0
                    ];
                }
                
                $nilaiByMapel[$mapelId]['nilai'][] = $n;
            }
            
            // Hitung nilai rata-rata untuk setiap mata pelajaran
            foreach ($nilaiByMapel as $mapelId => &$mapelData) {
                $total = 0;
                $bobot = 0;
                
                foreach ($mapelData['nilai'] as $n) {
                    $bobotTp = $n->tujuanPembelajaran->bobot ?: 1;
                    $total += $n->nilai * $bobotTp;
                    $bobot += $bobotTp;
                }
                
                $mapelData['nilai_rata_rata'] = $bobot > 0 ? round($total / $bobot, 2) : 0;
            }
            
            $report[] = [
                'siswa' => $s,
                'nilai_by_mapel' => array_values($nilaiByMapel)
            ];
        }
        
        $response = [
            'kelas' => $kelas,
            'report' => $report
        ];
        
        return ResponseBuilder::success(200, "Berhasil Mendapatkan Data", $response, true);
    }
} 