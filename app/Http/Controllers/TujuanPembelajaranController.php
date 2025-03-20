<?php

namespace App\Http\Controllers;

use App\Models\TujuanPembelajaran;
use App\Models\CapaianPembelajaran;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\DB;

class TujuanPembelajaranController extends Controller
{
    public function __construct()
    {
        $this->middleware("login");
    }

    public function index(Request $request)
    {
        try {
            $query = TujuanPembelajaran::with(['capaianPembelajaran.mataPelajaran']);
            
            if ($request->cp_id) {
                $query->where('cp_id', $request->cp_id);
            }

            if ($request->sekolah_id) {
                $query->where('sekolah_id', $request->sekolah_id);
            }

            $tujuan = $query->orderBy('created_at', 'desc')->get();

            $formattedData = [
                'total' => $tujuan->count(),
                'tujuan_pembelajaran' => $tujuan->map(function($item) {
                    return [
                        'id' => $item->id,
                        'kode_tp' => $item->kode_tp,
                        'deskripsi' => $item->deskripsi,
                        'bobot' => $item->bobot,
                        'capaian_pembelajaran' => [
                            'id' => $item->capaianPembelajaran->id,
                            'kode_cp' => $item->capaianPembelajaran->kode_cp,
                            'mata_pelajaran' => [
                                'id' => $item->capaianPembelajaran->mataPelajaran->id,
                                'nama_mapel' => $item->capaianPembelajaran->mataPelajaran->nama_mapel
                            ]
                        ]
                    ];
                })
            ];

            return ResponseBuilder::success(200, "Berhasil mendapatkan data", $formattedData);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mengambil data: " . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'kode_tp' => 'required|string|max:50',
            'deskripsi' => 'required|string',
            'bobot' => 'required|numeric|min:0|max:100',
            'cp_id' => 'required|exists:capaian_pembelajaran,id',
            'sekolah_id' => 'required|exists:sekolah,id'
        ]);

        try {
            DB::beginTransaction();
            
            // Cek apakah kode TP sudah ada untuk CP yang sama
            $exists = TujuanPembelajaran::where('kode_tp', $request->kode_tp)
                                      ->where('cp_id', $request->cp_id)
                                      ->exists();
            
            if ($exists) {
                return ResponseBuilder::error(400, "Kode TP sudah digunakan untuk capaian pembelajaran ini");
            }
            
            $tp = TujuanPembelajaran::create($request->all());
            
            DB::commit();
            
            $tp->load(['capaianPembelajaran.mataPelajaran', 'sekolah']);
            
            return ResponseBuilder::success(201, "Berhasil menambah data", $tp);
        } catch (\Exception $e) {
            DB::rollback();
            return ResponseBuilder::error(500, "Gagal menambah data: " . $e->getMessage());
        }
    }

    public function show($id)
    {
        $tp = TujuanPembelajaran::with(['capaianPembelajaran.mataPelajaran', 'sekolah'])->find($id);
        
        if (!$tp) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        return ResponseBuilder::success(200, "Berhasil Mendapatkan Data", $tp, true);
    }

    public function update(Request $request, $id)
    {
        $tp = TujuanPembelajaran::find($id);
        
        if (!$tp) {
            return ResponseBuilder::error(404, "Data tidak ditemukan");
        }

        $this->validate($request, [
            'kode_tp' => 'sometimes|required|string|max:50',
            'deskripsi' => 'sometimes|required|string',
            'bobot' => 'sometimes|required|numeric|min:0|max:100'
        ]);

        try {
            DB::beginTransaction();
            
            if ($request->has('kode_tp') && $request->kode_tp != $tp->kode_tp) {
                $exists = TujuanPembelajaran::where('kode_tp', $request->kode_tp)
                                          ->where('cp_id', $tp->cp_id)
                                          ->where('id', '!=', $id)
                                          ->exists();
                
                if ($exists) {
                    return ResponseBuilder::error(400, "Kode TP sudah digunakan");
                }
            }
            
            $tp->update($request->all());
            
            DB::commit();
            
            $tp->load(['capaianPembelajaran.mataPelajaran', 'sekolah']);
            
            return ResponseBuilder::success(200, "Berhasil mengubah data", $tp);
        } catch (\Exception $e) {
            DB::rollback();
            return ResponseBuilder::error(500, "Gagal mengubah data: " . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $tp = TujuanPembelajaran::find($id);
            
            if (!$tp) {
                return ResponseBuilder::error(404, "Data tidak ditemukan");
            }

            // Cek apakah TP masih memiliki nilai siswa
            if ($tp->nilaiSiswa()->count() > 0) {
                return ResponseBuilder::error(400, "Tidak dapat menghapus tujuan pembelajaran yang masih memiliki nilai siswa");
            }

            $tp->delete();
            return ResponseBuilder::success(200, "Berhasil menghapus data");
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal menghapus data: " . $e->getMessage());
        }
    }
} 