<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\MataPelajaran;
use App\Models\CapaianPembelajaran;
use App\Models\TujuanPembelajaran;
use App\Models\NilaiSiswa;
use App\Models\Pertemuan;
use App\Models\AbsensiSiswa;
use App\Models\PertemuanBulanan;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GuruController extends Controller
{
    public function getProfile(Request $request)
    {
        try {
            $guru = Guru::with(['user', 'sekolah'])
                ->where('user_id', $request->user_id)
                ->first();

            if (!$guru) {
                return ResponseBuilder::error(404, "Data guru tidak ditemukan");
            }

            return ResponseBuilder::success(200, "Berhasil mendapatkan profile", [
                'guru' => [
                    'id' => $guru->id,
                    'nama' => $guru->nama,
                    'nip' => $guru->nip,
                    'email' => $guru->email,
                    'no_telp' => $guru->no_telp,
                    'user' => [
                        'id' => $guru->user->id,
                        'email' => $guru->user->email,
                        'role' => $guru->user->role
                    ],
                    'sekolah' => [
                        'id' => $guru->sekolah->id,
                        'nama_sekolah' => $guru->sekolah->nama_sekolah,
                        'npsn' => $guru->sekolah->npsn
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mengambil profile: " . $e->getMessage());
        }
    }

    public function getKelas(Request $request)
    {
        try {
            $guru = Guru::find($request->user_id);
            $kelas = Kelas::with(['siswa', 'mataPelajaran'])
                ->where('guru_id', $guru->id)
                ->where('tahun_ajaran_id', $request->tahun_ajaran_id)
                ->get();

            return ResponseBuilder::success(200, "Berhasil mendapatkan data kelas", [
                'kelas' => $kelas->map(function($item) {
                    return [
                        'id' => $item->id,
                        'nama_kelas' => $item->nama_kelas,
                        'tingkat' => $item->tingkat,
                        'jumlah_siswa' => $item->siswa->count(),
                        'mata_pelajaran' => $item->mataPelajaran->map(function($mapel) {
                            return [
                                'id' => $mapel->id,
                                'nama' => $mapel->nama,
                                'kode' => $mapel->kode
                            ];
                        })
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mengambil data kelas: " . $e->getMessage());
        }
    }

    public function storeNilaiBatch(Request $request)
    {
        try {
            DB::beginTransaction();

            $nilai = collect($request->nilai)->map(function($item) use ($request) {
                return [
                    'id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
                    'siswa_id' => $item['siswa_id'],
                    'tp_id' => $request->tp_id,
                    'nilai' => $item['nilai'],
                    'created_by' => $request->user_id,
                    'sekolah_id' => $request->sekolah_id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ];
            })->all();

            NilaiSiswa::insert($nilai);

            DB::commit();
            return ResponseBuilder::success(200, "Berhasil menyimpan nilai");
        } catch (\Exception $e) {
            DB::rollback();
            return ResponseBuilder::error(500, "Gagal menyimpan nilai: " . $e->getMessage());
        }
    }

    public function storeAbsensiBatch(Request $request)
    {
        try {
            DB::beginTransaction();

            $absensi = collect($request->absensi)->map(function($item) use ($request) {
                return [
                    'id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
                    'siswa_id' => $item['siswa_id'],
                    'pertemuan_id' => $request->pertemuan_id,
                    'hadir' => $item['hadir'] ?? 0,
                    'izin' => $item['izin'] ?? 0,
                    'sakit' => $item['sakit'] ?? 0,
                    'absen' => $item['absen'] ?? 0,
                    'created_by' => $request->user_id,
                    'sekolah_id' => $request->sekolah_id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ];
            })->all();

            AbsensiSiswa::insert($absensi);

            DB::commit();
            return ResponseBuilder::success(200, "Berhasil menyimpan absensi");
        } catch (\Exception $e) {
            DB::rollback();
            return ResponseBuilder::error(500, "Gagal menyimpan absensi: " . $e->getMessage());
        }
    }

    public function getDashboardSummary(Request $request)
    {
        try {
            $guru = Guru::find($request->user_id);
            
            // Hitung total kelas
            $totalKelas = Kelas::where('guru_id', $guru->id)
                ->where('tahun_ajaran_id', $request->tahun_ajaran_id)
                ->where('sekolah_id', $guru->sekolah_id)
                ->count();

            // Hitung total siswa
            $totalSiswa = Siswa::whereHas('kelas', function($q) use ($guru) {
                $q->where('guru_id', $guru->id)
                  ->where('sekolah_id', $guru->sekolah_id);
            })->count();

            // Hitung rata-rata nilai
            $rataRataNilai = NilaiSiswa::whereHas('siswa.kelas', function($q) use ($guru) {
                $q->where('guru_id', $guru->id)
                  ->where('sekolah_id', $guru->sekolah_id);
            })->avg('nilai');

            // Hitung total pertemuan bulan ini
            $totalPertemuan = PertemuanBulanan::where('created_by', $request->user_id)
                ->where('sekolah_id', $guru->sekolah_id)
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->sum('total_pertemuan');

            return ResponseBuilder::success(200, "Berhasil mendapatkan summary", [
                'total_kelas' => $totalKelas,
                'total_siswa' => $totalSiswa,
                'rata_rata_nilai' => round($rataRataNilai, 2),
                'total_pertemuan' => $totalPertemuan
            ]);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mengambil summary: " . $e->getMessage());
        }
    }
} 