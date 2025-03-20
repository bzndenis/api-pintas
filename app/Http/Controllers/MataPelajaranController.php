<?php

namespace App\Http\Controllers;

use App\Models\MataPelajaran;
use App\Models\CapaianPembelajaran;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MataPelajaranController extends Controller
{
    public function __construct()
    {
        $this->middleware("login");
    }

    public function index(Request $request)
    {
        try {
            $query = MataPelajaran::with(['sekolah', 'capaianPembelajaran']);
            
            if ($request->sekolah_id) {
                $query->where('sekolah_id', $request->sekolah_id);
            }
            
            if ($request->tingkat) {
                $query->where('tingkat', $request->tingkat);
            }

            $mapel = $query->orderBy('created_at', 'desc')->get();

            $formattedData = [
                'total' => $mapel->count(),
                'mata_pelajaran' => $mapel->map(function($item) {
                    return [
                        'id' => $item->id,
                        'kode_mapel' => $item->kode_mapel,
                        'nama_mapel' => $item->nama_mapel,
                        'tingkat' => $item->tingkat,
                        'jumlah_cp' => $item->capaianPembelajaran->count(),
                        'sekolah' => $item->sekolah ? [
                            'id' => $item->sekolah->id,
                            'nama_sekolah' => $item->sekolah->nama_sekolah
                        ] : null
                    ];
                })
            ];

            return ResponseBuilder::success(200, "Berhasil mendapatkan data", $formattedData);
        } catch (\Exception $e) {
            \Log::error('Gagal mengambil data mapel: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return ResponseBuilder::error(500, "Gagal mengambil data: " . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'kode_mapel' => 'required|string|max:20',
            'nama_mapel' => 'required|string|max:255',
            'tingkat' => 'required|string',
            'sekolah_id' => 'required|exists:sekolah,id'
        ]);

        try {
            DB::beginTransaction();
            
            // Cek apakah kode mapel sudah ada untuk sekolah yang sama
            $exists = MataPelajaran::where('kode_mapel', $request->kode_mapel)
                                 ->where('sekolah_id', $request->sekolah_id)
                                 ->exists();
            
            if ($exists) {
                return ResponseBuilder::error(400, "Kode mapel sudah digunakan di sekolah ini");
            }
            
            $mapel = MataPelajaran::create($request->all());
            
            DB::commit();
            
            $mapel->load('sekolah');
            
            return ResponseBuilder::success(201, "Berhasil Menambahkan Data", $mapel, true);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal Menambahkan Data: " . $e->getMessage());
        }
    }

    public function show($id)
    {
        $mapel = MataPelajaran::with('sekolah')->find($id);
        
        if (!$mapel) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        return ResponseBuilder::success(200, "Berhasil Mendapatkan Data", $mapel, true);
    }

    public function update(Request $request, $id)
    {
        $mapel = MataPelajaran::find($id);
        
        if (!$mapel) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        $this->validate($request, [
            'kode_mapel' => 'sometimes|required|string|max:20',
            'nama_mapel' => 'sometimes|required|string|max:255',
            'tingkat' => 'sometimes|required|string|max:20',
            'sekolah_id' => 'sometimes|required|exists:sekolah,id'
        ]);
        
        try {
            DB::beginTransaction();
            
            // Cek apakah kode mapel sudah ada untuk sekolah yang sama (kecuali diri sendiri)
            if ($request->has('kode_mapel') && $request->kode_mapel != $mapel->kode_mapel) {
                $exists = MataPelajaran::where('kode_mapel', $request->kode_mapel)
                                     ->where('sekolah_id', $request->sekolah_id ?? $mapel->sekolah_id)
                                     ->where('id', '!=', $id)
                                     ->exists();
                
                if ($exists) {
                    return ResponseBuilder::error(400, "Kode mapel sudah digunakan di sekolah ini");
                }
            }
            
            $mapel->update($request->all());
            
            DB::commit();
            
            $mapel->load('sekolah');
            
            return ResponseBuilder::success(200, "Berhasil Mengubah Data", $mapel, true);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal Mengubah Data: " . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $mapel = MataPelajaran::find($id);
            
            if (!$mapel) {
                return ResponseBuilder::error(404, "Data tidak ditemukan");
            }

            // Cek apakah mapel masih memiliki CP
            if ($mapel->capaianPembelajaran()->count() > 0) {
                return ResponseBuilder::error(400, "Tidak dapat menghapus mata pelajaran yang masih memiliki capaian pembelajaran");
            }

            $mapel->delete();
            return ResponseBuilder::success(200, "Berhasil menghapus data");
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal menghapus data: " . $e->getMessage());
        }
    }
    
    public function getCapaianPembelajaran($id)
    {
        try {
            $mapel = MataPelajaran::with(['capaianPembelajaran.tujuanPembelajaran'])
                ->find($id);

            if (!$mapel) {
                return ResponseBuilder::error(404, "Data tidak ditemukan");
            }

            $formattedData = [
                'mata_pelajaran' => [
                    'id' => $mapel->id,
                    'kode_mapel' => $mapel->kode_mapel,
                    'nama_mapel' => $mapel->nama_mapel
                ],
                'capaian_pembelajaran' => $mapel->capaianPembelajaran->map(function($cp) {
                    return [
                        'id' => $cp->id,
                        'kode_cp' => $cp->kode_cp,
                        'deskripsi' => $cp->deskripsi,
                        'jumlah_tp' => $cp->tujuanPembelajaran->count(),
                        'tujuan_pembelajaran' => $cp->tujuanPembelajaran->map(function($tp) {
                            return [
                                'id' => $tp->id,
                                'kode_tp' => $tp->kode_tp,
                                'deskripsi' => $tp->deskripsi,
                                'bobot' => $tp->bobot
                            ];
                        })
                    ];
                })
            ];

            return ResponseBuilder::success(200, "Berhasil mendapatkan data", $formattedData);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mengambil data: " . $e->getMessage());
        }
    }
} 