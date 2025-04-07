<?php

namespace App\Http\Controllers\Guru;

use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Uuid;

class DashboardController extends BaseGuruController
{
    public function index()
    {
        try {
            $guru = Auth::user()->guru;
            
            // Get data mengajar
            $mengajar = MataPelajaran::with(['kelas', 'capaianPembelajaran'])
                ->where('guru_id', $guru->id)
                ->get();
                
            // Get jumlah siswa yang diajar
            $jumlahSiswa = Siswa::whereHas('kelas', function($q) use ($guru) {
                $q->whereHas('mataPelajaran', function($q) use ($guru) {
                    $q->where('mata_pelajaran.guru_id', $guru->id);
                });
            })->count();

            // Get info sekolah
            $sekolah = $guru->sekolah;

            // Get daftar kelas yang diajar
            $kelas = Kelas::whereHas('mataPelajaran', function($q) use ($guru) {
                $q->where('mata_pelajaran.guru_id', $guru->id);
            })->get();
            
            $data = [
                'guru' => $guru,
                'sekolah' => [
                    'id' => $sekolah->id,
                    'nama' => $sekolah->nama,
                    'alamat' => $sekolah->alamat
                ],
                'mengajar' => $mengajar,
                'kelas' => $kelas->map(function($item) {
                    return [
                        'id' => $item->id,
                        'nama_kelas' => $item->nama_kelas,
                        'tingkat' => $item->tingkat
                    ];
                }),
                'jumlah_siswa' => $jumlahSiswa,
            ];
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan data dashboard", $data);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }

    public function detailKelas($id)
    {
        try {
            // Validasi format UUID
            if (!Uuid::isValid($id)) {
                return ResponseBuilder::error(400, "Format ID tidak valid");
            }

            $guru = Auth::user()->guru;
            
            $kelas = Kelas::with(['siswa', 'waliKelas'])
                ->whereHas('mataPelajaran', function($q) use ($guru) {
                    $q->where('guru_id', $guru->id);
                })->find($id);
            
            if (!$kelas) {
                return ResponseBuilder::error(404, "Data kelas tidak ditemukan");
            }
            
            $mataPelajaran = MataPelajaran::forKelas($kelas->id)->where('guru_id', $guru->id)->get();
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan detail kelas", ['kelas' => $kelas]);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }
} 