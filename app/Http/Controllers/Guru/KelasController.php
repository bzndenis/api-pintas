<?php

namespace App\Http\Controllers\Guru;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Uuid;

class KelasController extends BaseGuruController
{
    public function index(Request $request)
    {
        try {
            $guru = Auth::user()->guru;
            
            // Ambil kelas yang diajar oleh guru
            $kelas = Kelas::whereHas('mataPelajaran', function($q) use ($guru) {
                $q->where('guru_id', $guru->id);
            })->get();
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan data kelas", $kelas);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
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
            
            $kelas = Kelas::with(['waliKelas'])
                ->whereHas('mataPelajaran', function($q) use ($guru) {
                    $q->where('guru_id', $guru->id);
                })->find($id);
            
            if (!$kelas) {
                return ResponseBuilder::error(404, "Data kelas tidak ditemukan");
            }
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan detail kelas", ['kelas' => $kelas]);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }
    
    public function listSiswa($id)
    {
        try {
            // Validasi format UUID
            if (!Uuid::isValid($id)) {
                return ResponseBuilder::error(400, "Format ID tidak valid");
            }

            $guru = Auth::user()->guru;
            
            $kelas = Kelas::whereHas('mataPelajaran', function($q) use ($guru) {
                $q->where('guru_id', $guru->id);
            })->find($id);
            
            if (!$kelas) {
                return ResponseBuilder::error(404, "Data kelas tidak ditemukan");
            }
            
            $siswa = Siswa::where('kelas_id', $id)->get();
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan data siswa", [
                'kelas' => $kelas,
                'siswa' => $siswa
            ]);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }
} 