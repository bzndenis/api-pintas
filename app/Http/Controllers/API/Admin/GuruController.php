<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Str;

class GuruController extends Controller
{
    public function __construct()
    {
        $this->middleware('login');
        $this->middleware('admin');
    }

    public function index(Request $request)
    {
        try {
            $admin = Auth::user();
            
            $query = Guru::with(['user'])
                ->where('sekolah_id', $admin->sekolah_id);
            
            // Filter berdasarkan nama
            if ($request->nama) {
                $query->where('nama', 'like', '%' . $request->nama . '%');
            }
            
            // Filter berdasarkan nip
            if ($request->nip) {
                $query->where('nip', 'like', '%' . $request->nip . '%');
            }
            
            // Pagination
            $perPage = $request->per_page ?? 10;
            $guru = $query->orderBy('created_at', 'desc')->paginate($perPage);
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan data guru", $guru);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mengambil data: " . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'nama' => 'required|string|max:255',
            'nip' => 'nullable|string|unique:guru,nip',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'no_telp' => 'nullable|string|max:20',
            'mata_pelajaran' => 'nullable|array',
            'mata_pelajaran.*' => 'exists:mata_pelajaran,id'
        ]);

        try {
            DB::beginTransaction();

            $admin = Auth::user();

            // Buat user account
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'guru',
                'is_active' => true,
                'sekolah_id' => $admin->sekolah_id,
                'nama_lengkap' => $request->nama,
                'no_telepon' => $request->no_telp
            ]);

            // Buat data guru
            $guru = Guru::create([
                'nama' => $request->nama,
                'nip' => $request->nip,
                'email' => $request->email,
                'no_telp' => $request->no_telp,
                'user_id' => $user->id,
                'sekolah_id' => $admin->sekolah_id
            ]);

            // Assign mata pelajaran jika ada
            if ($request->has('mata_pelajaran') && is_array($request->mata_pelajaran)) {
                $guru->mataPelajaran()->attach($request->mata_pelajaran);
            }

            DB::commit();

            $guru->load(['user', 'mataPelajaran']);

            return ResponseBuilder::success(201, "Berhasil membuat akun guru", $guru);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal membuat akun: " . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $admin = Auth::user();
            
            $guru = Guru::with(['user', 'mataPelajaran'])
                ->where('sekolah_id', $admin->sekolah_id)
                ->find($id);
            
            if (!$guru) {
                return ResponseBuilder::error(404, "Data guru tidak ditemukan");
            }
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan detail guru", $guru);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'nama' => 'required|string|max:255',
            'nip' => 'nullable|string|unique:guru,nip,' . $id,
            'email' => 'required|email|unique:users,email,' . $id . ',id',
            'no_telp' => 'nullable|string|max:20',
            'mata_pelajaran' => 'nullable|array',
            'mata_pelajaran.*' => 'exists:mata_pelajaran,id',
            'is_active' => 'nullable|boolean'
        ]);

        try {
            DB::beginTransaction();

            $admin = Auth::user();
            
            $guru = Guru::where('sekolah_id', $admin->sekolah_id)->find($id);
            
            if (!$guru) {
                return ResponseBuilder::error(404, "Data guru tidak ditemukan");
            }
            
            // Update data guru
            $guru->update([
                'nama' => $request->nama,
                'nip' => $request->nip,
                'email' => $request->email,
                'no_telp' => $request->no_telp
            ]);
            
            // Update data user
            $guru->user->update([
                'email' => $request->email,
                'nama_lengkap' => $request->nama,
                'no_telepon' => $request->no_telp,
                'is_active' => $request->has('is_active') ? $request->is_active : $guru->user->is_active
            ]);
            
            // Update mata pelajaran jika ada
            if ($request->has('mata_pelajaran')) {
                $guru->mataPelajaran()->sync($request->mata_pelajaran);
            }
            
            DB::commit();
            
            $guru->load(['user', 'mataPelajaran']);
            
            return ResponseBuilder::success(200, "Berhasil mengupdate data guru", $guru);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal mengupdate data: " . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $admin = Auth::user();
            
            $guru = Guru::where('sekolah_id', $admin->sekolah_id)->find($id);
            
            if (!$guru) {
                return ResponseBuilder::error(404, "Data guru tidak ditemukan");
            }
            
            DB::beginTransaction();
            
            // Cek apakah guru masih memiliki kelas
            if ($guru->kelas()->count() > 0) {
                return ResponseBuilder::error(400, "Tidak dapat menghapus guru yang masih mengajar kelas");
            }
            
            // Hapus data guru dan user terkait
            if ($guru->user_id) {
                $user = User::find($guru->user_id);
                if ($user) {
                    $user->delete();
                }
            } else {
                // Hapus guru jika tidak terkait dengan user
                $guru->delete();
            }
            
            DB::commit();
            
            return ResponseBuilder::success(200, "Berhasil menghapus data guru");
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal menghapus data: " . $e->getMessage());
        }
    }

    public function import(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        try {
            DB::beginTransaction();
            
            $admin = Auth::user();
            $file = $request->file('file');
            
            // Baca file Excel
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // Hapus header
            array_shift($rows);
            
            $imported = 0;
            $errors = [];
            $importedData = [];
            
            foreach ($rows as $index => $row) {
                // Validasi data
                if (empty($row[0]) || empty($row[2])) {
                    $errors[] = "Baris " . ($index + 2) . ": Nama dan Email wajib diisi";
                    continue;
                }
                
                $nama = $row[0];
                $nip = $row[1] ?? null;
                $email = $row[2];
                $no_telp = $row[3] ?? null;
                
                // Cek apakah email sudah terdaftar
                if (User::where('email', $email)->exists()) {
                    $errors[] = "Baris " . ($index + 2) . ": Email $email sudah terdaftar";
                    continue;
                }
                
                // Cek apakah NIP sudah terdaftar (jika ada)
                if ($nip && Guru::where('nip', $nip)->where('sekolah_id', $admin->sekolah_id)->exists()) {
                    $errors[] = "Baris " . ($index + 2) . ": NIP $nip sudah terdaftar";
                    continue;
                }
                
                // Generate password
                $password = Str::random(8);
                
                // Buat user account
                $user = User::create([
                    'email' => $email,
                    'password' => Hash::make($password),
                    'role' => 'guru',
                    'is_active' => true,
                    'sekolah_id' => $admin->sekolah_id,
                    'nama_lengkap' => $nama,
                    'no_telepon' => $no_telp
                ]);
                
                // Buat data guru
                $guru = Guru::create([
                    'nama' => $nama,
                    'nip' => $nip,
                    'email' => $email,
                    'no_telp' => $no_telp,
                    'user_id' => $user->id,
                    'sekolah_id' => $admin->sekolah_id
                ]);
                
                $importedData[] = [
                    'nama' => $nama,
                    'nip' => $nip,
                    'email' => $email,
                    'password' => $password // Tampilkan password untuk diberikan ke guru
                ];
                
                $imported++;
            }
            
            DB::commit();
            
            return ResponseBuilder::success(200, "Berhasil mengimpor $imported data guru", [
                'imported' => $imported,
                'errors' => $errors,
                'data' => $importedData
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal mengimpor data: " . $e->getMessage());
        }
    }

    public function getTemplate()
    {
        try {
            // Buat spreadsheet baru
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set header
            $sheet->setCellValue('A1', 'Nama');
            $sheet->setCellValue('B1', 'NIP');
            $sheet->setCellValue('C1', 'Email');
            $sheet->setCellValue('D1', 'No. Telepon');
            
            // Contoh data
            $sheet->setCellValue('A2', 'Contoh: Budi Santoso');
            $sheet->setCellValue('B2', 'Contoh: 198501152010011001');
            $sheet->setCellValue('C2', 'Contoh: budi@example.com');
            $sheet->setCellValue('D2', 'Contoh: 081234567890');
            
            // Simpan file
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $filename = 'template_import_guru.xlsx';
            $path = storage_path('app/public/templates/' . $filename);
            
            // Buat direktori jika belum ada
            if (!file_exists(storage_path('app/public/templates'))) {
                mkdir(storage_path('app/public/templates'), 0755, true);
            }
            
            $writer->save($path);
            
            return ResponseBuilder::success(200, "Berhasil membuat template", [
                'file_url' => url('storage/templates/' . $filename)
            ]);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal membuat template: " . $e->getMessage());
        }
    }
} 