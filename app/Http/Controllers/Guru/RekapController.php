<?php

namespace App\Http\Controllers\Guru;

use App\Models\NilaiSiswa;
use App\Models\AbsensiSiswa;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Str;

class RekapController extends BaseGuruController
{
    public function nilai(Request $request)
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
            
            $nilai = $query->get();
            
            // Hitung statistik
            $statistik = [
                'total_siswa' => $nilai->pluck('siswa_id')->unique()->count(),
                'rata_rata' => $nilai->avg('nilai'),
                'nilai_tertinggi' => $nilai->max('nilai'),
                'nilai_terendah' => $nilai->min('nilai')
            ];
            
            $data = [
                'nilai' => $nilai,
                'statistik' => $statistik
            ];
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan rekap nilai", $data);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }

    public function absensi(Request $request)
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
            
            // Filter berdasarkan rentang tanggal
            if ($request->start_date && $request->end_date) {
                $query->whereHas('pertemuan', function($q) use ($request) {
                    $q->whereBetween('tanggal', [
                        Carbon::parse($request->start_date),
                        Carbon::parse($request->end_date)
                    ]);
                });
            }
            
            $absensi = $query->get();
            
            // Hitung statistik
            $statistik = [
                'total_pertemuan' => $absensi->pluck('pertemuan_id')->unique()->count(),
                'total_siswa' => $absensi->pluck('siswa_id')->unique()->count(),
                'rekap_status' => [
                    'hadir' => $absensi->where('hadir', 1)->count(),
                    'izin' => $absensi->where('izin', 1)->count(),
                    'sakit' => $absensi->where('sakit', 1)->count(),
                    'absen' => $absensi->where('absen', 1)->count(),
                ]
            ];
            
            // Transformasi data untuk frontend
            $transformedAbsensi = $absensi->map(function($item) {
                $status = 'hadir';
                if ($item->izin == 1) $status = 'izin';
                if ($item->sakit == 1) $status = 'sakit';
                if ($item->absen == 1) $status = 'absen';
                
                $item->status = $status;
                return $item;
            });
            
            $data = [
                'absensi' => $transformedAbsensi,
                'statistik' => $statistik
            ];
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan rekap absensi", $data);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }

    public function detailNilai($id)
    {
        try {
            // Validasi format UUID
            if (!Str::isUuid($id)) {
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
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan detail nilai", ['nilai' => $nilai]);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }

    public function detailAbsensi($id)
    {
        try {
            // Validasi format UUID
            if (!Str::isUuid($id)) {
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
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan detail absensi", ['absensi' => $absensi]);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }
} 