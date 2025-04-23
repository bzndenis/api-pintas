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

            // Debug Step 1: Cek parameter awal
            \Log::info('Debug Step 1 - Initial Parameters:', [
                'mapel_id' => $mapel_id,
                'guru_id' => $guru->id ?? null
            ]);

            if (empty($mapel_id)) {
                return ResponseBuilder::error(400, "Parameter mata_pelajaran_id harus diisi");
            }

            if (!$guru) {
                return ResponseBuilder::error(403, "Akses ditolak. Anda bukan guru");
            }

            // Debug Step 2: Cek Mata Pelajaran
            $mapel = MataPelajaran::where('id', $mapel_id)
                ->where('guru_id', $guru->id)
                ->first();
            
            \Log::info('Debug Step 2 - Mata Pelajaran Check:', [
                'mapel_exists' => !is_null($mapel),
                'mapel_data' => $mapel ? $mapel->toArray() : null,
                'query' => [
                    'mapel_id' => $mapel_id,
                    'guru_id' => $guru->id
                ]
            ]);

            if (!$mapel) {
                return ResponseBuilder::error(404, "Mata pelajaran tidak ditemukan atau Anda tidak mengajar mata pelajaran ini");
            }

            // Debug Step 3: Cek Kelas (Menggunakan pendekatan baru)
            // Ambil kelas dari siswa yang memiliki nilai untuk mata pelajaran ini
            $kelasQuery = Kelas::whereHas('siswa.nilaiSiswa.tujuanPembelajaran.capaianPembelajaran', function($q) use ($mapel_id) {
                $q->where('mapel_id', $mapel_id);
            })
            ->where('sekolah_id', Auth::user()->sekolah_id)
            ->whereNull('deleted_at');

            // Debug query kelas
            \Log::info('Debug Step 3 - Kelas Query:', [
                'sql' => $kelasQuery->toSql(),
                'bindings' => $kelasQuery->getBindings()
            ]);

            $kelas = $kelasQuery->first();

            \Log::info('Debug Step 3 - Kelas Result:', [
                'kelas_exists' => !is_null($kelas),
                'kelas_data' => $kelas ? $kelas->toArray() : null
            ]);

            if (!$kelas) {
                // Debug tambahan untuk cek data
                $debugData = DB::select("
                    SELECT DISTINCT k.id as kelas_id, k.nama_kelas 
                    FROM kelas k
                    JOIN siswa s ON s.kelas_id = k.id
                    JOIN nilai_siswa ns ON ns.siswa_id = s.id
                    JOIN tujuan_pembelajaran tp ON ns.tp_id = tp.id
                    JOIN capaian_pembelajaran cp ON tp.cp_id = cp.id
                    WHERE cp.mapel_id = ?
                    AND k.sekolah_id = ?
                ", [$mapel_id, Auth::user()->sekolah_id]);

                \Log::info('Debug Step 3.1 - Check Data:', [
                    'data_exists' => !empty($debugData),
                    'data' => $debugData
                ]);

                return ResponseBuilder::error(404, "Tidak ditemukan kelas yang memiliki nilai untuk mata pelajaran ini");
            }

            // Debug Step 4: Cek Data Terkait
            $siswaCount = Siswa::where('kelas_id', $kelas->id)
                ->where('sekolah_id', Auth::user()->sekolah_id)
                ->count();
            
            $cp = CapaianPembelajaran::where('mapel_id', $mapel_id)->get();
            $cpIds = $cp->pluck('id');
            
            $tp = TujuanPembelajaran::whereIn('cp_id', $cpIds)->get();
            $tpIds = $tp->pluck('id');

            \Log::info('Debug Step 4 - Related Data:', [
                'siswa_count' => $siswaCount,
                'cp_count' => $cp->count(),
                'cp_ids' => $cpIds,
                'tp_count' => $tp->count(),
                'tp_ids' => $tpIds
            ]);

            // Debug Step 5: Cek Nilai
            $nilaiQuery = NilaiSiswa::with([
                'siswa',
                'tujuanPembelajaran.capaianPembelajaran'
            ])->whereHas('siswa', function($q) use ($kelas) {
                $q->where('kelas_id', $kelas->id);
            })->whereHas('tujuanPembelajaran', function($q) use ($mapel_id) {
                $q->whereHas('capaianPembelajaran', function($q) use ($mapel_id) {
                    $q->where('mapel_id', $mapel_id);
                });
            });

            \Log::info('Debug Step 5 - Nilai Query:', [
                'sql' => $nilaiQuery->toSql(),
                'bindings' => $nilaiQuery->getBindings()
            ]);

            $nilaiSiswa = $nilaiQuery->get();

            \Log::info('Debug Step 5 - Nilai Results:', [
                'nilai_count' => $nilaiSiswa->count(),
                'first_few_records' => $nilaiSiswa->take(3)->toArray()
            ]);

            if ($nilaiSiswa->isEmpty()) {
                // Coba query langsung seperti di SQL
                $rawNilai = \DB::select("
                    SELECT * FROM nilai_siswa 
                    WHERE siswa_id IN (
                        SELECT id FROM siswa 
                        WHERE kelas_id = ?
                    ) AND tp_id IN (
                        SELECT id FROM tujuan_pembelajaran 
                        WHERE cp_id IN (
                            SELECT id FROM capaian_pembelajaran 
                            WHERE mapel_id = ?
                        )
                    )
                ", [$kelas->id, $mapel_id]);

                if (empty($rawNilai)) {
                    return ResponseBuilder::error(404, "Tidak ada data nilai untuk kelas dan mata pelajaran yang dipilih. Pastikan: \n1. Ada siswa di kelas tersebut \n2. Ada capaian pembelajaran untuk mata pelajaran \n3. Ada tujuan pembelajaran \n4. Ada nilai yang sudah diinput");
                }

                // Jika data ditemukan dengan raw query, konversi ke collection
                $nilaiSiswa = collect($rawNilai)->groupBy('siswa_id')
                    ->map(function($nilai) {
                        $siswa = Siswa::find($nilai->first()->siswa_id);
                        return [
                            'nama' => $siswa->nama,
                            'nilai' => $nilai->pluck('nilai')->toArray()
                        ];
                    })->values();
            }

            // Proses data nilai
            $nilaiSiswa = $nilaiSiswa->groupBy('siswa_id')
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
            
            $sheet->setCellValue('A2', 'Kelas: ' . $kelas->nama_kelas);
            $sheet->setCellValue('A3', 'Mata Pelajaran: ' . $mapel->nama_mapel);
            $sheet->setCellValue('A4', 'Tanggal: ' . date('d/m/Y'));
            
            // Header tabel
            $sheet->setCellValue('A6', 'No');
            $sheet->setCellValue('B6', 'Nama Siswa');
            
            // Set header nilai (S1-S7)
            $kolom = 'C';
            for($i = 1; $i <= 7; $i++) {
                $sheet->setCellValue($kolom.'6', 'S-'.$i);
                $kolom++;
            }
            
            // Isi data
            $row = 7;
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
            $sheet->getStyle('A6:'.$lastColumn.$lastRow)->applyFromArray($borderStyle);
            
            // Auto size columns
            foreach(range('A', $lastColumn) as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            // Center align header
            $sheet->getStyle('A6:'.$lastColumn.'6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
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