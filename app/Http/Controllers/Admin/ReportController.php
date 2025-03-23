<?php

namespace App\Http\Controllers\Admin;

use App\Models\NilaiSiswa;
use App\Models\AbsensiSiswa;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReportController extends BaseAdminController
{
    public function nilai(Request $request)
    {
        try {
            $query = NilaiSiswa::with([
                'siswa.kelas',
                'tujuanPembelajaran.capaianPembelajaran.mataPelajaran'
            ])->where('sekolah_id', Auth::user()->sekolah_id);
            
            // Filter berdasarkan kelas
            if ($request->kelas_id) {
                $query->whereHas('siswa', function($q) use ($request) {
                    $q->where('kelas_id', $request->kelas_id);
                });
            }
            
            // Filter berdasarkan mata pelajaran
            if ($request->mapel_id) {
                $query->whereHas('tujuanPembelajaran.capaianPembelajaran', function($q) use ($request) {
                    $q->where('mata_pelajaran_id', $request->mapel_id);
                });
            }
            
            // Filter berdasarkan rentang tanggal
            if ($request->start_date && $request->end_date) {
                $query->whereBetween('created_at', [
                    Carbon::parse($request->start_date)->startOfDay(),
                    Carbon::parse($request->end_date)->endOfDay()
                ]);
            }
            
            $nilai = $query->get();
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan laporan nilai", $nilai);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }

    public function absensi(Request $request)
    {
        try {
            $query = AbsensiSiswa::with(['siswa.kelas', 'pertemuan'])
                ->where('sekolah_id', Auth::user()->sekolah_id);
            
            // Filter berdasarkan kelas
            if ($request->kelas_id) {
                $query->whereHas('siswa', function($q) use ($request) {
                    $q->where('kelas_id', $request->kelas_id);
                });
            }
            
            // Filter berdasarkan bulan dan tahun
            if ($request->bulan && $request->tahun) {
                $query->whereHas('pertemuan', function($q) use ($request) {
                    $q->where('bulan', $request->bulan)
                      ->where('tahun', $request->tahun);
                });
            }
            
            $absensi = $query->get();
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan laporan absensi", $absensi);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }

    public function aktivitas(Request $request)
    {
        try {
            $query = UserActivity::with('user')
                ->where('sekolah_id', Auth::user()->sekolah_id);
            
            // Filter berdasarkan user
            if ($request->user_id) {
                $query->where('user_id', $request->user_id);
            }
            
            // Filter berdasarkan rentang tanggal
            if ($request->start_date && $request->end_date) {
                $query->whereBetween('created_at', [
                    Carbon::parse($request->start_date)->startOfDay(),
                    Carbon::parse($request->end_date)->endOfDay()
                ]);
            }
            
            $aktivitas = $query->orderBy('created_at', 'desc')->get();
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan laporan aktivitas", $aktivitas);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }
} 