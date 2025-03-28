<?php

namespace App\Http\Controllers\Admin;

use App\Models\MataPelajaran;
use App\Models\Guru;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Ramsey\Uuid\Uuid;

class MataPelajaranController extends BaseAdminController
{
    public function __construct()
    {
        $this->middleware('autologout');
    }

    public function index()
    {
        try {
            $mapel = MataPelajaran::where('sekolah_id', Auth::user()->sekolah_id)
                ->orderBy('nama_mapel', 'asc')
                ->get();
                
            // Konversi ID ke UUID dan simpan perubahan 
            $mapel->transform(function ($item) {
                if (is_numeric($item->id)) {
                    $oldId = $item->id;
                    $item->id = Uuid::uuid4()->toString();
                    $item->save();
                    \Log::info("ID mata pelajaran dikonversi: {$oldId} -> {$item->id}");    
                }
                return $item;
            });

            return ResponseBuilder::success(200, "Berhasil mendapatkan data mata pelajaran", [
                'mata_pelajaran' => $mapel
            ]);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'kode_mapel' => 'required|string|max:50',
            'nama_mapel' => 'required|string|max:255',
            'tingkat' => 'required|string|max:50',
            'guru_id' => 'required|exists:guru,id'
        ]);

        try {
            DB::beginTransaction();
            
            $admin = Auth::user();
            
            // Debug untuk melihat nilai guru_id
            \Log::info('Guru ID dari request: ' . $request->guru_id);
            
            // Pastikan guru_id ada dan valid
            if (!$request->has('guru_id') || empty($request->guru_id)) {
                return ResponseBuilder::error(400, "Field guru_id wajib diisi");
            }
            
            // Verifikasi guru berada di sekolah yang sama
            $guru = Guru::where('sekolah_id', $admin->sekolah_id)
                ->where('id', $request->guru_id)
                ->first();
                
            if (!$guru) {
                return ResponseBuilder::error(400, "Guru tidak ditemukan atau tidak terdaftar di sekolah ini");
            }
            
            // Buat array data secara eksplisit
            $data = [
                'kode_mapel' => $request->kode_mapel,
                'nama_mapel' => $request->nama_mapel,
                'tingkat' => $request->tingkat,
                'guru_id' => $request->guru_id, // Pastikan nilai ini ada
                'sekolah_id' => $admin->sekolah_id
            ];
            
            // Debug data sebelum insert
            \Log::info('Data untuk insert: ', $data);
            
            // Pastikan semua field yang diperlukan ada di $fillable
            if (!in_array('guru_id', (new MataPelajaran)->getFillable())) {
                return ResponseBuilder::error(500, "Field 'guru_id' tidak dapat diisi karena tidak ada dalam daftar fillable");
            }
            
            $mapel = MataPelajaran::create($data);
            
            DB::commit();
            
            return ResponseBuilder::success(201, "Berhasil menambahkan mata pelajaran", [
                'id' => $mapel->id,
                'kode_mapel' => $mapel->kode_mapel,
                'nama_mapel' => $mapel->nama_mapel,
                'tingkat' => $mapel->tingkat,
                'guru_id' => $mapel->guru_id,
                'sekolah_id' => $mapel->sekolah_id,
                'created_at' => $mapel->created_at,
                'updated_at' => $mapel->updated_at,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Log error untuk debugging
            \Log::error('Error saat menambahkan mata pelajaran: ' . $e->getMessage());
            return ResponseBuilder::error(500, "Gagal menambahkan data: " . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'kode_mapel' => 'required|string|max:50',
            'nama_mapel' => 'required|string|max:255',
            'tingkat' => 'required|string|max:50',
            'guru_id' => 'required|exists:guru,id'
        ]);

        try {
            $mapel = MataPelajaran::where('sekolah_id', Auth::user()->sekolah_id)
                ->find($id);
            
            if (!$mapel) {
                return ResponseBuilder::error(404, "Mata pelajaran tidak ditemukan");
            }
            
            $mapel->update($request->all());
            
            return ResponseBuilder::success(200, "Berhasil mengupdate mata pelajaran", $mapel);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mengupdate data: " . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $mapel = MataPelajaran::where('sekolah_id', Auth::user()->sekolah_id)
                ->find($id);
            
            if (!$mapel) {
                return ResponseBuilder::error(404, "Mata pelajaran tidak ditemukan");
            }
            
            // Cek apakah mapel masih memiliki capaian pembelajaran
            if ($mapel->capaianPembelajaran()->count() > 0) {
                return ResponseBuilder::error(400, "Tidak dapat menghapus mata pelajaran yang masih memiliki capaian pembelajaran");
            }
            
            $mapel->delete();
            
            return ResponseBuilder::success(200, "Berhasil menghapus mata pelajaran");
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal menghapus data: " . $e->getMessage());
        }
    }

    public function storeBatch(Request $request)
    {
        $this->validate($request, [
            'mapel' => 'required|array',
            'mapel.*.kode_mapel' => 'required|string|max:50',
            'mapel.*.nama_mapel' => 'required|string|max:255',
            'mapel.*.tingkat' => 'required|string|max:50',
            'mapel.*.guru_id' => 'required|exists:guru,id'
        ]);

        try {
            DB::beginTransaction();
            
            $sekolahId = Auth::user()->sekolah_id;
            $mapelData = [];
            
            foreach ($request->mapel as $data) {
                $data['sekolah_id'] = $sekolahId;
                $mapel = MataPelajaran::create($data);
                $mapelData[] = $mapel;
            }
            
            DB::commit();
            
            return ResponseBuilder::success(201, "Berhasil menambahkan " . count($mapelData) . " mata pelajaran", $mapelData);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal menambahkan data: " . $e->getMessage());
        }
    }

    public function getTemplate()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set header
            $sheet->setCellValue('A1', 'Kode Mapel');
            $sheet->setCellValue('B1', 'Nama Mapel');
            $sheet->setCellValue('C1', 'Tingkat');
            $sheet->setCellValue('D1', 'ID Guru');
            
            // Contoh data
            $sheet->setCellValue('A2', 'MTK-01');
            $sheet->setCellValue('B2', 'Matematika');
            $sheet->setCellValue('C2', '1');
            $sheet->setCellValue('D2', '[ID Guru]');
            
            $sheet->setCellValue('A3', 'BIN-01');
            $sheet->setCellValue('B3', 'Bahasa Indonesia');
            $sheet->setCellValue('C3', '1');
            $sheet->setCellValue('D3', '[ID Guru]');
            
            // Set style header
            $sheet->getStyle('A1:D1')->getFont()->setBold(true);
            
            // Auto size kolom
            foreach (range('A', 'D') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            // Buat file Excel
            $writer = new Xlsx($spreadsheet);
            $filename = 'template_mapel_' . date('YmdHis') . '.xlsx';
            $path = storage_path('app/public/' . $filename);
            $writer->save($path);
            
            return response()->download($path, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal membuat template: " . $e->getMessage());
        }
    }

    public function import(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|file|mimes:xlsx,xls'
        ]);

        try {
            $file = $request->file('file');
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file->getPathname());
            $spreadsheet = $reader->load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // Hapus header
            array_shift($rows);
            
            DB::beginTransaction();
            
            $sekolahId = Auth::user()->sekolah_id;
            $imported = 0;
            $errors = [];
            $importedData = [];
            
            foreach ($rows as $index => $row) {
                // Skip baris kosong
                if (empty($row[0]) && empty($row[1]) && empty($row[2]) && empty($row[3])) {
                    continue;
                }
                
                $data = [
                    'kode_mapel' => $row[0],
                    'nama_mapel' => $row[1],
                    'tingkat' => $row[2],
                    'guru_id' => $row[3],
                    'sekolah_id' => $sekolahId
                ];
                
                $validator = Validator::make($data, [
                    'kode_mapel' => 'required|string|max:50',
                    'nama_mapel' => 'required|string|max:255',
                    'tingkat' => 'required|string|max:50',
                    'guru_id' => 'required|exists:guru,id',
                    'sekolah_id' => 'required|exists:sekolah,id'
                ]);
                
                if ($validator->fails()) {
                    $errors[] = [
                        'row' => $index + 2,
                        'kode_mapel' => $data['kode_mapel'],
                        'nama_mapel' => $data['nama_mapel'],
                        'errors' => $validator->errors()->all()
                    ];
                    continue;
                }
                
                try {
                    $mapel = MataPelajaran::create($data);
                    $importedData[] = $mapel;
                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = [
                        'row' => $index + 2,
                        'kode_mapel' => $data['kode_mapel'],
                        'nama_mapel' => $data['nama_mapel'],
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            DB::commit();
            
            return ResponseBuilder::success(200, "Berhasil mengimpor $imported mata pelajaran", [
                'imported' => $imported,
                'errors' => $errors,
                'data' => $importedData
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal mengimpor data: " . $e->getMessage());
        }
    }
} 