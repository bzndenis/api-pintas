<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Str;

class GuruController extends Controller
{
    public function __construct()
    {
        // Middleware sudah diterapkan di level route, jadi tidak perlu di sini
        // Atau pastikan konsisten dengan yang di route
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
            
            $guru = $query->orderBy('created_at', 'desc')->get();
            
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
            'password' => 'nullable|string|min:6',
            'no_telp' => 'nullable|string|max:20',
            'mata_pelajaran' => 'nullable|array',
            'mata_pelajaran.*' => 'exists:mata_pelajaran,id'
        ]);

        try {
            DB::beginTransaction();

            $admin = Auth::user();
            
            // Gunakan password default jika tidak ada input password
            $password = $request->password ?? '1234';

            // Buat user account
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($password),
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
                $nama = trim($row[0] ?? '');
                $nip = trim($row[1] ?? '');
                $email = trim($row[2] ?? '');
                $noTelp = trim($row[3] ?? '');
                
                // Validasi email
                if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "Baris $rowNumber: Format email tidak valid";
                    continue;
                }
                
                // Cek apakah email sudah terdaftar
                if (User::where('email', $email)->exists()) {
                    $errors[] = "Baris $rowNumber: Email $email sudah terdaftar";
                    continue;
                }
                
                // Cek apakah NIP sudah terdaftar (jika ada)
                if (!empty($nip) && Guru::where('nip', $nip)->where('sekolah_id', $admin->sekolah_id)->exists()) {
                    $errors[] = "Baris $rowNumber: NIP $nip sudah terdaftar";
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
                    'no_telepon' => $noTelp
                ]);
                
                // Buat data guru
                $guru = Guru::create([
                    'nama' => $nama,
                    'nip' => $nip,
                    'email' => $email,
                    'no_telp' => $noTelp,
                    'user_id' => $user->id,
                    'sekolah_id' => $admin->sekolah_id
                ]);
                
                $importedData[] = [
                    'id' => $guru->id,
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

    public function destroy($id)
    {
        try {
            $admin = Auth::user();
            
            $guru = Guru::where('sekolah_id', $admin->sekolah_id)->find($id);
            
            if (!$guru) {
                return ResponseBuilder::error(404, "Data guru tidak ditemukan");
            }
            
            DB::beginTransaction();
            
            // Cek apakah guru masih memiliki kelas sebagai wali kelas
            if ($guru->kelasWali()->count() > 0) {
                return ResponseBuilder::error(400, "Tidak dapat menghapus guru yang masih menjadi wali kelas");
            }
            
            // Cek apakah guru masih mengajar mata pelajaran
            if ($guru->jadwalMengajar()->count() > 0) {
                return ResponseBuilder::error(400, "Tidak dapat menghapus guru yang masih memiliki jadwal mengajar");
            }
            
            // Hapus user yang terkait jika ada
            if ($guru->user_id) {
                $user = User::find($guru->user_id);
                if ($user) {
                    $user->delete();
                }
            }
            
            // Hapus guru
            $guru->delete();
            
            DB::commit();
            
            return ResponseBuilder::success(200, "Berhasil menghapus data guru");
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal menghapus data: " . $e->getMessage());
        }
    }

    public function resetPassword(Request $request, $id)
    {
        $this->validate($request, [
            'new_password' => 'required|string|min:6'
        ]);

        try {
            $admin = Auth::user();
            
            $guru = Guru::where('sekolah_id', $admin->sekolah_id)->find($id);
            
            if (!$guru) {
                return ResponseBuilder::error(404, "Data guru tidak ditemukan");
            }
            
            // Update password
            $guru->user->update([
                'password' => Hash::make($request->new_password)
            ]);
            
            return ResponseBuilder::success(200, "Berhasil mereset password guru");
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mereset password: " . $e->getMessage());
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

    public function storeBatch(Request $request)
    {
        $this->validate($request, [
            'guru' => 'required|array|min:1',
            'guru.*.nama' => 'required|string|max:255',
            'guru.*.nip' => 'nullable|string|unique:guru,nip',
            'guru.*.email' => 'required|email|unique:users,email',
            'guru.*.no_telp' => 'nullable|string|max:15',
            'guru.*.kelas' => 'nullable|string|max:255'
        ]);

        try {
            $admin = Auth::user();
            \Log::info('Admin yang melakukan import: ', ['id' => $admin->id, 'sekolah_id' => $admin->sekolah_id]);
            
            $guruData = $request->guru;
            $importedData = [];
            $errors = [];
            $imported = 0;
            
            // Nonaktifkan foreign key check sementara
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::beginTransaction();
            
            foreach ($guruData as $index => $data) {
                try {
                    // Log data yang akan diproses
                    \Log::info('Processing guru data: ', $data);
                    
                    // Gunakan password default 1234
                    $password = '1234';
                    
                    // Buat UUID untuk user
                    $userId = (string) Str::uuid();
                    
                    // Buat user langsung dengan DB::table
                    DB::table('users')->insert([
                        'id' => $userId,
                        'email' => $data['email'],
                        'password' => Hash::make($password),
                        'role' => 'guru',
                        'is_active' => true,
                        'sekolah_id' => $admin->sekolah_id,
                        'nama_lengkap' => $data['nama'],
                        'no_telepon' => $data['no_telp'] ?? null,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                    
                    \Log::info('User created with ID: ' . $userId);
                    
                    // Buat UUID untuk guru
                    $guruId = (string) Str::uuid();
                    
                    // Buat data guru langsung dengan DB::table
                    DB::table('guru')->insert([
                        'id' => $guruId,
                        'nama' => $data['nama'],
                        'nip' => $data['nip'] ?? null,
                        'email' => $data['email'],
                        'no_telp' => $data['no_telp'] ?? null,
                        'user_id' => $userId,
                        'sekolah_id' => $admin->sekolah_id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                    
                    \Log::info('Guru created with ID: ' . $guruId);
                    
                    $importedData[] = [
                        'id' => $guruId,
                        'nama' => $data['nama'],
                        'nip' => $data['nip'] ?? null,
                        'email' => $data['email'],
                        'password' => $password // Tampilkan password default
                    ];
                    
                    $imported++;
                } catch (\Exception $e) {
                    \Log::error('Error creating guru: ' . $e->getMessage());
                    \Log::error('Stack trace: ' . $e->getTraceAsString());
                    $errors[] = [
                        'row' => $index + 1,
                        'nama' => $data['nama'] ?? 'Unknown',
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            DB::commit();
            // Aktifkan kembali foreign key check
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            
            return ResponseBuilder::success(200, "Berhasil menambahkan $imported data guru", [
                'imported' => $imported,
                'errors' => $errors,
                'data' => $importedData
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Pastikan foreign key check diaktifkan kembali jika terjadi error
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            
            \Log::error('Batch error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return ResponseBuilder::error(500, "Gagal menambahkan data: " . $e->getMessage());
        }
    }
} 