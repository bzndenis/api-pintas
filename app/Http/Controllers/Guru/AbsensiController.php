<?php

namespace App\Http\Controllers\Guru;

use App\Models\AbsensiSiswa;
use App\Models\Pertemuan;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Uuid;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Storage;

class AbsensiController extends BaseGuruController
{
    public function index(Request $request)
    {
        try {
            $guru = Auth::user()->guru;
            
            $query = AbsensiSiswa::with(['siswa.kelas', 'pertemuan.mataPelajaran'])
                ->whereHas('pertemuan', function($q) use ($guru) {
                    $q->whereHas('mataPelajaran', function($q2) use ($guru) {
                        $q2->where('guru_id', $guru->id);
                    });
                });
            
            // Filter berdasarkan kelas
            if ($request->kelas_id) {
                $query->whereHas('siswa', function($q) use ($request) {
                    $q->where('kelas_id', $request->kelas_id);
                });
            }
            
            // Filter berdasarkan tanggal
            if ($request->tanggal) {
                $query->whereHas('pertemuan', function($q) use ($request) {
                    $q->whereDate('tanggal', Carbon::parse($request->tanggal));
                });
            }
            
            $absensi = $query->orderBy('created_at', 'desc')->get();
            
            // Transformasi data untuk frontend
            $transformedAbsensi = $absensi->map(function($item) {
                return [
                    'id' => $item->id,
                    'siswa' => $item->siswa,
                    'pertemuan' => $item->pertemuan,
                    'status' => $this->getStatusAbsensi($item),
                    'created_at' => $item->created_at
                ];
            });
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan data absensi", $transformedAbsensi);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }

    private function getStatusAbsensi($absensi)
    {
        if ($absensi->hadir == 1) return 'hadir';
        if ($absensi->izin == 1) return 'izin';
        if ($absensi->sakit == 1) return 'sakit';
        return 'absen';
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'kelas_id' => 'required|exists:kelas,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'tanggal' => 'required|date',
            'pertemuan_ke' => 'required|integer',
            'materi' => 'required|string',
            'absensi' => 'required|array',
            'absensi.*.siswa_id' => 'required|exists:siswa,id',
            'absensi.*.status' => 'required|in:hadir,izin,sakit,absen'
        ]);

        try {
            DB::beginTransaction();
            
            $guru = Auth::user()->guru;
            
            // Buat pertemuan baru
            $pertemuan = Pertemuan::create([
                'kelas_id' => $request->kelas_id,
                'mata_pelajaran_id' => $request->mata_pelajaran_id,
                'guru_id' => $guru->id,
                'tanggal' => Carbon::parse($request->tanggal),
                'pertemuan_ke' => $request->pertemuan_ke,
                'materi' => $request->materi,
                'sekolah_id' => $guru->sekolah_id
            ]);
            
            // Simpan absensi siswa
            foreach ($request->absensi as $absen) {
                // Inisialisasi nilai default
                $hadir = 0;
                $izin = 0;
                $sakit = 0;
                $absen_val = 0;
                
                // Set nilai berdasarkan status
                switch ($absen['status']) {
                    case 'hadir':
                        $hadir = 1;
                        break;
                    case 'izin':
                        $izin = 1;
                        break;
                    case 'sakit':
                        $sakit = 1;
                        break;
                    case 'absen':
                        $absen_val = 1;
                        break;
                }
                
                AbsensiSiswa::create([
                    'pertemuan_id' => $pertemuan->id,
                    'siswa_id' => $absen['siswa_id'],
                    'hadir' => $hadir,
                    'izin' => $izin,
                    'sakit' => $sakit,
                    'absen' => $absen_val,
                    'keterangan' => $absen['keterangan'] ?? null,
                    'created_by' => Auth::user()->id,
                    'sekolah_id' => $guru->sekolah_id
                ]);
            }
            
            DB::commit();
            
            return ResponseBuilder::success(201, "Berhasil menyimpan absensi");
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal menyimpan absensi: " . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            // Validasi format UUID
            if (!Uuid::isValid($id)) {
                return ResponseBuilder::error(400, "Format ID tidak valid");
            }

            $guru = Auth::user()->guru;
            
            $absensi = AbsensiSiswa::with(['siswa.kelas', 'pertemuan.mataPelajaran'])
                ->whereHas('pertemuan.mataPelajaran', function($q) use ($guru) {
                    $q->where('guru_id', $guru->id);
                })->find($id);
            
            if (!$absensi) {
                return ResponseBuilder::error(404, "Data absensi tidak ditemukan");
            }
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan data absensi", ['absensi' => $absensi]);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Validasi format UUID
            if (!Uuid::isValid($id)) {
                return ResponseBuilder::error(400, "Format ID tidak valid");
            }

            $guru = Auth::user()->guru;
            
            $absensi = AbsensiSiswa::whereHas('pertemuan.mataPelajaran', function($q) use ($guru) {
                $q->where('guru_id', $guru->id);
            })->find($id);
            
            if (!$absensi) {
                return ResponseBuilder::error(404, "Data absensi tidak ditemukan");
            }
            
            // Inisialisasi nilai default
            $hadir = 0;
            $izin = 0;
            $sakit = 0;
            $absen_val = 0;
            
            // Set nilai berdasarkan status
            switch ($request->status) {
                case 'hadir':
                    $hadir = 1;
                    break;
                case 'izin':
                    $izin = 1;
                    break;
                case 'sakit':
                    $sakit = 1;
                    break;
                case 'absen':
                    $absen_val = 1;
                    break;
            }
            
            $absensi->update([
                'hadir' => $hadir,
                'izin' => $izin,
                'sakit' => $sakit,
                'absen' => $absen_val,
                'keterangan' => $request->keterangan ?? null
            ]);
            
            return ResponseBuilder::success(200, "Berhasil mengupdate absensi", ['absensi' => $absensi]);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mengupdate absensi: " . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            // Validasi format UUID
            if (!Uuid::isValid($id)) {
                return ResponseBuilder::error(400, "Format ID tidak valid");
            }

            $guru = Auth::user()->guru;
            
            $absensi = AbsensiSiswa::whereHas('pertemuan.mataPelajaran', function($q) use ($guru) {
                $q->where('guru_id', $guru->id);
            })->find($id);
            
            if (!$absensi) {
                return ResponseBuilder::error(404, "Data absensi tidak ditemukan");
            }
            
            // Hapus data absensi
            $absensi->delete();
            
            return ResponseBuilder::success(200, "Berhasil menghapus data absensi");
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal menghapus data: " . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        try {
            $guru = Auth::user()->guru;
            
            $query = AbsensiSiswa::with(['siswa.kelas', 'pertemuan.mataPelajaran'])
                ->whereHas('pertemuan.mataPelajaran', function($q) use ($guru) {
                    $q->where('guru_id', $guru->id);
                });
            
            // Filter berdasarkan kelas
            if ($request->kelas_id) {
                $query->whereHas('siswa', function($q) use ($request) {
                    $q->where('kelas_id', $request->kelas_id);
                });
            }
            
            // Filter berdasarkan tanggal
            if ($request->start_date && $request->end_date) {
                $query->whereHas('pertemuan', function($q) use ($request) {
                    $q->whereBetween('tanggal', [
                        Carbon::parse($request->start_date),
                        Carbon::parse($request->end_date)
                    ]);
                });
            }
            
            $absensi = $query->orderBy('created_at', 'desc')->get();
            
            // Buat spreadsheet baru
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set header
            $sheet->setCellValue('A1', 'No');
            $sheet->setCellValue('B1', 'Nama Siswa');
            $sheet->setCellValue('C1', 'Kelas');
            $sheet->setCellValue('D1', 'Mata Pelajaran');
            $sheet->setCellValue('E1', 'Tanggal');
            $sheet->setCellValue('F1', 'Pertemuan Ke');
            $sheet->setCellValue('G1', 'Status');
            $sheet->setCellValue('H1', 'Keterangan');
            
            // Isi data
            $row = 2;
            foreach ($absensi as $index => $item) {
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $item->siswa->nama);
                $sheet->setCellValue('C' . $row, $item->siswa->kelas->nama_kelas);
                $sheet->setCellValue('D' . $row, $item->pertemuan->mataPelajaran->nama_mapel);
                $sheet->setCellValue('E' . $row, $item->pertemuan->tanggal->format('d-m-Y'));
                $sheet->setCellValue('F' . $row, $item->pertemuan->pertemuan_ke);
                $sheet->setCellValue('G' . $row, ucfirst($item->status));
                $sheet->setCellValue('H' . $row, $item->keterangan);
                $row++;
            }
            
            // Simpan file
            $filename = 'rekap_absensi_' . date('YmdHis') . '.xlsx';
            $writer = new Xlsx($spreadsheet);
            $path = 'exports/' . $filename;
            
            Storage::makeDirectory('exports');
            $writer->save(storage_path('app/public/' . $path));
            
            return ResponseBuilder::success(200, "Berhasil mengekspor data absensi", [
                'file_url' => url('storage/' . $path)
            ]);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mengekspor data: " . $e->getMessage());
        }
    }

    public function batchUpdate(Request $request)
    {
        $this->validate($request, [
            'absensi' => 'required|array',
            'absensi.*.id' => 'required|exists:absensi_siswa,id',
            'absensi.*.status' => 'required|in:hadir,izin,sakit,absen',
            'absensi.*.keterangan' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();
            
            $guru = Auth::user()->guru;
            $updated = 0;
            
            foreach ($request->absensi as $absen) {
                $absensi = AbsensiSiswa::whereHas('pertemuan.mataPelajaran', function($q) use ($guru) {
                    $q->where('guru_id', $guru->id);
                })->find($absen['id']);
                
                if ($absensi) {
                    // Inisialisasi nilai default
                    $hadir = 0;
                    $izin = 0;
                    $sakit = 0;
                    $absen_val = 0;
                    
                    // Set nilai berdasarkan status
                    switch ($absen['status']) {
                        case 'hadir':
                            $hadir = 1;
                            break;
                        case 'izin':
                            $izin = 1;
                            break;
                        case 'sakit':
                            $sakit = 1;
                            break;
                        case 'absen':
                            $absen_val = 1;
                            break;
                    }
                    
                    $absensi->update([
                        'hadir' => $hadir,
                        'izin' => $izin,
                        'sakit' => $sakit,
                        'absen' => $absen_val,
                        'keterangan' => $absen['keterangan'] ?? null
                    ]);
                    $updated++;
                }
            }
            
            DB::commit();
            
            return ResponseBuilder::success(200, "Berhasil mengupdate {$updated} data absensi");
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal mengupdate absensi: " . $e->getMessage());
        }
    }

    public function rekapBulanan(Request $request)
    {
        try {
            $guru = Auth::user()->guru;
            
            $bulan = $request->bulan ?? date('m');
            $tahun = $request->tahun ?? date('Y');
            
            $query = AbsensiSiswa::with(['siswa.kelas', 'pertemuan.mataPelajaran'])
                ->whereHas('pertemuan.mataPelajaran', function($q) use ($guru) {
                    $q->where('guru_id', $guru->id);
                })
                ->whereHas('pertemuan', function($q) use ($bulan, $tahun) {
                    $q->whereMonth('tanggal', $bulan)
                      ->whereYear('tanggal', $tahun);
                });
            
            // Filter berdasarkan kelas
            if ($request->kelas_id) {
                $query->whereHas('siswa', function($q) use ($request) {
                    $q->where('kelas_id', $request->kelas_id);
                });
            }
            
            $absensi = $query->get();
            
            // Hitung statistik
            $statistik = [
                'total_pertemuan' => $absensi->pluck('pertemuan_id')->unique()->count(),
                'total_siswa' => $absensi->pluck('siswa_id')->unique()->count(),
                'rekap_status' => [
                    'hadir' => $absensi->where('hadir', 1)->count(),
                    'izin' => $absensi->where('izin', 1)->count(),
                    'sakit' => $absensi->where('sakit', 1)->count(),
                    'absen' => $absensi->where('absen', 1)->count(),
                ]
            ];
            
            // Kelompokkan berdasarkan siswa
            $rekapSiswa = [];
            foreach ($absensi as $absen) {
                $siswaId = $absen->siswa_id;
                
                if (!isset($rekapSiswa[$siswaId])) {
                    $rekapSiswa[$siswaId] = [
                        'siswa' => [
                            'id' => $absen->siswa->id,
                            'nama' => $absen->siswa->nama,
                            'kelas' => $absen->siswa->kelas->nama_kelas
                        ],
                        'rekap' => [
                            'hadir' => 0,
                            'izin' => 0,
                            'sakit' => 0,
                            'absen' => 0
                        ]
                    ];
                }
                
                if ($absen->hadir == 1) $rekapSiswa[$siswaId]['rekap']['hadir']++;
                if ($absen->izin == 1) $rekapSiswa[$siswaId]['rekap']['izin']++;
                if ($absen->sakit == 1) $rekapSiswa[$siswaId]['rekap']['sakit']++;
                if ($absen->absen == 1) $rekapSiswa[$siswaId]['rekap']['absen']++;
            }
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan rekap bulanan", [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'statistik' => $statistik,
                'rekap_siswa' => array_values($rekapSiswa)
            ]);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }

    public function getSiswaForAbsensi(Request $request)
    {
        try {
            $this->validate($request, [
                'kelas_id' => 'required|exists:kelas,id',
                'bulan' => 'nullable|integer|min:1|max:12',
                'tahun' => 'nullable|integer|min:2000|max:2100',
            ]);
            
            $guru = Auth::user()->guru;
            $bulan = $request->bulan ?? date('m');
            $tahun = $request->tahun ?? date('Y');
            
            // Cek apakah guru mengajar di kelas ini
            $kelasExists = \App\Models\MataPelajaran::where('guru_id', $guru->id)
                ->where('kelas_id', $request->kelas_id)
                ->exists();
                
            if (!$kelasExists) {
                return ResponseBuilder::error(403, "Anda tidak memiliki akses ke kelas ini");
            }
            
            // Ambil data siswa di kelas tersebut
            $siswa = \App\Models\Siswa::where('kelas_id', $request->kelas_id)
                ->where('sekolah_id', $guru->sekolah_id)
                ->orderBy('nama', 'asc')
                ->get();
                
            // Hitung total pertemuan di bulan ini
            $totalPertemuan = \App\Models\Pertemuan::where('kelas_id', $request->kelas_id)
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->count();
                
            // Format data untuk frontend
            $data = [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'total_pertemuan' => $totalPertemuan,
                'siswa' => $siswa->map(function($item) use ($bulan, $tahun) {
                    // Ambil rekap absensi siswa di bulan ini
                    $absensi = AbsensiSiswa::whereHas('pertemuan', function($q) use ($bulan, $tahun) {
                        $q->whereMonth('tanggal', $bulan)
                          ->whereYear('tanggal', $tahun);
                    })
                    ->where('siswa_id', $item->id)
                    ->get();
                    
                    return [
                        'id' => $item->id,
                        'nama' => $item->nama,
                        'rekap' => [
                            'hadir' => $absensi->where('hadir', 1)->count(),
                            'izin' => $absensi->where('izin', 1)->count(),
                            'sakit' => $absensi->where('sakit', 1)->count(),
                            'absen' => $absensi->where('absen', 1)->count(),
                        ]
                    ];
                })
            ];
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan data siswa untuk absensi", $data);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }

    public function storeBulanan(Request $request)
    {
        $this->validate($request, [
            'kelas_id' => 'required|exists:kelas,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000|max:2100',
            'total_pertemuan' => 'required|integer|min:1',
            'absensi' => 'required|array',
            'absensi.*.siswa_id' => 'required|exists:siswa,id',
            'absensi.*.hadir' => 'required|integer|min:0',
            'absensi.*.izin' => 'required|integer|min:0',
            'absensi.*.sakit' => 'required|integer|min:0'
        ]);

        try {
            DB::beginTransaction();
            
            $guru = Auth::user()->guru;
            
            // Validasi total pertemuan
            $totalHadir = $request->total_pertemuan;
            
            // Validasi jumlah kehadiran tidak melebihi total pertemuan
            foreach ($request->absensi as $absen) {
                $totalKehadiran = $absen['hadir'] + $absen['izin'] + $absen['sakit'];
                if ($totalKehadiran > $totalHadir) {
                    return ResponseBuilder::error(400, "Total kehadiran siswa tidak boleh melebihi total pertemuan");
                }
                
                // Hitung jumlah absen
                $absen_val = $totalHadir - $totalKehadiran;
                
                // Cek apakah sudah ada rekap bulanan
                $existingRekap = \App\Models\RekapBulanan::where('siswa_id', $absen['siswa_id'])
                    ->where('kelas_id', $request->kelas_id)
                    ->where('mata_pelajaran_id', $request->mata_pelajaran_id)
                    ->where('bulan', $request->bulan)
                    ->where('tahun', $request->tahun)
                    ->first();
                    
                if ($existingRekap) {
                    // Update rekap yang sudah ada
                    $existingRekap->update([
                        'total_pertemuan' => $totalHadir,
                        'hadir' => $absen['hadir'],
                        'izin' => $absen['izin'],
                        'sakit' => $absen['sakit'],
                        'absen' => $absen_val,
                        'updated_by' => Auth::user()->id
                    ]);
                } else {
                    // Buat rekap baru
                    \App\Models\RekapBulanan::create([
                        'id' => Uuid::uuid4()->toString(),
                        'siswa_id' => $absen['siswa_id'],
                        'kelas_id' => $request->kelas_id,
                        'mata_pelajaran_id' => $request->mata_pelajaran_id,
                        'bulan' => $request->bulan,
                        'tahun' => $request->tahun,
                        'total_pertemuan' => $totalHadir,
                        'hadir' => $absen['hadir'],
                        'izin' => $absen['izin'],
                        'sakit' => $absen['sakit'],
                        'absen' => $absen_val,
                        'created_by' => Auth::user()->id,
                        'sekolah_id' => $guru->sekolah_id
                    ]);
                }
            }
            
            DB::commit();
            
            return ResponseBuilder::success(201, "Berhasil menyimpan rekap absensi bulanan");
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal menyimpan rekap absensi: " . $e->getMessage());
        }
    }
} 