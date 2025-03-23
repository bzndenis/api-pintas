<?php

namespace App\Http\Controllers\Guru;

use App\Models\NilaiSiswa;
use App\Models\Siswa;
use App\Models\TujuanPembelajaran;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Uuid;

class NilaiController extends BaseGuruController
{
    public function index(Request $request)
    {
        try {
            $guru = Auth::user()->guru;
            
            $query = NilaiSiswa::with([
                'siswa.kelas',
                'tujuanPembelajaran.capaianPembelajaran.mataPelajaran'
            ])->whereHas('tujuanPembelajaran.capaianPembelajaran.mataPelajaran', function($q) use ($guru) {
                $q->where('guru_id', $guru->id);
            });
            
            // Filter berdasarkan kelas
            if ($request->kelas_id) {
                $query->whereHas('siswa', function($q) use ($request) {
                    $q->where('kelas_id', $request->kelas_id);
                });
            }
            
            // Filter berdasarkan semester
            if ($request->semester) {
                $query->where('semester', $request->semester);
            }
            
            $nilai = $query->orderBy('created_at', 'desc')->get();
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan data nilai", $nilai);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'siswa_id' => 'required|exists:siswa,id',
            'tujuan_pembelajaran_id' => 'required|exists:tujuan_pembelajaran,id',
            'nilai' => 'required|numeric|min:0|max:100',
            'semester' => 'required|in:1,2',
            'jenis_nilai' => 'required|in:UH,STS,SAS',
            'nomor_uh' => 'required_if:jenis_nilai,UH|nullable|integer|min:1|max:3',
            'keterangan' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();
            
            $guru = Auth::user()->guru;
            
            // Validasi apakah guru mengajar mata pelajaran tersebut
            $tp = TujuanPembelajaran::with('capaianPembelajaran.mataPelajaran')
                ->find($request->tujuan_pembelajaran_id);
                
            if ($tp->capaianPembelajaran->mataPelajaran->guru_id !== $guru->id) {
                return ResponseBuilder::error(403, "Anda tidak memiliki akses untuk menilai mata pelajaran ini");
            }

            // Validasi jumlah UH per bab
            if ($request->jenis_nilai === 'UH') {
                $existingUH = NilaiSiswa::where('siswa_id', $request->siswa_id)
                    ->where('semester', $request->semester)
                    ->where('jenis_nilai', 'UH')
                    ->where('nomor_uh', $request->nomor_uh)
                    ->whereHas('tujuanPembelajaran', function($q) use ($tp) {
                        $q->where('capaian_pembelajaran_id', $tp->capaian_pembelajaran_id);
                    })
                    ->exists();

                if ($existingUH) {
                    return ResponseBuilder::error(400, "Nilai UH {$request->nomor_uh} untuk siswa ini sudah ada");
                }
            }
            
            $nilai = NilaiSiswa::create([
                'siswa_id' => $request->siswa_id,
                'tujuan_pembelajaran_id' => $request->tujuan_pembelajaran_id,
                'nilai' => $request->nilai,
                'semester' => $request->semester,
                'jenis_nilai' => $request->jenis_nilai,
                'nomor_uh' => $request->nomor_uh,
                'keterangan' => $request->keterangan,
                'guru_id' => $guru->id,
                'sekolah_id' => $guru->sekolah_id
            ]);
            
            DB::commit();
            
            return ResponseBuilder::success(201, "Berhasil menambahkan nilai", $nilai);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal menambahkan nilai: " . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            // Validasi format UUID
            if (!Uuid::isValid($id)) {
                return ResponseBuilder::error(400, "Format ID tidak valid");
            }

            $guru = Auth::user()->guru;
            
            $nilai = NilaiSiswa::with([
                'siswa.kelas',
                'tujuanPembelajaran.capaianPembelajaran.mataPelajaran'
            ])->whereHas('tujuanPembelajaran.capaianPembelajaran.mataPelajaran', function($q) use ($guru) {
                $q->where('guru_id', $guru->id);
            })->find($id);
            
            if (!$nilai) {
                return ResponseBuilder::error(404, "Data nilai tidak ditemukan");
            }
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan data nilai", ['nilai' => $nilai]);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Validasi format UUID
            if (!Uuid::isValid($id)) {
                return ResponseBuilder::error(400, "Format ID tidak valid");
            }
            
            // Validasi input
            $this->validate($request, [
                'nilai' => 'required|numeric|min:0|max:100',
                'keterangan' => 'nullable|string'
            ]);

            $guru = Auth::user()->guru;
            
            $nilai = NilaiSiswa::whereHas('tujuanPembelajaran.capaianPembelajaran.mataPelajaran', 
                function($q) use ($guru) {
                    $q->where('guru_id', $guru->id);
                })->find($id);
            
            if (!$nilai) {
                return ResponseBuilder::error(404, "Data nilai tidak ditemukan");
            }
            
            $nilai->update($request->only(['nilai', 'keterangan']));
            
            return ResponseBuilder::success(200, "Berhasil mengupdate nilai", ['nilai' => $nilai]);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mengupdate nilai: " . $e->getMessage());
        }
    }
} 