<?php

namespace App\Http\Controllers\Guru;

use App\Models\NilaiSiswa;
use App\Models\AbsensiSiswa;
use App\Models\UserActivity;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\TahunAjaran;
use App\Models\Siswa;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;
use App\Models\CapaianPembelajaran;
use App\Models\TujuanPembelajaran;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function nilai(Request $request)
    {
        try {
            $query = NilaiSiswa::with([
                'siswa.kelas',
                'tujuanPembelajaran.capaianPembelajaran.mataPelajaran'
            ])->whereHas('siswa', function($q) {
                $q->where('sekolah_id', Auth::user()->sekolah_id);
            });
            
            // Filter berdasarkan tahun ajaran
            if ($request->tahun_ajaran_id) {
                $query->whereHas('siswa.kelas', function($q) use ($request) {
                    $q->where('tahun_ajaran_id', $request->tahun_ajaran_id);
                });
            }
            
            // Filter berdasarkan semester
            if ($request->semester) {
                $query->where('semester', $request->semester);
            }
            
            // Filter berdasarkan mata pelajaran
            if ($request->mapel_id) {
                $query->whereHas('tujuanPembelajaran.capaianPembelajaran', function($q) use ($request) {
                    $q->where('mata_pelajaran_id', $request->mapel_id);
                });
            }
            
            // Filter berdasarkan kelas
            if ($request->kelas_id) {
                $query->whereHas('siswa', function($q) use ($request) {
                    $q->where('kelas_id', $request->kelas_id);
                });
            }
            
            // Filter berdasarkan siswa
            if ($request->siswa_id) {
                $query->where('siswa_id', $request->siswa_id);
            }
            
            $nilai = $query->get();
            
            // Hitung statistik
            $statistik = [
                'total_siswa' => $nilai->pluck('siswa_id')->unique()->count(),
                'rata_rata' => $nilai->avg('nilai'),
                'nilai_tertinggi' => $nilai->max('nilai'),
                'nilai_terendah' => $nilai->min('nilai')
            ];
            
            $data = [
                'nilai' => $nilai,
                'statistik' => $statistik
            ];
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan data nilai", $data);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }
    
    public function exportNilai(Request $request)
    {
        try {
            $mapel_id = $request->query('mata_pelajaran_id') ?? $request->input('mata_pelajaran_id');
            $guru = Auth::user()->guru;

            if (empty($mapel_id)) {
                return ResponseBuilder::error(400, "Parameter mata_pelajaran_id harus diisi");
            }

            if (!$guru) {
                return ResponseBuilder::error(403, "Akses ditolak. Anda bukan guru");
            }

            // Cek Mata Pelajaran
            $mapel = MataPelajaran::where('id', $mapel_id)
                ->where('guru_id', $guru->id)
                ->first();

            if (!$mapel) {
                return ResponseBuilder::error(404, "Mata pelajaran tidak ditemukan atau Anda tidak mengajar mata pelajaran ini");
            }

            // Ambil kelas dari siswa yang memiliki nilai untuk mata pelajaran ini
            $kelas = Kelas::whereHas('siswa.nilaiSiswa.tujuanPembelajaran.capaianPembelajaran', function($q) use ($mapel_id) {
                $q->where('mapel_id', $mapel_id);
            })
            ->where('sekolah_id', Auth::user()->sekolah_id)
            ->first();

            if (!$kelas) {
                return ResponseBuilder::error(404, "Tidak ditemukan kelas yang memiliki nilai untuk mata pelajaran ini");
            }

            // Ambil data CP untuk mata pelajaran ini
            $capaianPembelajaran = CapaianPembelajaran::where('mapel_id', $mapel_id)
                ->with(['tujuanPembelajaran.nilaiSiswa' => function($q) use ($kelas) {
                    $q->whereHas('siswa', function($q) use ($kelas) {
                        $q->where('kelas_id', $kelas->id);
                    });
                }])
                ->get();

            // Ambil data siswa
            $siswa = Siswa::where('kelas_id', $kelas->id)
                ->where('sekolah_id', Auth::user()->sekolah_id)
                ->orderBy('nama')
                ->get();

            // Buat spreadsheet baru
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set judul sheet
            $sheet->setTitle('Rekap Nilai');
            
            // Header laporan
            $sheet->setCellValue('A1', 'REKAP NILAI SISWA');
            $sheet->setCellValue('A2', 'Kelas: ' . $kelas->nama_kelas);
            $sheet->setCellValue('A3', 'Mata Pelajaran: ' . $mapel->nama_mapel);
            $sheet->setCellValue('A4', 'Tanggal: ' . date('d/m/Y'));
            
            // Header tabel
            $sheet->setCellValue('A6', 'No');
            $sheet->setCellValue('B6', 'Nama Siswa');
            
            // Set header CP
            $kolom = 'C';
            foreach($capaianPembelajaran as $cp) {
                $sheet->setCellValue($kolom.'6', 'CP-'.substr($cp->kode_cp, -2));
                $kolom++;
            }
            
            // Isi data
            $row = 7;
            foreach($siswa as $index => $s) {
                $sheet->setCellValue('A'.$row, $index + 1);
                $sheet->setCellValue('B'.$row, $s->nama);
                
                $kolom = 'C';
                foreach($capaianPembelajaran as $cp) {
                    // Hitung rata-rata nilai TP untuk CP ini
                    $nilaiTP = collect();
                    foreach($cp->tujuanPembelajaran as $tp) {
                        $nilai = $tp->nilaiSiswa->where('siswa_id', $s->id)->first();
                        if($nilai) {
                            $nilaiTP->push($nilai->nilai);
                        }
                    }
                    
                    $rataCP = $nilaiTP->isNotEmpty() ? $nilaiTP->avg() : '';
                    $sheet->setCellValue($kolom.$row, $rataCP);
                    $kolom++;
                }
                $row++;
            }
            
            // Style tabel
            $lastRow = $row - 1;
            $lastColumn = $kolom;
            
            // Border style
            $borderStyle = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN
                    ]
                ]
            ];
            
            // Apply style ke tabel
            $sheet->getStyle('A6:'.$lastColumn.$lastRow)->applyFromArray($borderStyle);
            
            // Auto size columns
            foreach(range('A', $lastColumn) as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            // Center align header
            $sheet->getStyle('A6:'.$lastColumn.'6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
            // Set format angka untuk nilai
            $sheet->getStyle('C7:'.$lastColumn.$lastRow)->getNumberFormat()->setFormatCode('0.00');
            
            // Set header untuk download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="Rekap Nilai Siswa.xlsx"');
            header('Cache-Control: max-age=0');
            
            // Create Excel file
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
            
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mengekspor data: " . $e->getMessage());
        }
    }
    
    public function absensi(Request $request)
    {
        try {
            $query = AbsensiSiswa::with([
                'siswa.kelas',
                'pertemuan'
            ])->whereHas('siswa', function($q) {
                $q->where('sekolah_id', Auth::user()->sekolah_id);
            });
            
            // Filter berdasarkan tahun ajaran
            if ($request->tahun_ajaran_id) {
                $query->whereHas('siswa.kelas', function($q) use ($request) {
                    $q->where('tahun_ajaran_id', $request->tahun_ajaran_id);
                });
            }
            
            // Filter berdasarkan bulan
            if ($request->bulan) {
                $query->whereHas('pertemuan', function($q) use ($request) {
                    $q->where('bulan', $request->bulan);
                });
            }
            
            // Filter berdasarkan tahun
            if ($request->tahun) {
                $query->whereHas('pertemuan', function($q) use ($request) {
                    $q->where('tahun', $request->tahun);
                });
            }
            
            // Filter berdasarkan kelas
            if ($request->kelas_id) {
                $query->whereHas('siswa', function($q) use ($request) {
                    $q->where('kelas_id', $request->kelas_id);
                });
            }
            
            // Filter berdasarkan siswa
            if ($request->siswa_id) {
                $query->where('siswa_id', $request->siswa_id);
            }
            
            $absensi = $query->get();
            
            // Hitung statistik
            $statistik = [
                'total_siswa' => $absensi->pluck('siswa_id')->unique()->count(),
                'total_hadir' => $absensi->sum('hadir'),
                'total_izin' => $absensi->sum('izin'),
                'total_sakit' => $absensi->sum('sakit'),
                'total_absen' => $absensi->sum('absen')
            ];
            
            $data = [
                'absensi' => $absensi,
                'statistik' => $statistik
            ];
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan data absensi", $data);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }
    
    public function aktivitas(Request $request)
    {
        try {
            $query = UserActivity::with(['user'])
                ->where('sekolah_id', Auth::user()->sekolah_id);
            
            // Filter berdasarkan user
            if ($request->user_id) {
                $query->where('user_id', $request->user_id);
            }
            
            // Filter berdasarkan tanggal
            if ($request->start_date && $request->end_date) {
                $query->whereBetween('created_at', [
                    Carbon::parse($request->start_date)->startOfDay(),
                    Carbon::parse($request->end_date)->endOfDay()
                ]);
            }
            
            // Filter berdasarkan action
            if ($request->action) {
                $query->where('action', 'like', "%{$request->action}%");
            }
            
            $aktivitas = $query->orderBy('created_at', 'desc')
                ->paginate($request->per_page ?? 10);
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan data aktivitas", $aktivitas);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }

    public function checkNilaiData(Request $request)
    {
        try {
            $mapel_id = $request->query('mata_pelajaran_id') ?? $request->input('mata_pelajaran_id');
            $kelas_id = $request->query('kelas_id') ?? $request->input('kelas_id');

            if (empty($mapel_id) || empty($kelas_id)) {
                return ResponseBuilder::error(400, "Parameter mata_pelajaran_id dan kelas_id harus diisi");
            }

            // Cek siswa
            $siswa = Siswa::where('kelas_id', $kelas_id)
                ->where('sekolah_id', Auth::user()->sekolah_id)
                ->get(['id', 'nama', 'nisn']);

            // Cek capaian pembelajaran
            $cp = CapaianPembelajaran::where('mapel_id', $mapel_id)
                ->get(['id', 'kode_cp', 'nama']);

            // Cek tujuan pembelajaran
            $tp = TujuanPembelajaran::whereIn('cp_id', $cp->pluck('id'))
                ->get(['id', 'kode_tp', 'nama']);

            // Cek nilai
            $nilai = NilaiSiswa::whereIn('siswa_id', $siswa->pluck('id'))
                ->whereIn('tp_id', $tp->pluck('id'))
                ->get(['id', 'siswa_id', 'tp_id', 'nilai']);

            return ResponseBuilder::success(200, "Data berhasil dicek", [
                'siswa' => [
                    'count' => $siswa->count(),
                    'data' => $siswa
                ],
                'capaian_pembelajaran' => [
                    'count' => $cp->count(),
                    'data' => $cp
                ],
                'tujuan_pembelajaran' => [
                    'count' => $tp->count(),
                    'data' => $tp
                ],
                'nilai' => [
                    'count' => $nilai->count(),
                    'data' => $nilai
                ]
            ]);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mengecek data: " . $e->getMessage());
        }
    }

    public function getDropdownData()
    {
        try {
            // Get mata pelajaran yang memiliki nilai
            $mataPelajaran = MataPelajaran::where('sekolah_id', Auth::user()->sekolah_id)
                ->whereHas('capaianPembelajaran.tujuanPembelajaran.nilaiSiswa')
                ->select('id', 'nama_mapel as nama', 'kode_mapel as kode')
                ->orderBy('nama_mapel')
                ->get();

            // Get kelas yang memiliki nilai
            $kelas = Kelas::where('sekolah_id', Auth::user()->sekolah_id)
                ->whereHas('siswa.nilaiSiswa')
                ->select('id', 'nama_kelas as nama', 'tingkat')
                ->orderBy('tingkat')
                ->orderBy('nama_kelas')
                ->get();

            // Debug: Log data yang ditemukan
            \Log::info('Dropdown Data:', [
                'mata_pelajaran_count' => $mataPelajaran->count(),
                'kelas_count' => $kelas->count(),
                'sekolah_id' => Auth::user()->sekolah_id
            ]);

            if ($mataPelajaran->isEmpty() && $kelas->isEmpty()) {
                return ResponseBuilder::error(404, "Tidak ada data nilai yang tersedia");
            }

            return ResponseBuilder::success(200, "Berhasil mendapatkan data dropdown", [
                'mata_pelajaran' => $mataPelajaran,
                'kelas' => $kelas
            ]);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data dropdown: " . $e->getMessage());
        }
    }
} 