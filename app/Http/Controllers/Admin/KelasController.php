<?php

namespace App\Http\Controllers\Admin;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Guru;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Carbon\Carbon;

class KelasController extends BaseAdminController
{
    public function __construct()
    {
        $this->middleware('autologout');
    }

    public function index(Request $request)
    {
        try {
            $query = Kelas::with(['tahunAjaran', 'waliKelas', 'siswa'])
                ->where('sekolah_id', Auth::user()->sekolah_id);

            if ($request->tahun_ajaran_id) {
                $query->where('tahun_ajaran_id', $request->tahun_ajaran_id);
            }

            if ($request->tingkat) {
                $query->where('tingkat', $request->tingkat);
            }

            $kelas = $query->orderBy('nama_kelas', 'asc')->get();

            return ResponseBuilder::success(200, "Berhasil mendapatkan data kelas", $kelas);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'nama_kelas' => 'required|string|max:255',
            'tingkat' => 'required|string|max:255',
            'tahun' => 'required',
            'guru_id' => 'required|exists:guru,id'
        ]);

        try {
            $admin = Auth::user();
            $data = $request->all();
            $data['sekolah_id'] = $admin->sekolah_id;

            // Cek apakah kombinasi nama_kelas, tahun, dan sekolah_id sudah ada
            $existingKelas = Kelas::where('nama_kelas', $data['nama_kelas'])
                ->where('tahun', $data['tahun'])
                ->where('tingkat', $data['tingkat'])
                ->where('sekolah_id', $admin->sekolah_id)
                ->first();

            if ($existingKelas) {
                return ResponseBuilder::error(400, "Kelas dengan nama, tingkat, dan tahun yang sama sudah ada");
            }

            $kelas = Kelas::create($data);

            return ResponseBuilder::success(201, "Berhasil menambahkan kelas", $kelas);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal menambahkan data: " . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'nama_kelas' => 'required|string|max:255',
            'tingkat' => 'required|string|max:255',
            'tahun' => 'required',
            'guru_id' => 'required|exists:guru,id'
        ]);

        try {
            $admin = Auth::user();
            $kelas = Kelas::where('sekolah_id', $admin->sekolah_id)->find($id);

            if (!$kelas) {
                return ResponseBuilder::error(404, "Kelas tidak ditemukan");
            }

            $data = $request->all();

            // Cek apakah kombinasi nama_kelas, tahun, dan sekolah_id sudah ada (selain kelas ini sendiri)
            $existingKelas = Kelas::where('nama_kelas', $data['nama_kelas'])
                ->where('tahun', $data['tahun'])
                ->where('tingkat', $data['tingkat'])
                ->where('sekolah_id', $admin->sekolah_id)
                ->where('id', '!=', $id)
                ->first();

            if ($existingKelas) {
                return ResponseBuilder::error(400, "Kelas dengan nama, tingkat, dan tahun yang sama sudah ada");
            }

            $kelas->update($data);

            return ResponseBuilder::success(200, "Berhasil mengupdate kelas", $kelas);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mengupdate data: " . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $kelas = Kelas::where('sekolah_id', Auth::user()->sekolah_id)->find($id);

            if (!$kelas) {
                return ResponseBuilder::error(404, "Kelas tidak ditemukan");
            }

            // Cek apakah kelas masih memiliki siswa
            if ($kelas->siswa()->count() > 0) {
                return ResponseBuilder::error(400, "Tidak dapat menghapus kelas yang masih memiliki siswa");
            }

            $kelas->delete();

            return ResponseBuilder::success(200, "Berhasil menghapus kelas");
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal menghapus data: " . $e->getMessage());
        }
    }

    public function detail($id)
    {
        try {
            $kelas = Kelas::with(['tahunAjaran', 'waliKelas', 'siswa'])
                ->where('sekolah_id', Auth::user()->sekolah_id)
                ->find($id);

            if (!$kelas) {
                return ResponseBuilder::error(404, "Kelas tidak ditemukan");
            }

            return ResponseBuilder::success(200, "Berhasil mendapatkan detail kelas", $kelas);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }

    public function assignGuru(Request $request, $id)
    {
        $this->validate($request, [
            'guru_id' => 'required|exists:guru,id'
        ]);

        try {
            $kelas = Kelas::where('sekolah_id', Auth::user()->sekolah_id)->find($id);

            if (!$kelas) {
                return ResponseBuilder::error(404, "Kelas tidak ditemukan");
            }

            $guru = Guru::where('sekolah_id', Auth::user()->sekolah_id)
                ->find($request->guru_id);

            if (!$guru) {
                return ResponseBuilder::error(404, "Guru tidak ditemukan");
            }

            $kelas->guru_id = $guru->id;
            $kelas->save();

            return ResponseBuilder::success(200, "Berhasil menetapkan guru ke kelas", $kelas);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal menetapkan guru: " . $e->getMessage());
        }
    }

    public function assignSiswa(Request $request, $id)
    {
        $this->validate($request, [
            'siswa_id' => 'required|exists:siswa,id'
        ]);

        try {
            DB::beginTransaction();

            $kelas = Kelas::where('sekolah_id', Auth::user()->sekolah_id)->find($id);

            if (!$kelas) {
                return ResponseBuilder::error(404, "Kelas tidak ditemukan");
            }

            // Update kelas_id untuk semua siswa yang dipilih
            Siswa::where('id', $request->siswa_id)
                ->where('sekolah_id', Auth::user()->sekolah_id)
                ->update(['kelas_id' => $kelas->id]);

            DB::commit();

            return ResponseBuilder::success(200, "Berhasil menetapkan siswa ke kelas", [
                'kelas' => $kelas,
                'siswa_count' => 1
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal menetapkan siswa: " . $e->getMessage());
        }
    }

    public function getTemplate()
    {
        try {
            // Buat spreadsheet baru
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set header dengan penjelasan yang lebih jelas
            $sheet->setCellValue('A1', 'Nama Kelas');
            $sheet->setCellValue('B1', 'Tingkat');
            $sheet->setCellValue('C1', 'Tahun');
            $sheet->setCellValue('D1', 'ID Guru (Wali Kelas)');

            // Contoh data
            $sheet->setCellValue('A2', 'X IPA 1');
            $sheet->setCellValue('B2', '10');
            $sheet->setCellValue('C2', '2024');
            $sheet->setCellValue('D2', '[ID Guru]');

            // Atur style untuk header
            $headerStyle = [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E0E0E0']
                ]
            ];
            $sheet->getStyle('A1:D1')->applyFromArray($headerStyle);

            // Atur lebar kolom agar lebih mudah dibaca
            $sheet->getColumnDimension('A')->setWidth(25);
            $sheet->getColumnDimension('B')->setWidth(15);
            $sheet->getColumnDimension('C')->setWidth(15);
            $sheet->getColumnDimension('D')->setWidth(25);

            $filename = 'template_kelas_' . date('YmdHis') . '.xlsx';

            // Kembalikan file sebagai stream
            return response()->streamDownload(function () use ($spreadsheet) {
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                $writer->save('php://output');
            }, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal membuat template: " . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        try {
            $query = Kelas::with(['waliKelas', 'siswa'])
                ->where('sekolah_id', Auth::user()->sekolah_id);

            // Filter berdasarkan tahun
            if ($request->tahun) {
                $query->where('tahun', $request->tahun);
            }

            // Filter berdasarkan tingkat
            if ($request->tingkat) {
                $query->where('tingkat', $request->tingkat);
            }

            $kelas = $query->orderBy('nama_kelas', 'asc')->get();

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set header
            $sheet->setCellValue('A1', 'No');
            $sheet->setCellValue('B1', 'Nama Kelas');
            $sheet->setCellValue('C1', 'Tingkat');
            $sheet->setCellValue('D1', 'Tahun');
            $sheet->setCellValue('E1', 'Wali Kelas');
            $sheet->setCellValue('F1', 'Jumlah Siswa');

            // Set style header
            $headerStyle = [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E0E0E0']
                ]
            ];
            $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);

            // Isi data
            $row = 2;
            foreach ($kelas as $index => $item) {
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $item->nama_kelas);
                $sheet->setCellValue('C' . $row, $item->tingkat);
                $sheet->setCellValue('D' . $row, $item->tahun);
                $sheet->setCellValue('E' . $row, $item->waliKelas ? $item->waliKelas->nama : '-');
                $sheet->setCellValue('F' . $row, $item->siswa->count());
                $row++;
            }

            // Auto size kolom
            foreach (range('A', 'F') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $filename = 'data_kelas_' . date('YmdHis') . '.xlsx';

            // Kembalikan file sebagai stream
            return response()->streamDownload(function () use ($spreadsheet) {
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                $writer->save('php://output');
            }, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mengekspor data: " . $e->getMessage());
        }
    }

    public function import(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            $admin = Auth::user();
            $file = $request->file('file');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            DB::beginTransaction();

            $imported = 0;
            $errors = [];
            $importedData = [];

            // Debug info
            \Log::info('Starting import process', [
                'admin_id' => $admin->id,
                'sekolah_id' => $admin->sekolah_id,
                'total_rows' => count($rows)
            ]);

            foreach ($rows as $index => $row) {
                if ($index == 0) {
                    // Skip header row
                    continue;
                }

                // Skip baris kosong
                if (empty($row[0]) && empty($row[1]) && empty($row[2])) {
                    continue;
                }

                try {
                    $nama_kelas = trim($row[0] ?? '');
                    $tingkat = trim($row[1] ?? '');
                    $tahun = trim($row[2] ?? '');
                    $guru_id = !empty($row[3]) ? trim($row[3]) : null;

                    // Debug data row
                    \Log::info('Processing row', [
                        'row_number' => $index + 1,
                        'nama_kelas' => $nama_kelas,
                        'tingkat' => $tingkat,
                        'tahun' => $tahun,
                        'guru_id' => $guru_id
                    ]);

                    // Validasi data wajib
                    if (empty($nama_kelas) || empty($tingkat) || empty($tahun)) {
                        $errors[] = [
                            'row' => $index + 2,
                            'error' => 'Nama kelas, tingkat, dan tahun tidak boleh kosong'
                        ];
                        continue;
                    }

                    // Cek guru hanya jika guru_id diisi
                    if (!empty($guru_id)) {
                        $guru = Guru::where('id', $guru_id)
                            ->where('sekolah_id', $admin->sekolah_id)
                            ->first();
                        
                        \Log::info('Checking guru', [
                            'guru_id' => $guru_id,
                            'found' => !is_null($guru),
                            'sekolah_id' => $admin->sekolah_id
                        ]);
                        
                        if (!$guru) {
                            $errors[] = [
                                'row' => $index + 2,
                                'error' => "Guru dengan ID $guru_id tidak ditemukan di sekolah ini"
                            ];
                            continue;
                        }
                    }

                    // Buat atau update data kelas
                    $kelas = Kelas::updateOrCreate(
                        [
                            'nama_kelas' => $nama_kelas,
                            'tahun' => $tahun,
                            'sekolah_id' => $admin->sekolah_id
                        ],
                        [
                            'tingkat' => $tingkat,
                            'guru_id' => $guru_id // Bisa null jika tidak ada guru
                        ]
                    );

                    \Log::info('Kelas created/updated', [
                        'kelas_id' => $kelas->id,
                        'nama_kelas' => $kelas->nama_kelas
                    ]);

                    $importedData[] = $kelas;
                    $imported++;

                } catch (\Exception $e) {
                    \Log::error('Error processing row', [
                        'row' => $index + 2,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);

                    $errors[] = [
                        'row' => $index + 2,
                        'error' => $e->getMessage()
                    ];
                }
            }

            DB::commit();

            \Log::info('Import completed', [
                'imported' => $imported,
                'errors' => count($errors)
            ]);

            return ResponseBuilder::success(200, "Berhasil mengimport $imported data kelas", [
                'imported' => $imported,
                'errors' => $errors,
                'data' => $importedData
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return ResponseBuilder::error(500, "Gagal mengimport data: " . $e->getMessage());
        }
    }

    public function storeBatch(Request $request)
    {
        $this->validate($request, [
            'kelas' => 'required|array|min:1',
            'kelas.*.nama_kelas' => 'required|string|max:255',
            'kelas.*.tingkat' => 'required|string|max:255',
            'kelas.*.tahun' => 'required',
            'kelas.*.guru_id' => 'nullable|string|uuid'
        ]);

        try {
            $admin = Auth::user();
            \Log::info('Admin yang melakukan batch import kelas: ', ['id' => $admin->id, 'sekolah_id' => $admin->sekolah_id]);
            
            $kelasData = $request->kelas;
            $importedData = [];
            $errors = [];
            $imported = 0;
            
            DB::beginTransaction();
            
            foreach ($kelasData as $index => $data) {
                try {
                    // Log data yang akan diproses
                    \Log::info('Processing kelas data: ', $data);
                    
                    // Validasi guru_id jika ada
                    if (!empty($data['guru_id'])) {
                        $guru = Guru::where('id', $data['guru_id'])
                            ->where('sekolah_id', $admin->sekolah_id)
                            ->first();
                        
                        if (!$guru) {
                            $errors[] = [
                                'row' => $index + 1,
                                'nama_kelas' => $data['nama_kelas'],
                                'error' => "Guru dengan ID {$data['guru_id']} tidak ditemukan"
                            ];
                            continue;
                        }
                    }
                    
                    // Cek apakah kombinasi nama_kelas, tahun, dan sekolah_id sudah ada
                    $existingKelas = Kelas::where('nama_kelas', $data['nama_kelas'])
                        ->where('tahun', $data['tahun'])
                        ->where('sekolah_id', $admin->sekolah_id)
                        ->first();
                    
                    if ($existingKelas) {
                        // Update kelas yang sudah ada
                        $existingKelas->update([
                            'tingkat' => $data['tingkat'],
                            'guru_id' => $data['guru_id'] ?? null
                        ]);
                        
                        $importedData[] = $existingKelas;
                        $imported++;
                        \Log::info('Kelas updated: ', ['id' => $existingKelas->id, 'nama_kelas' => $existingKelas->nama_kelas]);
                    } else {
                        // Buat kelas baru
                        $kelas = Kelas::create([
                            'nama_kelas' => $data['nama_kelas'],
                            'tingkat' => $data['tingkat'],
                            'tahun' => $data['tahun'],
                            'guru_id' => $data['guru_id'] ?? null,
                            'sekolah_id' => $admin->sekolah_id
                        ]);
                        
                        $importedData[] = $kelas;
                        $imported++;
                        \Log::info('Kelas created: ', ['id' => $kelas->id, 'nama_kelas' => $kelas->nama_kelas]);
                    }
                } catch (\Exception $e) {
                    \Log::error('Error creating kelas: ' . $e->getMessage());
                    \Log::error('Stack trace: ' . $e->getTraceAsString());
                    $errors[] = [
                        'row' => $index + 1,
                        'nama_kelas' => $data['nama_kelas'] ?? 'Unknown',
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            DB::commit();
            
            return ResponseBuilder::success(200, "Berhasil menambahkan $imported data kelas", [
                'imported' => $imported,
                'errors' => $errors,
                'data' => $importedData
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Batch error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return ResponseBuilder::error(500, "Gagal menambahkan data: " . $e->getMessage());
        }
    }
}