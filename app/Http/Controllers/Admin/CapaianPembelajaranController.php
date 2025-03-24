<?php

namespace App\Http\Controllers\Admin;

use App\Models\CapaianPembelajaran;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CapaianPembelajaranController extends BaseAdminController
{
    public function index()
    {
        try {
            $cp = CapaianPembelajaran::where('sekolah_id', Auth::user()->sekolah_id)
                ->with(['mataPelajaran', 'tujuanPembelajaran'])
                ->get();
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan data capaian pembelajaran", $cp);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'kode_cp' => 'required|string|max:50',
            'deskripsi' => 'required|string',
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id'
        ]);

        try {
            $data = $request->all();
            $data['sekolah_id'] = Auth::user()->sekolah_id;
            $data['mapel_id'] = $request->mata_pelajaran_id; // Menambahkan mapel_id dari mata_pelajaran_id
            
            $cp = CapaianPembelajaran::create($data);
            
            return ResponseBuilder::success(201, "Berhasil menambahkan capaian pembelajaran", $cp);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal menambahkan data: " . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'kode_cp' => 'required|string|max:50',
            'deskripsi' => 'required|string',
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id'
        ]);

        try {
            $cp = CapaianPembelajaran::where('sekolah_id', Auth::user()->sekolah_id)
                ->find($id);
            
            if (!$cp) {
                return ResponseBuilder::error(404, "Capaian pembelajaran tidak ditemukan");
            }
            
            $data = $request->all();
            $data['mapel_id'] = $request->mata_pelajaran_id; // Menambahkan mapel_id dari mata_pelajaran_id
            
            $cp->update($data);
            
            return ResponseBuilder::success(200, "Berhasil mengupdate capaian pembelajaran", $cp);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mengupdate data: " . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $admin = Auth::user();
            
            $cp = CapaianPembelajaran::whereHas('mataPelajaran', function($q) use ($admin) {
                $q->where('sekolah_id', $admin->sekolah_id);
            })->find($id);
            
            if (!$cp) {
                return ResponseBuilder::error(404, "Data capaian pembelajaran tidak ditemukan");
            }
            
            DB::beginTransaction();
            
            // Cek apakah capaian pembelajaran masih digunakan oleh tujuan pembelajaran
            if ($cp->tujuanPembelajaran()->count() > 0) {
                return ResponseBuilder::error(400, "Tidak dapat menghapus capaian pembelajaran yang masih memiliki tujuan pembelajaran");
            }
            
            // Hapus capaian pembelajaran
            $cp->delete();
            
            DB::commit();
            
            return ResponseBuilder::success(200, "Berhasil menghapus data capaian pembelajaran");
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal menghapus data: " . $e->getMessage());
        }
    }

    public function storeBatch(Request $request)
    {
        $this->validate($request, [
            'capaian' => 'required|array|min:1',
            'capaian.*.kode_cp' => 'required|string|max:255',
            'capaian.*.deskripsi' => 'required|string',
            'capaian.*.mapel_id' => 'required|string|uuid|exists:mata_pelajaran,id'
        ]);

        try {
            $admin = Auth::user();
            \Log::info('Admin yang melakukan import CP: ', ['id' => $admin->id, 'sekolah_id' => $admin->sekolah_id]);
            
            $cpData = $request->capaian;
            $importedData = [];
            $errors = [];
            $imported = 0;
            
            // Nonaktifkan foreign key check sementara
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::beginTransaction();
            
            foreach ($cpData as $index => $data) {
                try {
                    // Log data yang akan diproses
                    \Log::info('Processing CP data: ', $data);
                    
                    // Buat UUID untuk capaian pembelajaran
                    $cpId = (string) Str::uuid();
                    
                    // Buat data capaian pembelajaran langsung dengan DB::table
                    $insertData = [
                        'id' => $cpId,
                        'kode_cp' => $data['kode_cp'],
                        'deskripsi' => $data['deskripsi'],
                        'mapel_id' => $data['mapel_id'],
                        'sekolah_id' => $admin->sekolah_id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ];
                    
                    DB::table('capaian_pembelajaran')->insert($insertData);
                    
                    \Log::info('Capaian pembelajaran created with ID: ' . $cpId);
                    
                    $importedData[] = [
                        'id' => $cpId,
                        'kode_cp' => $data['kode_cp'],
                        'deskripsi' => $data['deskripsi'],
                        'mapel_id' => $data['mapel_id']
                    ];
                    
                    $imported++;
                } catch (\Exception $e) {
                    \Log::error('Error creating CP: ' . $e->getMessage());
                    \Log::error('Stack trace: ' . $e->getTraceAsString());
                    $errors[] = [
                        'row' => $index + 1,
                        'kode_cp' => $data['kode_cp'] ?? 'Unknown',
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            DB::commit();
            // Aktifkan kembali foreign key check
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            
            return ResponseBuilder::success(200, "Berhasil menambahkan $imported data capaian pembelajaran", [
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