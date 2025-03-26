<?php

namespace App\Http\Controllers\Admin;

use App\Models\Siswa;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class SiswaController extends BaseAdminController
{
    public function index(Request $request)
    {
        try {
            $query = Siswa::with(['kelas', 'sekolah'])
                ->where('sekolah_id', Auth::user()->sekolah_id);

            if ($request->search) {
                $query->where(function($q) use ($request) {
                    $q->where('nama', 'like', "%{$request->search}%")
                      ->orWhere('nisn', 'like', "%{$request->search}%");
                });
            }

            if ($request->kelas_id) {
                $query->where('kelas_id', $request->kelas_id);
            }

            $siswa = $query->orderBy('created_at', 'desc')->get();

            return ResponseBuilder::success(200, "Berhasil mendapatkan data siswa", $siswa);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'nisn' => 'required|string|unique:siswa,nisn',
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'kelas_id' => 'required|exists:kelas,id'
        ]);

        try {
            $data = $request->all();
            $data['sekolah_id'] = Auth::user()->sekolah_id;

            $siswa = Siswa::create($data);

            return ResponseBuilder::success(201, "Berhasil menambahkan siswa", $siswa);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal menambahkan data: " . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'kelas_id' => 'required|exists:kelas,id'
        ]);

        try {
            $siswa = Siswa::where('sekolah_id', Auth::user()->sekolah_id)
                ->find($id);

            if (!$siswa) {
                return ResponseBuilder::error(404, "Siswa tidak ditemukan");
            }

            $siswa->update($request->all());

            return ResponseBuilder::success(200, "Berhasil mengupdate siswa", $siswa);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mengupdate data: " . $e->getMessage());
        }
    }

    public function import(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|file|mimes:xlsx,xls|max:2048',
            'kelas_id' => 'required|exists:kelas,id'
        ]);

        try {
            DB::beginTransaction();
            
            $file = $request->file('file');
            $kelas_id = $request->kelas_id;
            $sekolah_id = Auth::user()->sekolah_id;
            
            // Baca file Excel
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // Hapus baris header dan contoh
            array_shift($rows); // Hapus header
            array_shift($rows); // Hapus contoh
            array_shift($rows); // Hapus catatan
            
            $response = [
                'total_data' => count($rows),
                'berhasil' => 0,
                'gagal' => 0,
                'errors' => []
            ];
            
            foreach ($rows as $index => $row) {
                try {
                    // Skip baris kosong
                    if (empty($row[0])) continue;
                    
                    // Validasi data minimal
                    if (empty($row[0]) || empty($row[1]) || empty($row[2])) {
                        throw new \Exception("Nama, NISN, dan Jenis Kelamin wajib diisi");
                    }
                    
                    // Validasi jenis kelamin
                    if (!in_array(strtoupper($row[2]), ['L', 'P'])) {
                        throw new \Exception("Jenis kelamin harus L atau P");
                    }
                    
                    // Buat data siswa
                    $siswa = Siswa::create([
                        'nama' => $row[0],
                        'nisn' => $row[1],
                        'jenis_kelamin' => strtoupper($row[2]),
                        'kelas_id' => $kelas_id,
                        'sekolah_id' => $sekolah_id
                    ]);
                    
                    $response['berhasil']++;
                } catch (\Exception $e) {
                    $response['gagal']++;
                    $response['errors'][] = "Baris " . ($index + 4) . ": " . $e->getMessage();
                }
            }
            
            DB::commit();
            return ResponseBuilder::success(200, "Berhasil mengimport data", $response);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal mengimport data: " . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $admin = Auth::user();
            
            $siswa = Siswa::where('sekolah_id', $admin->sekolah_id)->find($id);
            
            if (!$siswa) {
                return ResponseBuilder::error(404, "Data siswa tidak ditemukan");
            }
            
            DB::beginTransaction();
            
            // Cek apakah siswa masih memiliki nilai
            if ($siswa->nilai()->count() > 0) {
                return ResponseBuilder::error(400, "Tidak dapat menghapus siswa yang masih memiliki data nilai");
            }
            
            // Cek apakah siswa masih memiliki absensi
            if ($siswa->absensi()->count() > 0) {
                return ResponseBuilder::error(400, "Tidak dapat menghapus siswa yang masih memiliki data absensi");
            }
            
            // Hapus user yang terkait jika ada
            if ($siswa->user_id) {
                $user = User::find($siswa->user_id);
                if ($user) {
                    $user->delete();
                }
            }
            
            // Hapus siswa
            $siswa->delete();
            
            DB::commit();
            
            return ResponseBuilder::success(200, "Berhasil menghapus data siswa");
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal menghapus data: " . $e->getMessage());
        }
    }

    public function storeBatch(Request $request)
    {
        $this->validate($request, [
            'siswa' => 'required|array|min:1',
            'siswa.*.nisn' => 'required|string|unique:siswa,nisn',
            'siswa.*.nama' => 'required|string|max:255',
            'siswa.*.jenis_kelamin' => 'required|in:L,P',
            'siswa.*.kelas_id' => 'required|exists:kelas,id'
        ]);

        try {
            $admin = Auth::user();
            $siswaData = $request->siswa;
            $importedData = [];
            $errors = [];
            $imported = 0;
            
            DB::beginTransaction();
            
            foreach ($siswaData as $index => $data) {
                try {
                    // Buat data siswa
                    $siswa = Siswa::create([
                        'nisn' => $data['nisn'],
                        'nama' => $data['nama'],
                        'jenis_kelamin' => $data['jenis_kelamin'],
                        'kelas_id' => $data['kelas_id'],
                        'sekolah_id' => $admin->sekolah_id
                    ]);
                    
                    $importedData[] = [
                        'id' => $siswa->id,
                        'nisn' => $data['nisn'],
                        'nama' => $data['nama'],
                        'jenis_kelamin' => $data['jenis_kelamin'],
                        'kelas_id' => $data['kelas_id']
                    ];
                    
                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = [
                        'row' => $index + 1,
                        'nama' => $data['nama'] ?? 'Unknown',
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            DB::commit();
            
            return ResponseBuilder::success(201, "Berhasil menambahkan $imported data siswa", [
                'imported' => $imported,
                'errors' => $errors,
                'data' => $importedData
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal menambahkan data: " . $e->getMessage());
        }
    }

    public function getTemplate()
    {
        try {
            // Buat spreadsheet baru
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set header dengan penjelasan yang jelas
            $sheet->setCellValue('A1', 'Nama');
            $sheet->setCellValue('B1', 'NISN');
            $sheet->setCellValue('C1', 'Jenis Kelamin (L/P)');
            $sheet->setCellValue('D1', 'ID Kelas');
            
            // Contoh data
            $sheet->setCellValue('A2', 'Contoh: Budi Santoso');
            $sheet->setCellValue('B2', 'Contoh: 9876543210');
            $sheet->setCellValue('C2', 'Contoh: L');
            $sheet->setCellValue('D2', 'Contoh: (Isi dengan ID kelas yang valid)');
            
            // Tambahkan catatan di baris ketiga
            $sheet->setCellValue('A3', 'Catatan: Kolom Nama, NISN, Jenis Kelamin, dan ID Kelas wajib diisi');
            $sheet->mergeCells('A3:D3');
            
            // Atur lebar kolom agar lebih mudah dibaca
            $sheet->getColumnDimension('A')->setWidth(25);
            $sheet->getColumnDimension('B')->setWidth(15);
            $sheet->getColumnDimension('C')->setWidth(20);
            $sheet->getColumnDimension('D')->setWidth(30);
            
            // Atur style untuk header
            $headerStyle = [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E0E0E0']
                ]
            ];
            $sheet->getStyle('A1:D1')->applyFromArray($headerStyle);
            
            // Atur style untuk catatan
            $noteStyle = [
                'font' => ['italic' => true, 'color' => ['rgb' => '808080']]
            ];
            $sheet->getStyle('A3:D3')->applyFromArray($noteStyle);
            
            $filename = 'template_import_siswa.xlsx';
            
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
} 