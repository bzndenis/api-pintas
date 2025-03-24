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
            // Validasi parameter yang diperlukan
            $this->validate($request, [
                'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
                'semester' => 'required|in:1,2',
                'mapel_id' => 'required|exists:mata_pelajaran,id',
                'kelas_id' => 'required|exists:kelas,id',
                'siswa_id' => 'nullable|exists:siswa,id'
            ]);
            
            // Ambil data nilai berdasarkan filter
            $query = NilaiSiswa::with([
                'siswa',
                'tujuanPembelajaran.capaianPembelajaran.mataPelajaran'
            ])->whereHas('siswa', function($q) {
                $q->where('sekolah_id', Auth::user()->sekolah_id);
            });
            
            // Filter berdasarkan tahun ajaran
            $query->whereHas('siswa.kelas', function($q) use ($request) {
                $q->where('tahun_ajaran_id', $request->tahun_ajaran_id);
            });
            
            // Filter berdasarkan semester
            $query->where('semester', $request->semester);
            
            // Filter berdasarkan mata pelajaran
            $query->whereHas('tujuanPembelajaran.capaianPembelajaran', function($q) use ($request) {
                $q->where('mata_pelajaran_id', $request->mapel_id);
            });
            
            // Filter berdasarkan kelas
            $query->whereHas('siswa', function($q) use ($request) {
                $q->where('kelas_id', $request->kelas_id);
            });
            
            // Filter berdasarkan siswa jika ada
            if ($request->siswa_id) {
                $query->where('siswa_id', $request->siswa_id);
            }
            
            $nilai = $query->get();
            
            // Ambil data pendukung
            $tahunAjaran = TahunAjaran::find($request->tahun_ajaran_id);
            $mapel = MataPelajaran::find($request->mapel_id);
            $kelas = Kelas::find($request->kelas_id);
            
            // Buat spreadsheet baru
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set judul sheet
            $sheet->setTitle('Rekap Nilai');
            
            // Header laporan
            $sheet->setCellValue('A1', 'REKAP NILAI SISWA');
            $sheet->setCellValue('A2', 'Tahun Ajaran: ' . $tahunAjaran->nama_tahun_ajaran);
            $sheet->setCellValue('A3', 'Semester: ' . $request->semester);
            $sheet->setCellValue('A4', 'Mata Pelajaran: ' . $mapel->nama_mapel);
            $sheet->setCellValue('A5', 'Kelas: ' . $kelas->nama_kelas);
            $sheet->setCellValue('A6', 'Tanggal: ' . date('d/m/Y'));
            
            // Merge cells untuk header
            $sheet->mergeCells('A1:G1');
            $sheet->mergeCells('A2:G2');
            $sheet->mergeCells('A3:G3');
            $sheet->mergeCells('A4:G4');
            $sheet->mergeCells('A5:G5');
            $sheet->mergeCells('A6:G6');
            
            // Style untuk header
            $headerStyle = [
                'font' => [
                    'bold' => true,
                    'size' => 14
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ];
            
            $sheet->getStyle('A1:G6')->applyFromArray($headerStyle);
            
            // Header tabel
            $sheet->setCellValue('A8', 'No');
            $sheet->setCellValue('B8', 'NISN');
            $sheet->setCellValue('C8', 'Nama Siswa');
            $sheet->setCellValue('D8', 'Tujuan Pembelajaran');
            $sheet->setCellValue('E8', 'Nilai');
            $sheet->setCellValue('F8', 'Keterangan');
            $sheet->setCellValue('G8', 'Tanggal');
            
            // Style untuk header tabel
            $tableHeaderStyle = [
                'font' => [
                    'bold' => true
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN
                    ]
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => 'E2EFDA'
                    ]
                ]
            ];
            
            $sheet->getStyle('A8:G8')->applyFromArray($tableHeaderStyle);
            
            // Isi tabel
            $row = 9;
            $no = 1;
            
            foreach ($nilai as $item) {
                $sheet->setCellValue('A' . $row, $no);
                $sheet->setCellValue('B' . $row, $item->siswa->nisn);
                $sheet->setCellValue('C' . $row, $item->siswa->nama);
                $sheet->setCellValue('D' . $row, $item->tujuanPembelajaran->deskripsi);
                $sheet->setCellValue('E' . $row, $item->nilai);
                
                // Tentukan keterangan berdasarkan nilai
                if ($item->nilai >= 90) {
                    $keterangan = 'Sangat Baik';
                } elseif ($item->nilai >= 80) {
                    $keterangan = 'Baik';
                } elseif ($item->nilai >= 70) {
                    $keterangan = 'Cukup';
                } elseif ($item->nilai >= 60) {
                    $keterangan = 'Kurang';
                } else {
                    $keterangan = 'Perlu Perbaikan';
                }
                
                $sheet->setCellValue('F' . $row, $keterangan);
                $sheet->setCellValue('G' . $row, Carbon::parse($item->created_at)->format('d/m/Y'));
                
                $row++;
                $no++;
            }
            
            // Style untuk isi tabel
            $bodyStyle = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN
                    ]
                ]
            ];
            
            $sheet->getStyle('A9:G' . ($row - 1))->applyFromArray($bodyStyle);
            
            // Auto-size kolom
            foreach (range('A', 'G') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            // Buat file Excel
            $writer = new Xlsx($spreadsheet);
            $filename = 'Rekap_Nilai_' . $kelas->nama_kelas . '_' . $mapel->nama_mapel . '_' . date('Ymd_His') . '.xlsx';
            
            // Simpan langsung ke direktori public tanpa menggunakan symlink
            $exportDir = 'exports';
            $publicPath = base_path('public/' . $exportDir);
            
            // Pastikan direktori ada
            if (!file_exists($publicPath)) {
                mkdir($publicPath, 0777, true);
            }
            
            $path = $publicPath . '/' . $filename;
            $writer->save($path);
            
            // Kembalikan URL untuk download
            $url = url($exportDir . '/' . $filename);
            
            return ResponseBuilder::success(200, "Berhasil mengekspor data nilai", [
                'download_url' => $url,
                'filename' => $filename
            ]);
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