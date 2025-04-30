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
                $lastTP = TujuanPembelajaran::withTrashed()
                    ->where('cp_id', $request->cp_id)
                    ->where('sekolah_id', $guru->sekolah_id)
                    ->orderBy(DB::raw('CAST(SUBSTRING_INDEX(kode_tp, ".", -1) AS UNSIGNED)'), 'desc')
                    ->first();

                $counter = 1;
                if ($lastTP && preg_match('/(\d+)$/', $lastTP->kode_tp, $matches)) {
                    $counter = intval($matches[1]) + 1;
                }

                $request->merge(['kode_tp' => 'TP.' . $cp->kode_cp . '.' . str_pad($counter, 2, '0', STR_PAD_LEFT)]);
            }
            
            // Validasi kode TP unik per CP dan sekolah
            $existingTP = TujuanPembelajaran::withTrashed()
                ->where('kode_tp', $request->kode_tp)
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
            
            // Hapus semua nilai siswa terkait
            $tp->nilaiSiswa()->delete();
            
            // Hapus tujuan pembelajaran
            $tp->delete();
            
            DB::commit();
            
            return ResponseBuilder::success(200, "Berhasil menghapus data tujuan pembelajaran beserta nilainya");
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
            
            // Nonaktifkan foreign key check sementara
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::beginTransaction();

            // Kelompokkan data berdasarkan cp_id
            $groupedData = collect($tpData)->groupBy('cp_id');
            
            foreach ($groupedData as $cpId => $tpGroup) {
                // Validasi apakah guru memiliki akses ke capaian pembelajaran
                $cp = CapaianPembelajaran::whereHas('mataPelajaran', function($q) use ($guru) {
                    $q->where('guru_id', $guru->id);
                })->find($cpId);
                
                if (!$cp) {
                    foreach ($tpGroup as $index => $data) {
                        $errors[] = [
                            'row' => $index + 1,
                            'kode_tp' => $data['kode_tp'] ?? 'Unknown',
                            'error' => "Anda tidak memiliki akses untuk capaian pembelajaran dengan ID: " . $cpId
                        ];
                    }
                    continue;
                }

                // Dapatkan kode TP terakhir untuk capaian pembelajaran ini
                $lastTP = TujuanPembelajaran::withTrashed()
                    ->where('cp_id', $cpId)
                    ->where('sekolah_id', $guru->sekolah_id)
                    ->orderBy(DB::raw('CAST(SUBSTRING_INDEX(kode_tp, ".", -1) AS UNSIGNED)'), 'desc')
                    ->first();

                $counter = 1;
                if ($lastTP && preg_match('/(\d+)$/', $lastTP->kode_tp, $matches)) {
                    $counter = intval($matches[1]) + 1;
                }

                $baseKodeTP = 'TP.' . $cp->kode_cp . '.';

                // Dapatkan semua kode TP yang sudah ada untuk capaian pembelajaran ini
                $existingCodes = TujuanPembelajaran::withTrashed()
                    ->where('cp_id', $cpId)
                    ->where('sekolah_id', $guru->sekolah_id)
                    ->pluck('kode_tp')
                    ->toArray();

                foreach ($tpGroup as $index => $data) {
                    try {
                        $originalKodeTP = $data['kode_tp'] ?? null;
                        $kodeTP = $originalKodeTP;

                        // Jika kode TP tidak diisi atau sudah ada, generate kode baru
                        if (empty($kodeTP) || in_array($kodeTP, $existingCodes)) {
                            do {
                                $kodeTP = $baseKodeTP . str_pad($counter++, 2, '0', STR_PAD_LEFT);
                            } while (in_array($kodeTP, $existingCodes));
                        }

                        // Tambahkan kode TP baru ke daftar yang sudah ada
                        $existingCodes[] = $kodeTP;

                        $tpId = (string) Str::uuid();
                        $insertData = [
                            'id' => $tpId,
                            'kode_tp' => $kodeTP,
                            'nama' => $data['nama'] ?? null,
                            'deskripsi' => $data['deskripsi'],
                            'bobot' => $data['bobot'],
                            'cp_id' => $cpId,
                            'sekolah_id' => $guru->sekolah_id,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ];
                        
                        DB::table('tujuan_pembelajaran')->insert($insertData);
                        
                        \Log::info('Tujuan pembelajaran created with ID: ' . $tpId);
                        
                        $importedData[] = [
                            'id' => $tpId,
                            'kode_tp' => $kodeTP,
                            'nama' => $data['nama'] ?? null,
                            'deskripsi' => $data['deskripsi'],
                            'bobot' => $data['bobot'],
                            'cp_id' => $cpId
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
            }
            
            DB::commit();
            // Aktifkan kembali foreign key check
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            
            $message = "Berhasil menambahkan $imported data tujuan pembelajaran";
            if (count($importedData) > 0 && count($errors) > 0) {
                $message .= " dengan " . count($errors) . " data gagal";
            }
            
            return ResponseBuilder::success(200, $message, [
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