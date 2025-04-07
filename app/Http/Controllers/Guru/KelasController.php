<?php

namespace App\Http\Controllers\Guru;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Uuid;

class KelasController extends BaseGuruController
{
    public function index(Request $request)
    {
        try {
            $guru = Auth::user()->guru;
            
            // Debug: Log ID guru
            \Log::info('ID Guru yang login: ' . $guru->id);
            
            // Buat query builder
            $query = Kelas::where('guru_id', $guru->id)
                ->whereNull('deleted_at');
            
            // Debug: Log query yang akan dijalankan
            \Log::info('Query Kelas:', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);
            
            // Jalankan query
            $kelas = $query->get();
            
            // Debug: Log hasil query
            \Log::info('Hasil Query:', [
                'total' => $kelas->count(),
                'data' => $kelas->toArray()
            ]);
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan data kelas", $kelas);
        } catch (\Exception $e) {
            \Log::error('Error di KelasController@index: ' . $e->getMessage());
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
                ->where('guru_id', $guru->id)
                ->find($id);
            
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
            
            $kelas = Kelas::where('guru_id', $guru->id)
                ->find($id);
            
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