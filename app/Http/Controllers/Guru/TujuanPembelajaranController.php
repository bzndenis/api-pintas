<?php

namespace App\Http\Controllers\Guru;

use App\Models\TujuanPembelajaran;
use App\Models\CapaianPembelajaran;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TujuanPembelajaranController extends BaseGuruController
{
    public function index(Request $request)
    {
        try {
            $guru = Auth::user()->guru;
            
            $query = TujuanPembelajaran::where('sekolah_id', Auth::user()->sekolah_id)
                ->whereHas('capaianPembelajaran.mataPelajaran', function($q) use ($guru) {
                    $q->where('guru_id', $guru->id);
                })
                ->with(['capaianPembelajaran.mataPelajaran']);
            
            // Filter berdasarkan capaian pembelajaran
            if ($request->capaian_pembelajaran_id) {
                $query->where('cp_id', $request->capaian_pembelajaran_id);
            }
            
            $tp = $query->get();
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan data tujuan pembelajaran", $tp);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }
    
    public function show($id)
    {
        try {
            $guru = Auth::user()->guru;
            
            $tp = TujuanPembelajaran::where('sekolah_id', Auth::user()->sekolah_id)
                ->whereHas('capaianPembelajaran.mataPelajaran', function($q) use ($guru) {
                    $q->where('guru_id', $guru->id);
                })
                ->with(['capaianPembelajaran.mataPelajaran'])
                ->find($id);
            
            if (!$tp) {
                return ResponseBuilder::error(404, "Tujuan pembelajaran tidak ditemukan");
            }
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan detail tujuan pembelajaran", $tp);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'deskripsi' => 'required|string',
            'nama' => 'nullable|string|max:255',
            'bobot' => 'required|numeric|min:0|max:100',
            'cp_id' => 'required|exists:capaian_pembelajaran,id',
            'kode_tp' => 'nullable|string|max:20'
        ]);

        try {
            DB::beginTransaction();
            
            $guru = Auth::user()->guru;
            
            // Validasi apakah guru memiliki akses ke CP ini
            $cp = CapaianPembelajaran::whereHas('mataPelajaran', function($q) use ($guru) {
                $q->where('guru_id', $guru->id);
            })->find($request->cp_id);
            
            if (!$cp) {
                return ResponseBuilder::error(403, "Anda tidak memiliki akses untuk capaian pembelajaran ini");
            }

            // Generate kode TP otomatis jika tidak diisi
            if (!$request->kode_tp) {
                $lastTP = TujuanPembelajaran::where('cp_id', $request->cp_id)
                    ->where('sekolah_id', $guru->sekolah_id)
                    ->orderBy('created_at', 'desc')
                    ->first();

                $counter = 1;
                if ($lastTP && preg_match('/(\d+)$/', $lastTP->kode_tp, $matches)) {
                    $counter = intval($matches[1]) + 1;
                }

                $request->merge(['kode_tp' => 'TP.' . $cp->kode_cp . '.' . str_pad($counter, 2, '0', STR_PAD_LEFT)]);
            }
            
            // Validasi kode TP unik per CP dan sekolah
            $existingTP = TujuanPembelajaran::where('kode_tp', $request->kode_tp)
                ->where('cp_id', $request->cp_id)
                ->where('sekolah_id', $guru->sekolah_id)
                ->exists();
                
            if ($existingTP) {
                return ResponseBuilder::error(400, "Kode TP sudah digunakan untuk capaian pembelajaran ini");
            }
            
            $tp = TujuanPembelajaran::create([
                'kode_tp' => $request->kode_tp,
                'nama' => $request->nama,
                'deskripsi' => $request->deskripsi,
                'bobot' => $request->bobot,
                'cp_id' => $request->cp_id,
                'sekolah_id' => $guru->sekolah_id
            ]);
            
            DB::commit();
            
            return ResponseBuilder::success(201, "Berhasil menambahkan tujuan pembelajaran", $tp);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal menambahkan data: " . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'kode_tp' => 'nullable|string|max:50',
            'nama' => 'nullable|string|max:255',
            'deskripsi' => 'required|string',
            'capaian_pembelajaran_id' => 'required|exists:capaian_pembelajaran,id'
        ]);

        try {
            $guru = Auth::user()->guru;
            
            // Validasi apakah guru mengajar mata pelajaran terkait capaian pembelajaran
            $cp = CapaianPembelajaran::whereHas('mataPelajaran', function($q) use ($guru) {
                $q->where('guru_id', $guru->id);
            })->find($request->capaian_pembelajaran_id);
            
            if (!$cp) {
                return ResponseBuilder::error(403, "Anda tidak memiliki akses untuk capaian pembelajaran ini");
            }
            
            $tp = TujuanPembelajaran::where('sekolah_id', Auth::user()->sekolah_id)
                ->whereHas('capaianPembelajaran.mataPelajaran', function($q) use ($guru) {
                    $q->where('guru_id', $guru->id);
                })
                ->find($id);
            
            if (!$tp) {
                return ResponseBuilder::error(404, "Tujuan pembelajaran tidak ditemukan");
            }
            
            $tp->update($request->all());
            
            return ResponseBuilder::success(200, "Berhasil mengupdate tujuan pembelajaran", $tp);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mengupdate data: " . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $guru = Auth::user()->guru;
            
            $tp = TujuanPembelajaran::where('sekolah_id', Auth::user()->sekolah_id)
                ->whereHas('capaianPembelajaran.mataPelajaran', function($q) use ($guru) {
                    $q->where('guru_id', $guru->id);
                })
                ->find($id);
            
            if (!$tp) {
                return ResponseBuilder::error(404, "Data tujuan pembelajaran tidak ditemukan");
            }
            
            DB::beginTransaction();
            
            // Cek apakah tujuan pembelajaran masih digunakan oleh nilai
            if ($tp->nilaiSiswa()->count() > 0) {
                return ResponseBuilder::error(400, "Tidak dapat menghapus tujuan pembelajaran yang masih memiliki data nilai");
            }
            
            // Hapus tujuan pembelajaran
            $tp->delete();
            
            DB::commit();
            
            return ResponseBuilder::success(200, "Berhasil menghapus data tujuan pembelajaran");
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal menghapus data: " . $e->getMessage());
        }
    }

    public function storeBatch(Request $request)
    {
        $this->validate($request, [
            'data' => 'required|array|min:1',
            'data.*.deskripsi' => 'required|string',
            'data.*.nama' => 'nullable|string|max:255',
            'data.*.bobot' => 'required|numeric|min:0|max:100',
            'data.*.cp_id' => 'required|string|uuid|exists:capaian_pembelajaran,id',
            'data.*.kode_tp' => 'nullable|string|max:20'
        ]);

        try {
            $guru = Auth::user()->guru;
            \Log::info('Guru yang melakukan import TP: ', ['id' => $guru->id, 'sekolah_id' => $guru->sekolah_id]);
            
            $tpData = $request->data;
            $importedData = [];
            $errors = [];
            $imported = 0;
            
            DB::beginTransaction();
            
            foreach ($tpData as $index => $data) {
                try {
                    // Log data yang akan diproses
                    \Log::info('Processing TP data: ', $data);
                    
                    // Validasi apakah guru memiliki akses ke capaian pembelajaran
                    $cp = CapaianPembelajaran::whereHas('mataPelajaran', function($q) use ($guru) {
                        $q->where('guru_id', $guru->id);
                    })->find($data['cp_id']);
                    
                    if (!$cp) {
                        $errors[] = [
                            'row' => $index + 1,
                            'kode_tp' => $data['kode_tp'] ?? 'Unknown',
                            'error' => "Capaian pembelajaran tidak ditemukan"
                        ];
                        continue;
                    }
                    
                    // Generate kode TP otomatis jika tidak diisi
                    if (empty($data['kode_tp'])) {
                        $lastTP = TujuanPembelajaran::where('cp_id', $data['cp_id'])
                            ->where('sekolah_id', $guru->sekolah_id)
                            ->orderBy('created_at', 'desc')
                            ->first();

                        $counter = 1;
                        if ($lastTP && preg_match('/(\d+)$/', $lastTP->kode_tp, $matches)) {
                            $counter = intval($matches[1]) + 1;
                        }

                        $data['kode_tp'] = 'TP.' . $cp->kode_cp . '.' . str_pad($counter, 2, '0', STR_PAD_LEFT);
                    }
                    
                    // Validasi kode TP unik per CP dan sekolah
                    $existingTP = TujuanPembelajaran::where('kode_tp', $data['kode_tp'])
                        ->where('cp_id', $data['cp_id'])
                        ->where('sekolah_id', $guru->sekolah_id)
                        ->exists();
                        
                    if ($existingTP) {
                        $errors[] = [
                            'row' => $index + 1,
                            'kode_tp' => $data['kode_tp'] ?? 'Unknown',
                            'error' => "Kode TP sudah digunakan untuk capaian pembelajaran ini"
                        ];
                        continue;
                    }
                    
                    // Buat UUID untuk tujuan pembelajaran
                    $tpId = (string) Str::uuid();
                    
                    // Buat data tujuan pembelajaran
                    $insertData = [
                        'id' => $tpId,
                        'kode_tp' => $data['kode_tp'],
                        'nama' => $data['nama'] ?? null,
                        'deskripsi' => $data['deskripsi'],
                        'bobot' => $data['bobot'],
                        'cp_id' => $data['cp_id'],
                        'sekolah_id' => $guru->sekolah_id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ];
                    
                    DB::table('tujuan_pembelajaran')->insert($insertData);
                    
                    \Log::info('Tujuan pembelajaran created with ID: ' . $tpId);
                    
                    $importedData[] = [
                        'id' => $tpId,
                        'kode_tp' => $data['kode_tp'],
                        'nama' => $data['nama'] ?? null,
                        'deskripsi' => $data['deskripsi'],
                        'bobot' => $data['bobot'],
                        'cp_id' => $data['cp_id']
                    ];
                    
                    $imported++;
                } catch (\Exception $e) {
                    \Log::error('Error creating TP: ' . $e->getMessage());
                    \Log::error('Stack trace: ' . $e->getTraceAsString());
                    $errors[] = [
                        'row' => $index + 1,
                        'kode_tp' => $data['kode_tp'] ?? 'Unknown',
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            DB::commit();
            
            return ResponseBuilder::success(200, "Berhasil menambahkan $imported data tujuan pembelajaran", [
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