<?php

namespace App\Http\Controllers;

use App\Models\PertemuanBulanan;
use App\Models\Kelas;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PertemuanBulananController extends Controller
{
    public function __construct()
    {
        $this->middleware("login");
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $sekolahId = $user->sekolah_id;
        
        $query = PertemuanBulanan::with(['kelas'])
            ->where('sekolah_id', $sekolahId);
            
        // Filter berdasarkan kelas_id jika ada
        if ($request->has('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }
        
        // Filter berdasarkan bulan jika ada
        if ($request->has('bulan')) {
            $query->where('bulan', $request->bulan);
        }
        
        // Filter berdasarkan tahun jika ada
        if ($request->has('tahun')) {
            $query->where('tahun', $request->tahun);
        }
        
        $pertemuan = $query->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $pertemuan
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $sekolahId = $user->sekolah_id;
        
        $validator = Validator::make($request->all(), [
            'kelas_id' => 'required|exists:kelas,id',
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000|max:2100',
            'total_pertemuan' => 'required|integer|min:1',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }
        
        // Cek apakah kelas berada di sekolah yang sama dengan user
        $kelas = Kelas::find($request->kelas_id);
        
        if ($kelas->sekolah_id != $sekolahId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kelas tidak ditemukan di sekolah ini'
            ], 403);
        }
        
        // Cek apakah sudah ada pertemuan untuk kelas, bulan, dan tahun ini
        $existingPertemuan = PertemuanBulanan::where('kelas_id', $request->kelas_id)
            ->where('bulan', $request->bulan)
            ->where('tahun', $request->tahun)
            ->where('sekolah_id', $sekolahId)
            ->first();
            
        if ($existingPertemuan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pertemuan untuk kelas, bulan, dan tahun ini sudah ada'
            ], 400);
        }
        
        $pertemuan = new PertemuanBulanan();
        $pertemuan->kelas_id = $request->kelas_id;
        $pertemuan->bulan = $request->bulan;
        $pertemuan->tahun = $request->tahun;
        $pertemuan->total_pertemuan = $request->total_pertemuan;
        $pertemuan->created_by = $user->id;
        $pertemuan->sekolah_id = $sekolahId;
        $pertemuan->save();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Pertemuan berhasil disimpan',
            'data' => $pertemuan
        ], 201);
    }

    public function show($id)
    {
        $user = Auth::user();
        $sekolahId = $user->sekolah_id;
        
        $pertemuan = PertemuanBulanan::with(['kelas'])
            ->where('id', $id)
            ->where('sekolah_id', $sekolahId)
            ->first();
            
        if (!$pertemuan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pertemuan tidak ditemukan'
            ], 404);
        }
        
        return response()->json([
            'status' => 'success',
            'data' => $pertemuan
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $sekolahId = $user->sekolah_id;
        
        $validator = Validator::make($request->all(), [
            'total_pertemuan' => 'required|integer|min:1',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }
        
        $pertemuan = PertemuanBulanan::where('id', $id)
            ->where('sekolah_id', $sekolahId)
            ->first();
            
        if (!$pertemuan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pertemuan tidak ditemukan'
            ], 404);
        }
        
        $pertemuan->total_pertemuan = $request->total_pertemuan;
        $pertemuan->save();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Pertemuan berhasil diperbarui',
            'data' => $pertemuan
        ]);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $sekolahId = $user->sekolah_id;
        
        $pertemuan = PertemuanBulanan::where('id', $id)
            ->where('sekolah_id', $sekolahId)
            ->first();
            
        if (!$pertemuan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pertemuan tidak ditemukan'
            ], 404);
        }
        
        // Cek apakah ada absensi yang terkait dengan pertemuan ini
        if ($pertemuan->absensiSiswa()->count() > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak dapat menghapus pertemuan karena masih ada absensi yang terkait'
            ], 400);
        }
        
        $pertemuan->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Pertemuan berhasil dihapus'
        ]);
    }

    public function getByKelas($kelasId)
    {
        $user = Auth::user();
        $sekolahId = $user->sekolah_id;
        
        $kelas = Kelas::where('id', $kelasId)
            ->where('sekolah_id', $sekolahId)
            ->first();
            
        if (!$kelas) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kelas tidak ditemukan'
            ], 404);
        }
        
        $pertemuan = PertemuanBulanan::where('kelas_id', $kelasId)
            ->where('sekolah_id', $sekolahId)
            ->get();
            
        return response()->json([
            'status' => 'success',
            'data' => [
                'kelas' => $kelas,
                'pertemuan' => $pertemuan
            ]
        ]);
    }
} 