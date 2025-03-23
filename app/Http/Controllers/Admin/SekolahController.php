<?php

namespace App\Http\Controllers\Admin;

use App\Models\Sekolah;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Uuid;

class SekolahController extends BaseAdminController
{
    public function index(Request $request)
    {
        try {
            $query = Sekolah::query();
            
            // Filter berdasarkan nama
            if ($request->nama) {
                $query->where('nama', 'like', "%{$request->nama}%");
            }
            
            $sekolah = $query->orderBy('created_at', 'desc')->get();
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan data sekolah", $sekolah);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'nama_sekolah' => 'required|string|max:255',
            'npsn' => 'required|string|unique:sekolah,npsn',
            'alamat' => 'required|string',
            'kota' => 'required|string|max:255',
            'provinsi' => 'required|string|max:255',
            'kode_pos' => 'nullable|string|max:255',
            'no_telp' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'kepala_sekolah' => 'nullable|string|max:255',
            'logo' => 'nullable|image|max:2048'
        ]);

        try {
            $data = $request->all();
            
            if ($request->hasFile('logo')) {
                $logo = $request->file('logo');
                $path = $logo->store('public/logo');
                $data['logo'] = $path;
            }
            
            $sekolah = Sekolah::create($data);
            
            return ResponseBuilder::success(201, "Berhasil menambahkan sekolah", $sekolah);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal menambahkan data: " . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            // Validasi format UUID
            if (!Uuid::isValid($id)) {
                return ResponseBuilder::error(400, "Format ID tidak valid");
            }

            $sekolah = Sekolah::with(['guru', 'kelas', 'siswa', 'mapel'])
                ->find($id);
            
            if (!$sekolah) {
                return ResponseBuilder::error(404, "Data sekolah tidak ditemukan");
            }
            
            // Kirim data dalam bentuk array dengan satu item
            return ResponseBuilder::success(200, "Berhasil mendapatkan data sekolah", ['sekolah' => $sekolah]);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'nama_sekolah' => 'required|string|max:255',
            'alamat' => 'required|string',
            'kota' => 'required|string|max:255',
            'provinsi' => 'required|string|max:255',
            'kode_pos' => 'nullable|string|max:255',
            'no_telp' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'kepala_sekolah' => 'nullable|string|max:255',
            'logo' => 'nullable|image|max:2048'
        ]);

        try {
            $sekolah = Sekolah::find($id);
            
            if (!$sekolah) {
                return ResponseBuilder::error(404, "Sekolah tidak ditemukan");
            }
            
            $data = $request->all();
            
            if ($request->hasFile('logo')) {
                $logo = $request->file('logo');
                $path = $logo->store('public/logo');
                $data['logo'] = $path;
            }
            
            $sekolah->update($data);
            
            return ResponseBuilder::success(200, "Berhasil mengupdate sekolah", $sekolah);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mengupdate data: " . $e->getMessage());
        }
    }
} 