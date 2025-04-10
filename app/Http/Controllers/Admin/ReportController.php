<?php

namespace App\Http\Controllers\Admin;

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

class ReportController extends BaseAdminController
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
            // Validasi request
            $this->validate($request, [
                'semester' => 'required|in:1,2',
                'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
                'kelas_id' => 'required|exists:kelas,id'
            ]);

            // Query data nilai
            $query = NilaiSiswa::with([
                'siswa',
                'tujuanPembelajaran.capaianPembelajaran.mataPelajaran'
            ])->whereHas('siswa', function($q) use ($request) {
                $q->where('kelas_id', $request->kelas_id)
                  ->where('sekolah_id', Auth::user()->sekolah_id);
            })->whereHas('tujuanPembelajaran.capaianPembelajaran', function($q) use ($request) {
                $q->where('mata_pelajaran_id', $request->mata_pelajaran_id);
            });

            // Filter semester
            if ($request->semester) {
                $query->where('semester', $request->semester);
            }

            $nilaiSiswa = $query->get()
                ->groupBy('siswa_id')
                ->map(function($nilai) {
                    $siswa = $nilai->first()->siswa;
                    $nilaiUrut = $nilai->sortBy('tujuanPembelajaran.kode_tp')->values();
                    
                    $data = [
                        'nama' => $siswa->nama,
                    ];
                    
                    foreach($nilaiUrut as $index => $n) {
                        $data['S-'.($index+1)] = $n->nilai;
                    }
                    
                    return $data;
                })->values();

            // Buat spreadsheet baru
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set judul sheet
            $sheet->setTitle('Rekap Nilai');
            
            // Header laporan
            $sheet->setCellValue('A1', 'REKAP NILAI SISWA');
            
            // Ambil data pendukung
            $kelas = Kelas::find($request->kelas_id);
            $mapel = MataPelajaran::find($request->mata_pelajaran_id);
            
            $sheet->setCellValue('A2', 'Kelas: ' . $kelas->nama_kelas);
            $sheet->setCellValue('A3', 'Mata Pelajaran: ' . $mapel->nama_mapel);
            $sheet->setCellValue('A4', 'Semester: ' . $request->semester);
            $sheet->setCellValue('A5', 'Tanggal: ' . date('d/m/Y'));
            
            // Header tabel
            $sheet->setCellValue('A7', 'No');
            $sheet->setCellValue('B7', 'Nama Siswa');
            
            // Set header nilai (S1-S7)
            $kolom = 'C';
            for($i = 1; $i <= 7; $i++) {
                $sheet->setCellValue($kolom.'7', 'S-'.$i);
                $kolom++;
            }
            
            // Isi data
            $row = 8;
            foreach($nilaiSiswa as $index => $nilai) {
                $sheet->setCellValue('A'.$row, $index + 1);
                $sheet->setCellValue('B'.$row, $nilai['nama']);
                
                $kolom = 'C';
                for($i = 1; $i <= 7; $i++) {
                    $nilaiCell = $nilai['S-'.$i] ?? '';
                    $sheet->setCellValue($kolom.$row, $nilaiCell);
                    $kolom++;
                }
                $row++;
            }
            
            // Style tabel
            $lastRow = $row - 1;
            $lastColumn = 'I';
            
            // Border style
            $borderStyle = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN
                    ]
                ]
            ];
            
            // Apply style ke tabel
            $sheet->getStyle('A7:'.$lastColumn.$lastRow)->applyFromArray($borderStyle);
            
            // Auto size columns
            foreach(range('A', $lastColumn) as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            // Center align header
            $sheet->getStyle('A7:'.$lastColumn.'7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
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
} 