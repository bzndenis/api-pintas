<?php

namespace App\Http\Controllers\Admin;

use App\Models\MataPelajaran;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MataPelajaranController extends BaseAdminController
{
    public function index()
    {
        try {
            // Ambil data mata pelajaran berdasarkan sekolah_id user yang login
            $mapel = MataPelajaran::where('sekolah_id', Auth::user()->sekolah_id)
                ->get();
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan data mata pelajaran", $mapel);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'nama_mapel' => 'required|string|max:255',
            'kode_mapel' => 'required|string|max:50',
            'guru_id' => 'required|string|uuid|exists:guru,id'
        ]);

        try {
            $data = $request->all();
            $data['sekolah_id'] = Auth::user()->sekolah_id;
            
            $mapel = MataPelajaran::create($data);
            
            return ResponseBuilder::success(201, "Berhasil menambahkan mata pelajaran", $mapel);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal menambahkan data: " . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'nama_mapel' => 'required|string|max:255',
            'kode_mapel' => 'required|string|max:50',
            'guru_id' => 'required|string|uuid|exists:guru,id'
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
            $admin = Auth::user();
            
            $mapel = MataPelajaran::where('sekolah_id', $admin->sekolah_id)->find($id);
            
            if (!$mapel) {
                return ResponseBuilder::error(404, "Data mata pelajaran tidak ditemukan");
            }
            
            DB::beginTransaction();
            
            // Hapus mata pelajaran tanpa pengecekan relasi guru
            // karena method guru() tidak tersedia
            
            // Cek apakah mata pelajaran masih digunakan oleh capaian pembelajaran
            if (method_exists($mapel, 'capaianPembelajaran') && $mapel->capaianPembelajaran()->count() > 0) {
                return ResponseBuilder::error(400, "Tidak dapat menghapus mata pelajaran yang masih memiliki capaian pembelajaran");
            }
            
            // Hapus mata pelajaran
            $mapel->delete();
            
            DB::commit();
            
            return ResponseBuilder::success(200, "Berhasil menghapus data mata pelajaran");
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal menghapus data: " . $e->getMessage());
        }
    }

    public function storeBatch(Request $request)
    {
        $this->validate($request, [
            'mapel' => 'required|array|min:1',
            'mapel.*.nama' => 'required|string|max:255',
            'mapel.*.kode' => 'required|string|max:50',
            'mapel.*.tingkat' => 'required|string|max:50',
            'mapel.*.guru_id' => 'nullable|string|uuid|exists:guru,id'
        ]);

        try {
            $admin = Auth::user();
            \Log::info('Admin yang melakukan import: ', ['id' => $admin->id, 'sekolah_id' => $admin->sekolah_id]);
            
            $mapelData = $request->mapel;
            $importedData = [];
            $errors = [];
            $imported = 0;
            
            // Nonaktifkan foreign key check sementara
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::beginTransaction();
            
            foreach ($mapelData as $index => $data) {
                try {
                    // Log data yang akan diproses
                    \Log::info('Processing mapel data: ', $data);
                    
                    // Buat UUID untuk mata pelajaran
                    $mapelId = (string) Str::uuid();
                    
                    // Cek struktur tabel mata_pelajaran
                    $tableColumns = DB::getSchemaBuilder()->getColumnListing('mata_pelajaran');
                    \Log::info('Kolom tabel mata_pelajaran: ', $tableColumns);
                    
                    // Buat data mata pelajaran langsung dengan DB::table
                    // Sesuaikan dengan struktur tabel yang benar
                    $insertData = [
                        'id' => $mapelId,
                        'nama_mapel' => $data['nama'],
                        'kode_mapel' => $data['kode'],
                        'tingkat' => $data['tingkat'],
                        'sekolah_id' => $admin->sekolah_id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ];
                    
                    // Jika kolom guru_id ada di tabel, tambahkan ke data
                    if (in_array('guru_id', $tableColumns)) {
                        $insertData['guru_id'] = $data['guru_id'] ?? null;
                    }
                    
                    DB::table('mata_pelajaran')->insert($insertData);
                    
                    \Log::info('Mata pelajaran created with ID: ' . $mapelId);
                    
                    $importedData[] = [
                        'id' => $mapelId,
                        'nama_mapel' => $data['nama'],
                        'kode_mapel' => $data['kode'],
                        'tingkat' => $data['tingkat']
                    ];
                    
                    // Jika kolom guru_id ada di tabel, tambahkan ke data hasil
                    if (in_array('guru_id', $tableColumns)) {
                        $importedData[count($importedData) - 1]['guru_id'] = $data['guru_id'] ?? null;
                    }
                    
                    $imported++;
                } catch (\Exception $e) {
                    \Log::error('Error creating mapel: ' . $e->getMessage());
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
            
            return ResponseBuilder::success(200, "Berhasil menambahkan $imported data mata pelajaran", [
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