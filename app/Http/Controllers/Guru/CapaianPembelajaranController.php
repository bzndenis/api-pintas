<?php

namespace App\Http\Controllers\Guru;

use App\Models\CapaianPembelajaran;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CapaianPembelajaranController extends BaseGuruController
{
    public function index(Request $request)
    {
        try {
            $guru = Auth::user()->guru;
            
            $query = CapaianPembelajaran::where('sekolah_id', Auth::user()->sekolah_id)
                ->whereHas('mataPelajaran', function($q) use ($guru) {
                    $q->where('guru_id', $guru->id);
                })
                ->with(['mataPelajaran', 'tujuanPembelajaran']);
            
            // Filter berdasarkan mata pelajaran
            if ($request->mapel_id) {
                $query->where('mapel_id', $request->mapel_id);
            }
            
            $cp = $query->get();
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan data capaian pembelajaran", $cp);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }
    
    public function show($id)
    {
        try {
            $guru = Auth::user()->guru;
            
            $cp = CapaianPembelajaran::where('sekolah_id', Auth::user()->sekolah_id)
                ->whereHas('mataPelajaran', function($q) use ($guru) {
                    $q->where('guru_id', $guru->id);
                })
                ->with(['mataPelajaran', 'tujuanPembelajaran'])
                ->find($id);
            
            if (!$cp) {
                return ResponseBuilder::error(404, "Capaian pembelajaran tidak ditemukan");
            }
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan detail capaian pembelajaran", $cp);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'kode_cp' => 'nullable|string|max:20',
            'deskripsi' => 'required|string',
            'mapel_id' => 'required|exists:mata_pelajaran,id'
        ]);

        try {
            DB::beginTransaction();
            
            $guru = Auth::user()->guru;
            
            // Validasi apakah guru mengajar mata pelajaran ini
            $mapelCount = $guru->mataPelajaran()
                ->where('id', $request->mapel_id)
                ->count();
                
            if ($mapelCount === 0) {
                return ResponseBuilder::error(403, "Anda tidak memiliki akses untuk mata pelajaran ini");
            }

            // Generate kode CP otomatis jika tidak diisi
            if (!$request->kode_cp) {
                $mapel = \App\Models\MataPelajaran::find($request->mapel_id);
                $lastCP = CapaianPembelajaran::where('mapel_id', $request->mapel_id)
                    ->where('sekolah_id', $guru->sekolah_id)
                    ->orderBy('created_at', 'desc')
                    ->first();

                $counter = 1;
                if ($lastCP && preg_match('/(\d+)$/', $lastCP->kode_cp, $matches)) {
                    $counter = intval($matches[1]) + 1;
                }

                $kodeMapel = $mapel ? strtoupper($mapel->nama) : 'MP';
                $kodeMapel = preg_replace('/[^A-Z]/', '', $kodeMapel); // Ambil huruf kapital saja
                if (empty($kodeMapel)) {
                    $kodeMapel = 'MP';
                }
                $request->merge(['kode_cp' => $kodeMapel . '.' . str_pad($counter, 2, '0', STR_PAD_LEFT)]);
            }
            
            // Validasi kode CP unik per mapel dan sekolah
            $existingCP = CapaianPembelajaran::where('kode_cp', $request->kode_cp)
                ->where('mapel_id', $request->mapel_id)
                ->where('sekolah_id', $guru->sekolah_id)
                ->exists();
                
            if ($existingCP) {
                return ResponseBuilder::error(400, "Kode CP sudah digunakan untuk mata pelajaran ini");
            }
            
            $cp = CapaianPembelajaran::create([
                'kode_cp' => $request->kode_cp,
                'deskripsi' => $request->deskripsi,
                'mapel_id' => $request->mapel_id,
                'sekolah_id' => $guru->sekolah_id
            ]);
            
            DB::commit();
            
            return ResponseBuilder::success(201, "Berhasil menambahkan capaian pembelajaran", $cp);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal menambahkan data: " . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'kode_cp' => 'nullable|string|max:20',
            'deskripsi' => 'required|string',
            'mapel_id' => 'required|exists:mata_pelajaran,id'
        ]);

        try {
            DB::beginTransaction();
            
            $guru = Auth::user()->guru;
            
            // Validasi apakah guru mengajar mata pelajaran tersebut
            $mapel = \App\Models\MataPelajaran::where('id', $request->mapel_id)
                ->where('guru_id', $guru->id)
                ->first();
                
            if (!$mapel) {
                return ResponseBuilder::error(403, "Anda tidak memiliki akses untuk mata pelajaran ini");
            }
            
            $cp = CapaianPembelajaran::where('sekolah_id', Auth::user()->sekolah_id)
                ->whereHas('mataPelajaran', function($q) use ($guru) {
                    $q->where('guru_id', $guru->id);
                })
                ->find($id);
            
            if (!$cp) {
                return ResponseBuilder::error(404, "Capaian pembelajaran tidak ditemukan");
            }
            
            // Validasi kode CP unik per mapel dan sekolah (kecuali untuk CP yang sedang diupdate)
            $existingCP = CapaianPembelajaran::where('kode_cp', $request->kode_cp)
                ->where('mapel_id', $request->mapel_id)
                ->where('sekolah_id', $guru->sekolah_id)
                ->where('id', '!=', $id)
                ->exists();
                
            if ($existingCP) {
                return ResponseBuilder::error(400, "Kode CP sudah digunakan untuk mata pelajaran ini");
            }
            
            $cp->update([
                'kode_cp' => $request->kode_cp,
                'deskripsi' => $request->deskripsi,
                'mapel_id' => $request->mapel_id
            ]);
            
            DB::commit();
            
            return ResponseBuilder::success(200, "Berhasil mengupdate capaian pembelajaran", $cp);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal mengupdate data: " . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $guru = Auth::user()->guru;
            
            $cp = CapaianPembelajaran::where('sekolah_id', Auth::user()->sekolah_id)
                ->whereHas('mataPelajaran', function($q) use ($guru) {
                    $q->where('guru_id', $guru->id);
                })
                ->find($id);
            
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
            'capaian.*.kode_cp' => 'nullable|string|max:20',
            'capaian.*.deskripsi' => 'required|string',
            'capaian.*.mapel_id' => 'required|string|uuid|exists:mata_pelajaran,id'
        ]);

        try {
            $guru = Auth::user()->guru;
            \Log::info('Guru yang melakukan import CP: ', ['id' => $guru->id, 'sekolah_id' => $guru->sekolah_id]);
            
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
                    
                    // Validasi apakah guru mengajar mata pelajaran ini
                    $mapelCount = $guru->mataPelajaran()
                        ->where('id', $data['mapel_id'])
                        ->count();
                        
                    if ($mapelCount === 0) {
                        throw new \Exception("Anda tidak memiliki akses untuk mata pelajaran dengan ID: " . $data['mapel_id']);
                    }
                    
                    // Generate kode CP otomatis jika tidak diisi
                    if (empty($data['kode_cp'])) {
                        $mapel = \App\Models\MataPelajaran::find($data['mapel_id']);
                        $lastCP = CapaianPembelajaran::where('mapel_id', $data['mapel_id'])
                            ->where('sekolah_id', $guru->sekolah_id)
                            ->orderBy('created_at', 'desc')
                            ->first();

                        $counter = 1;
                        if ($lastCP && preg_match('/(\d+)$/', $lastCP->kode_cp, $matches)) {
                            $counter = intval($matches[1]) + 1;
                        }

                        $kodeMapel = $mapel ? strtoupper($mapel->nama) : 'MP';
                        $kodeMapel = preg_replace('/[^A-Z]/', '', $kodeMapel); // Ambil huruf kapital saja
                        if (empty($kodeMapel)) {
                            $kodeMapel = 'MP';
                        }
                        $data['kode_cp'] = $kodeMapel . '.' . str_pad($counter, 2, '0', STR_PAD_LEFT);
                    }
                    
                    // Validasi kode CP unik per mapel dan sekolah
                    $existingCP = CapaianPembelajaran::where('kode_cp', $data['kode_cp'])
                        ->where('mapel_id', $data['mapel_id'])
                        ->where('sekolah_id', $guru->sekolah_id)
                        ->exists();
                        
                    if ($existingCP) {
                        throw new \Exception("Kode CP " . $data['kode_cp'] . " sudah digunakan untuk mata pelajaran ini");
                    }
                    
                    // Buat UUID untuk capaian pembelajaran
                    $cpId = (string) Str::uuid();
                    
                    // Buat data capaian pembelajaran langsung dengan DB::table
                    $insertData = [
                        'id' => $cpId,
                        'kode_cp' => $data['kode_cp'],
                        'deskripsi' => $data['deskripsi'],
                        'mapel_id' => $data['mapel_id'],
                        'sekolah_id' => $guru->sekolah_id,
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