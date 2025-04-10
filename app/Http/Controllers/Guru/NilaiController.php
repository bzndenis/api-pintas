<?php

namespace App\Http\Controllers\Guru;

use App\Models\NilaiSiswa;
use App\Models\Siswa;
use App\Models\TujuanPembelajaran;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use App\Models\CapaianPembelajaran;
use App\Models\Kelas;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class NilaiController extends BaseGuruController
{
    public function index(Request $request)
    {
        try {
            $guru = Auth::user()->guru;
            
            $query = NilaiSiswa::with([
                'siswa.kelas',
                'tujuanPembelajaran.capaianPembelajaran.mataPelajaran'
            ])->whereHas('tujuanPembelajaran.capaianPembelajaran.mataPelajaran', function($q) use ($guru) {
                $q->where('guru_id', $guru->id);
            });
            
            // Filter berdasarkan kelas
            if ($request->kelas_id) {
                $query->whereHas('siswa', function($q) use ($request) {
                    $q->where('kelas_id', $request->kelas_id);
                });
            }
            
            // Filter berdasarkan semester
            if ($request->semester) {
                $query->where('semester', $request->semester);
            }
            
            $nilai = $query->orderBy('created_at', 'desc')->get();
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan data nilai", $nilai);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'siswa_id' => 'required|exists:siswa,id',
            'tp_id' => 'required|exists:tujuan_pembelajaran,id',
            'nilai' => 'required|numeric|min:0|max:100'
        ]);

        try {
            DB::beginTransaction();
            
            $guru = Auth::user()->guru;
            
            // Validasi apakah guru mengajar mata pelajaran tersebut
            $tp = TujuanPembelajaran::with('capaianPembelajaran.mataPelajaran')
                ->find($request->tp_id);
                
            if (!$tp || $tp->capaianPembelajaran->mataPelajaran->guru_id !== $guru->id) {
                return ResponseBuilder::error(403, "Anda tidak memiliki akses untuk menilai mata pelajaran ini");
            }

            $nilai = NilaiSiswa::create([
                'id' => Uuid::uuid4()->toString(),
                'siswa_id' => $request->siswa_id,
                'tp_id' => $request->tp_id,
                'nilai' => $request->nilai,
                'created_by' => Auth::id(),
                'sekolah_id' => $guru->sekolah_id
            ]);

            DB::commit();
            return ResponseBuilder::success(201, "Berhasil menambahkan nilai", $nilai);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal menambahkan nilai: " . $e->getMessage());
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
            
            $nilai = NilaiSiswa::with([
                'siswa.kelas',
                'tujuanPembelajaran.capaianPembelajaran.mataPelajaran'
            ])->whereHas('tujuanPembelajaran.capaianPembelajaran.mataPelajaran', function($q) use ($guru) {
                $q->where('guru_id', $guru->id);
            })->find($id);
            
            if (!$nilai) {
                return ResponseBuilder::error(404, "Data nilai tidak ditemukan");
            }
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan data nilai", ['nilai' => $nilai]);
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
            
            // Validasi input
            $this->validate($request, [
                'nilai' => 'required|numeric|min:0|max:100'
            ]);

            $guru = Auth::user()->guru;
            
            $nilai = NilaiSiswa::whereHas('tujuanPembelajaran.capaianPembelajaran.mataPelajaran', 
                function($q) use ($guru) {
                    $q->where('guru_id', $guru->id);
                })->find($id);
            
            if (!$nilai) {
                return ResponseBuilder::error(404, "Data nilai tidak ditemukan");
            }
            
            $nilai->update([
                'nilai' => $request->nilai
            ]);
            
            return ResponseBuilder::success(200, "Berhasil mengupdate nilai", ['nilai' => $nilai]);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mengupdate nilai: " . $e->getMessage());
        }
    }

    /**
     * Menyimpan nilai siswa secara batch
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeBatch(Request $request)
    {
        $this->validate($request, [
            'nilai_batch' => 'required|array|min:1',
            'nilai_batch.*.siswa_id' => 'required|exists:siswa,id',
            'nilai_batch.*.tp_id' => 'required|exists:tujuan_pembelajaran,id',
            'nilai_batch.*.nilai' => 'required|numeric|min:0|max:100'
        ]);

        try {
            DB::beginTransaction();
            
            $guru = Auth::user()->guru;
            $nilaiBatch = $request->nilai_batch;
            $hasilInput = [];
            $errors = [];
            
            foreach ($nilaiBatch as $index => $nilaiData) {
                try {
                    // Validasi apakah guru mengajar mata pelajaran tersebut
                    $tp = TujuanPembelajaran::with('capaianPembelajaran.mataPelajaran')
                        ->find($nilaiData['tp_id']);
                        
                    if (!$tp || $tp->capaianPembelajaran->mataPelajaran->guru_id !== $guru->id) {
                        $errors[] = [
                            'index' => $index,
                            'message' => "Anda tidak memiliki akses untuk menilai mata pelajaran ini pada data ke-" . ($index + 1)
                        ];
                        continue;
                    }

                    // Buat nilai baru
                    $nilai = NilaiSiswa::create([
                        'id' => Uuid::uuid4()->toString(),
                        'siswa_id' => $nilaiData['siswa_id'],
                        'tp_id' => $nilaiData['tp_id'],
                        'nilai' => $nilaiData['nilai'],
                        'created_by' => Auth::id(),
                        'sekolah_id' => $guru->sekolah_id
                    ]);
                    
                    $hasilInput[] = $nilai;
                    
                } catch (\Exception $e) {
                    $errors[] = [
                        'index' => $index,
                        'message' => "Error pada data ke-" . ($index + 1) . ": " . $e->getMessage()
                    ];
                }
            }
            
            // Jika semua data error, rollback transaksi
            if (count($errors) === count($nilaiBatch)) {
                DB::rollBack();
                return ResponseBuilder::error(400, "Gagal menyimpan semua data nilai", ['errors' => $errors]);
            }
            
            DB::commit();
            
            return ResponseBuilder::success(201, "Berhasil menyimpan data nilai batch", [
                'nilai' => $hasilInput,
                'total_success' => count($hasilInput),
                'total_error' => count($errors),
                'errors' => $errors
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal menyimpan data nilai batch: " . $e->getMessage());
        }
    }

    /**
     * Menghasilkan template untuk input nilai
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTemplate(Request $request)
    {
        $this->validate($request, [
            'kelas_id' => 'required|exists:kelas,id',
            'capaian_pembelajaran_id' => 'required|exists:capaian_pembelajaran,id'
        ]);

        try {
            $guru = Auth::user()->guru;
            
            // Validasi apakah guru mengajar mata pelajaran dari CP ini
            $cp = CapaianPembelajaran::with('mataPelajaran')
                ->find($request->capaian_pembelajaran_id);
                
            if (!$cp || $cp->mataPelajaran->guru_id !== $guru->id) {
                return ResponseBuilder::error(403, "Anda tidak memiliki akses untuk capaian pembelajaran ini");
            }
            
            // Dapatkan semua siswa di kelas
            $siswa = Siswa::where('kelas_id', $request->kelas_id)
                ->where('sekolah_id', $guru->sekolah_id)
                ->orderBy('nama', 'asc')
                ->get(['id', 'nama', 'nisn']);
                
            if ($siswa->isEmpty()) {
                return ResponseBuilder::error(404, "Tidak ada siswa di kelas ini");
            }
            
            // Dapatkan semua tujuan pembelajaran dari CP ini
            $tujuanPembelajaran = TujuanPembelajaran::where('cp_id', $request->capaian_pembelajaran_id)
                ->where('sekolah_id', $guru->sekolah_id)
                ->get(['id', 'kode_tp', 'deskripsi']);
                
            if ($tujuanPembelajaran->isEmpty()) {
                return ResponseBuilder::error(404, "Tidak ada tujuan pembelajaran untuk capaian pembelajaran ini");
            }

            // Buat spreadsheet baru
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set judul sheet dengan format yang lebih jelas
            $sheet->setTitle('Input Nilai');
            
            // Header informasi dengan format yang lebih rapi
            $sheet->setCellValue('A1', 'TEMPLATE INPUT NILAI SISWA');
            $sheet->setCellValue('A2', 'Kelas: ' . Kelas::find($request->kelas_id)->nama_kelas);
            $sheet->setCellValue('A3', 'Mata Pelajaran: ' . $cp->mataPelajaran->nama_mapel);
            $sheet->setCellValue('A4', 'Capaian Pembelajaran: ' . $cp->deskripsi);
            
            // Merge cells untuk header
            $sheet->mergeCells('A1:G1');
            $sheet->mergeCells('A2:G2');
            $sheet->mergeCells('A3:G3');
            $sheet->mergeCells('A4:G4');

            // Header tabel dengan format yang lebih informatif
            $sheet->setCellValue('A6', 'No');
            $sheet->setCellValue('B6', 'NISN');
            $sheet->setCellValue('C6', 'Nama Siswa');
            $sheet->setCellValue('D6', 'Tujuan Pembelajaran');
            $sheet->setCellValue('E6', 'Nilai');
            $sheet->setCellValue('F6', 'ID Siswa (Jangan Diubah)');
            $sheet->setCellValue('G6', 'ID TP (Jangan Diubah)');
            
            // Style untuk header
            $headerStyle = [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E0E0E0']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ];
            $sheet->getStyle('A1:G6')->applyFromArray($headerStyle);
            
            // Isi data dengan nomor urut
            $row = 7;
            $no = 1;
            foreach ($siswa as $s) {
                foreach ($tujuanPembelajaran as $tp) {
                    $sheet->setCellValue('A' . $row, $no);
                    $sheet->setCellValue('B' . $row, $s->nisn);
                    $sheet->setCellValue('C' . $row, $s->nama);
                    $sheet->setCellValue('D' . $row, $tp->kode_tp . ' - ' . $tp->deskripsi);
                    $sheet->setCellValue('F' . $row, $s->id);
                    $sheet->setCellValue('G' . $row, $tp->id);
                    
                    // Proteksi kolom ID
                    $sheet->getStyle('F' . $row)->getProtection()->setLocked(true);
                    $sheet->getStyle('G' . $row)->getProtection()->setLocked(true);
                    
                    // Style untuk baris data
                    $sheet->getStyle('A' . $row . ':G' . $row)->getAlignment()
                        ->setVertical(Alignment::VERTICAL_CENTER);
                    
                    $row++;
                    $no++;
                }
            }

            // Sembunyikan kolom ID
            $sheet->getColumnDimension('F')->setVisible(false);
            $sheet->getColumnDimension('G')->setVisible(false);
            
            // Proteksi worksheet
            $sheet->getProtection()->setSheet(true);
            
            // Beri warna berbeda untuk kolom nilai
            $sheet->getStyle('E7:E' . ($row-1))->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('FFFFFF');
                
            // Unlock kolom nilai untuk editing
            $sheet->getStyle('E7:E' . ($row-1))->getProtection()->setLocked(false);
            
            // Auto-size kolom yang visible
            foreach (range('A', 'E') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            // Buat file Excel
            $filename = 'Template_Nilai_' . Kelas::find($request->kelas_id)->nama_kelas . '_' . date('Ymd_His') . '.xlsx';
            
            // Kembalikan file sebagai stream
            return response()->streamDownload(function() use ($spreadsheet) {
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                $writer->save('php://output');
            }, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal membuat template: " . $e->getMessage());
        }
    }

    /**
     * Mengimpor nilai dari template
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function import(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|file|mimes:xlsx,xls|max:2048',
        ]);

        try {
            DB::beginTransaction();
            
            $guru = Auth::user()->guru;
            $nilaiImport = $request->nilai_import;
            $hasilInput = [];
            $hasilUpdate = [];
            $errors = [];
            
            foreach ($nilaiImport as $index => $nilaiData) {
                try {
                    // Validasi apakah guru mengajar mata pelajaran tersebut
                    $tp = TujuanPembelajaran::with('capaianPembelajaran.mataPelajaran')
                        ->find($nilaiData['tp_id']);
                        
                    if (!$tp || $tp->capaianPembelajaran->mataPelajaran->guru_id !== $guru->id) {
                        $errors[] = [
                            'index' => $index,
                            'message' => "Anda tidak memiliki akses untuk menilai mata pelajaran ini pada data ke-" . ($index + 1)
                        ];
                        continue;
                    }

                    // Cek apakah nilai sudah ada
                    $existingNilai = NilaiSiswa::where('siswa_id', $nilaiData['siswa_id'])
                        ->where('tp_id', $nilaiData['tp_id'])
                        ->where('sekolah_id', Auth::user()->sekolah_id)
                        ->first();
                    
                    if ($existingNilai) {
                        // Update nilai yang sudah ada
                        $existingNilai->update([
                            'nilai' => $nilaiData['nilai']
                        ]);
                        
                        $hasilUpdate[] = $existingNilai;
                    } else {
                        // Buat nilai baru
                        $nilai = NilaiSiswa::create([
                            'siswa_id' => $nilaiData['siswa_id'],
                            'tp_id' => $nilaiData['tp_id'],
                            'nilai' => $nilaiData['nilai'],
                            'created_by' => Auth::id(),
                            'sekolah_id' => $guru->sekolah_id
                        ]);
                        
                        $hasilInput[] = $nilai;
                    }
                    
                } catch (\Exception $e) {
                    $errors[] = [
                        'index' => $index,
                        'message' => "Error pada data ke-" . ($index + 1) . ": " . $e->getMessage()
                    ];
                }
            }
            
            // Jika semua data error, rollback transaksi
            if (count($errors) === count($nilaiImport)) {
                DB::rollBack();
                return ResponseBuilder::error(400, "Gagal menyimpan semua data nilai", ['errors' => $errors]);
            }
            
            DB::commit();
            
            return ResponseBuilder::success(201, "Berhasil mengimpor data nilai", [
                'nilai_baru' => $hasilInput,
                'nilai_diupdate' => $hasilUpdate,
                'total_success' => count($hasilInput) + count($hasilUpdate),
                'total_baru' => count($hasilInput),
                'total_diupdate' => count($hasilUpdate),
                'total_error' => count($errors),
                'errors' => $errors
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal mengimpor data nilai: " . $e->getMessage());
        }
    }

    public function rekapNilai(Request $request)
    {
        try {
            $guru = Auth::user()->guru;
            
            // Dapatkan semua nilai siswa untuk mata pelajaran yang diajar guru
            $nilaiSiswa = NilaiSiswa::with(['siswa', 'tujuanPembelajaran.capaianPembelajaran.mataPelajaran'])
                ->whereHas('tujuanPembelajaran.capaianPembelajaran.mataPelajaran', function($q) use ($guru) {
                    $q->where('guru_id', $guru->id);
                })
                ->get()
                ->groupBy('siswa_id')
                ->map(function($nilai) {
                    $siswa = $nilai->first()->siswa;
                    // Urutkan nilai berdasarkan urutan tp_id
                    $nilaiUrut = $nilai->sortBy('tp_id')->values();
                    
                    $data = [
                        'nama' => $siswa->nama,
                    ];
                    
                    // Tambahkan nilai sesuai urutan
                    foreach($nilaiUrut as $index => $n) {
                        $data['S-'.($index+1)] = $n->nilai;
                    }
                    
                    return $data;
                });
                
            return ResponseBuilder::success(200, "Berhasil mendapatkan rekap nilai", $nilaiSiswa);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }

    public function storeBatchFromUI(Request $request)
    {
        $this->validate($request, [
            'nilai_batch' => 'required|array|min:1',
            'nilai_batch.*.siswa_id' => 'required|exists:siswa,id',
            'nilai_batch.*.nilai' => 'required|numeric|min:0|max:100',
            'tp_id' => 'required|exists:tujuan_pembelajaran,id'
        ]);

        try {
            DB::beginTransaction();
            
            $guru = Auth::user()->guru;
            $tp = TujuanPembelajaran::with('capaianPembelajaran.mataPelajaran')
                ->find($request->tp_id);
                
            // Validasi akses guru ke mata pelajaran
            if (!$tp || $tp->capaianPembelajaran->mataPelajaran->guru_id !== $guru->id) {
                return ResponseBuilder::error(403, "Anda tidak memiliki akses untuk menilai mata pelajaran ini");
            }

            $hasilInput = [];
            $hasilUpdate = [];
            $errors = [];

            foreach ($request->nilai_batch as $nilaiData) {
                try {
                    // Validasi format UUID untuk siswa_id
                    if (!Uuid::isValid($nilaiData['siswa_id'])) {
                        throw new \Exception("Format ID Siswa tidak valid");
                    }

                    // Cek apakah nilai sudah ada
                    $existingNilai = NilaiSiswa::where('siswa_id', $nilaiData['siswa_id'])
                        ->where('tp_id', $request->tp_id)
                        ->where('sekolah_id', $guru->sekolah_id)
                        ->first();

                    if ($existingNilai) {
                        // Update nilai yang sudah ada
                        $existingNilai->update([
                            'nilai' => $nilaiData['nilai'],
                            'updated_by' => Auth::id()
                        ]);
                        $hasilUpdate[] = $existingNilai;
                    } else {
                        // Buat nilai baru dengan UUID v4
                        $nilai = NilaiSiswa::create([
                            'id' => Uuid::uuid4()->toString(), // Generate UUID menggunakan Ramsey\Uuid
                            'siswa_id' => $nilaiData['siswa_id'],
                            'tp_id' => $request->tp_id,
                            'nilai' => $nilaiData['nilai'],
                            'created_by' => Auth::id(),
                            'sekolah_id' => $guru->sekolah_id
                        ]);
                        $hasilInput[] = $nilai;
                    }
                } catch (\Exception $e) {
                    $siswa = Siswa::find($nilaiData['siswa_id']);
                    $errors[] = [
                        'siswa' => $siswa ? $siswa->nama : 'Unknown',
                        'message' => "Gagal menyimpan nilai: " . $e->getMessage()
                    ];
                }
            }

            // Jika semua data error, rollback transaksi
            if (count($errors) === count($request->nilai_batch)) {
                DB::rollBack();
                return ResponseBuilder::error(400, "Gagal menyimpan semua data nilai", ['errors' => $errors]);
            }

            DB::commit();

            return ResponseBuilder::success(201, "Berhasil menyimpan data nilai", [
                'nilai_baru' => $hasilInput,
                'nilai_diupdate' => $hasilUpdate,
                'total_success' => count($hasilInput) + count($hasilUpdate),
                'total_error' => count($errors),
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal menyimpan data nilai: " . $e->getMessage());
        }
    }

    public function getSiswaWithTP(Request $request)
    {
        try {
            $guru = Auth::user()->guru;
            
            // Validasi request
            $this->validate($request, [
                'kelas_id' => 'required|exists:kelas,id',
                'cp_id' => 'required|exists:capaian_pembelajaran,id'
            ]);

            // Dapatkan daftar siswa berdasarkan kelas
            $siswa = Siswa::where('kelas_id', $request->kelas_id)
                ->where('sekolah_id', $guru->sekolah_id)
                ->orderBy('nama', 'asc')
                ->get(['id', 'nama']);

            // Dapatkan tujuan pembelajaran berdasarkan CP
            $tujuanPembelajaran = TujuanPembelajaran::with(['capaianPembelajaran.mataPelajaran'])
                ->where('cp_id', $request->cp_id)
                ->whereHas('capaianPembelajaran.mataPelajaran', function($q) use ($guru) {
                    $q->where('guru_id', $guru->id);
                })
                ->get(['id', 'kode_tp', 'deskripsi']);

            // Dapatkan nilai yang sudah ada
            $existingNilai = NilaiSiswa::where('sekolah_id', $guru->sekolah_id)
                ->whereIn('siswa_id', $siswa->pluck('id'))
                ->whereIn('tp_id', $tujuanPembelajaran->pluck('id'))
                ->get()
                ->groupBy('siswa_id');

            // Format response
            $result = $siswa->map(function($s) use ($existingNilai) {
                return [
                    'id' => $s->id,
                    'nama' => $s->nama,
                    'nilai' => isset($existingNilai[$s->id]) ? $existingNilai[$s->id]->pluck('nilai', 'tp_id') : []
                ];
            });

            return ResponseBuilder::success(200, "Berhasil mendapatkan data siswa dan TP", [
                'siswa' => $result,
                'tujuan_pembelajaran' => $tujuanPembelajaran
            ]);

        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }
} 