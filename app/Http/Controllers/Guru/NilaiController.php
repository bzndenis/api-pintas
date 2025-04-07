<?php

namespace App\Http\Controllers\Guru;

use App\Models\NilaiSiswa;
use App\Models\Siswa;
use App\Models\TujuanPembelajaran;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Uuid;
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
            
            // Set judul sheet
            $sheet->setTitle('Template Nilai');
            
            // Header informasi
            $sheet->setCellValue('A1', 'TEMPLATE INPUT NILAI SISWA');
            $sheet->setCellValue('A2', 'Kelas: ' . Kelas::find($request->kelas_id)->nama_kelas);
            $sheet->setCellValue('A3', 'Mata Pelajaran: ' . $cp->mataPelajaran->nama);
            $sheet->setCellValue('A4', 'Capaian Pembelajaran: ' . $cp->deskripsi);
            
            // Merge cells untuk header informasi
            $sheet->mergeCells('A1:E1');
            $sheet->mergeCells('A2:E2');
            $sheet->mergeCells('A3:E3');
            $sheet->mergeCells('A4:E4');
            
            // Header tabel
            $sheet->setCellValue('A6', 'NISN');
            $sheet->setCellValue('B6', 'Nama Siswa');
            $sheet->setCellValue('C6', 'ID Siswa');
            $sheet->setCellValue('D6', 'ID TP');
            $sheet->setCellValue('E6', 'Nilai');
            
            // Style untuk header
            $headerStyle = [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E0E0E0']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
                ]
            ];
            $sheet->getStyle('A1:E6')->applyFromArray($headerStyle);
            
            // Isi data
            $row = 7;
            foreach ($siswa as $s) {
                foreach ($tujuanPembelajaran as $tp) {
                    $sheet->setCellValue('A' . $row, $s->nisn);
                    $sheet->setCellValue('B' . $row, $s->nama);
                    $sheet->setCellValue('C' . $row, $s->id);
                    $sheet->setCellValue('D' . $row, $tp->id);
                    $row++;
                }
            }
            
            // Tambahkan catatan
            $row += 2;
            $sheet->setCellValue('A' . $row, 'Catatan:');
            $row++;
            $sheet->setCellValue('A' . $row, '1. Jangan mengubah ID Siswa dan ID TP');
            $row++;
            $sheet->setCellValue('A' . $row, '2. Nilai harus diisi dengan angka 0-100');
            $row++;
            $sheet->setCellValue('A' . $row, '3. File yang diunggah harus dalam format Excel (.xlsx atau .xls)');
            
            // Auto-size kolom
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
} 