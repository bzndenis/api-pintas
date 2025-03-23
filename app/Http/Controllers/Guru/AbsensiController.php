<?php

namespace App\Http\Controllers\Guru;

use App\Models\AbsensiSiswa;
use App\Models\Pertemuan;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Uuid;

class AbsensiController extends BaseGuruController
{
    public function index(Request $request)
    {
        try {
            $guru = Auth::user()->guru;
            
            $query = AbsensiSiswa::with(['siswa.kelas', 'pertemuan.mataPelajaran'])
                ->whereHas('pertemuan.mataPelajaran', function($q) use ($guru) {
                    $q->where('guru_id', $guru->id);
                });
            
            // Filter berdasarkan kelas
            if ($request->kelas_id) {
                $query->whereHas('siswa', function($q) use ($request) {
                    $q->where('kelas_id', $request->kelas_id);
                });
            }
            
            // Filter berdasarkan tanggal
            if ($request->tanggal) {
                $query->whereHas('pertemuan', function($q) use ($request) {
                    $q->whereDate('tanggal', Carbon::parse($request->tanggal));
                });
            }
            
            $absensi = $query->orderBy('created_at', 'desc')->get();
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan data absensi", $absensi);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'kelas_id' => 'required|exists:kelas,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'tanggal' => 'required|date',
            'pertemuan_ke' => 'required|integer',
            'materi' => 'required|string',
            'absensi' => 'required|array',
            'absensi.*.siswa_id' => 'required|exists:siswa,id',
            'absensi.*.status' => 'required|in:hadir,izin,sakit,alpha'
        ]);

        try {
            DB::beginTransaction();
            
            $guru = Auth::user()->guru;
            
            // Buat pertemuan baru
            $pertemuan = Pertemuan::create([
                'kelas_id' => $request->kelas_id,
                'mata_pelajaran_id' => $request->mata_pelajaran_id,
                'guru_id' => $guru->id,
                'tanggal' => Carbon::parse($request->tanggal),
                'pertemuan_ke' => $request->pertemuan_ke,
                'materi' => $request->materi,
                'sekolah_id' => $guru->sekolah_id
            ]);
            
            // Simpan absensi siswa
            foreach ($request->absensi as $absen) {
                AbsensiSiswa::create([
                    'pertemuan_id' => $pertemuan->id,
                    'siswa_id' => $absen['siswa_id'],
                    'status' => $absen['status'],
                    'keterangan' => $absen['keterangan'] ?? null,
                    'sekolah_id' => $guru->sekolah_id
                ]);
            }
            
            DB::commit();
            
            return ResponseBuilder::success(201, "Berhasil menyimpan absensi");
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal menyimpan absensi: " . $e->getMessage());
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
            
            $absensi = AbsensiSiswa::with(['siswa.kelas', 'pertemuan.mataPelajaran'])
                ->whereHas('pertemuan.mataPelajaran', function($q) use ($guru) {
                    $q->where('guru_id', $guru->id);
                })->find($id);
            
            if (!$absensi) {
                return ResponseBuilder::error(404, "Data absensi tidak ditemukan");
            }
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan data absensi", ['absensi' => $absensi]);
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

            $guru = Auth::user()->guru;
            
            $absensi = AbsensiSiswa::whereHas('pertemuan.mataPelajaran', function($q) use ($guru) {
                $q->where('guru_id', $guru->id);
            })->find($id);
            
            if (!$absensi) {
                return ResponseBuilder::error(404, "Data absensi tidak ditemukan");
            }
            
            $absensi->update($request->only(['status', 'keterangan']));
            
            return ResponseBuilder::success(200, "Berhasil mengupdate absensi", ['absensi' => $absensi]);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mengupdate absensi: " . $e->getMessage());
        }
    }
} 