<?php

namespace App\Http\Controllers\Guru;

use App\Models\TujuanPembelajaran;
use App\Models\CapaianPembelajaran;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            'kode_tp' => 'required|string|max:20',
            'deskripsi' => 'required|string',
            'bobot' => 'required|numeric|min:0|max:100',
            'cp_id' => 'required|exists:capaian_pembelajaran,id'
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
            'kode_tp' => 'required|string|max:50',
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
            if ($tp->nilai()->count() > 0) {
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
} 