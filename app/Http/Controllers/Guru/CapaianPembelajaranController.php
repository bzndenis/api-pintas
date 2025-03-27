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
            'kode_cp' => 'required|string|max:20',
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
            'kode_cp' => 'required|string|max:50',
            'deskripsi' => 'required|string',
            'mapel_id' => 'required|exists:mata_pelajaran,id'
        ]);

        try {
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
            
            $cp->update($request->all());
            
            return ResponseBuilder::success(200, "Berhasil mengupdate capaian pembelajaran", $cp);
        } catch (\Exception $e) {
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
} 