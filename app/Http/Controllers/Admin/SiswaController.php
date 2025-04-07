<?php

namespace App\Http\Controllers\Admin;

use App\Models\Siswa;
use App\Models\Kelas;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;


class SiswaController extends BaseAdminController
{
    public function __construct()
    {
        $this->middleware('autologout');
    }

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
        ]);

        try {
            DB::beginTransaction();
            
            $admin = Auth::user();
            $file = $request->file('file');
            
            // Load spreadsheet
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // Hapus header (baris pertama)
            array_shift($rows);
            
            $imported = 0;
            $errors = [];
            $importedData = [];
            
            foreach ($rows as $index => $row) {
                // Skip baris kosong
                if (empty($row[0]) && empty($row[1]) && empty($row[2])) {
                    continue;
                }
                
                $rowNumber = $index + 2; // +2 karena index dimulai dari 0 dan header di baris 1
                
                // Validasi data
                $nisn = trim($row[0] ?? '');
                $nama = trim($row[1] ?? '');
                $jenisKelamin = trim($row[2] ?? '');
                $kelasId = trim($row[3] ?? '');
                
                // Validasi NISN
                if (empty($nisn)) {
                    $errors[] = "Baris $rowNumber: NISN tidak boleh kosong";
                    continue;
                }
                
                // Cek apakah NISN sudah terdaftar
                if (Siswa::where('nisn', $nisn)->exists()) {
                    $errors[] = "Baris $rowNumber: NISN $nisn sudah terdaftar";
                    continue;
                }
                
                // Validasi jenis kelamin
                if (empty($jenisKelamin) || !in_array($jenisKelamin, ['L', 'P'])) {
                    $errors[] = "Baris $rowNumber: Jenis kelamin harus L atau P";
                    continue;
                }
                
                // Validasi kelas
                if (empty($kelasId)) {
                    $errors[] = "Baris $rowNumber: ID Kelas tidak boleh kosong";
                    continue;
                }
                
                // Cek apakah kelas ada
                $kelas = Kelas::where('id', $kelasId)->where('sekolah_id', $admin->sekolah_id)->first();
                if (!$kelas) {
                    $errors[] = "Baris $rowNumber: Kelas dengan ID $kelasId tidak ditemukan";
                    continue;
                }
                
                // Buat data siswa
                $siswa = Siswa::create([
                    'nisn' => $nisn,
                    'nama' => $nama,
                    'jenis_kelamin' => $jenisKelamin,
                    'kelas_id' => $kelasId,
                    'sekolah_id' => $admin->sekolah_id
                ]);
                
                $importedData[] = [
                    'id' => $siswa->id,
                    'nisn' => $nisn,
                    'nama' => $nama,
                    'jenis_kelamin' => $jenisKelamin,
                    'kelas_id' => $kelasId
                ];
                
                $imported++;
            }
            
            DB::commit();
            
            return ResponseBuilder::success(200, "Berhasil mengimpor $imported data siswa", [
                'imported' => $imported,
                'errors' => $errors,
                'data' => $importedData
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal mengimpor data: " . $e->getMessage());
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
            $sheet->setCellValue('A1', 'nisn');
            $sheet->setCellValue('B1', 'nama');
            $sheet->setCellValue('C1', 'jenis_kelamin');
            $sheet->setCellValue('D1', 'kelas_id');
            
            // Contoh data
            $sheet->setCellValue('A2', 'Contoh: 1234567890');
            $sheet->setCellValue('B2', 'Contoh: Budi Santoso');
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