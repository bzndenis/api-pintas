<?php

namespace App\Http\Controllers;

use App\Models\Sekolah;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SekolahController extends Controller
{
    public function __construct()
    {
        $this->middleware("login");
    }

    public function index(Request $request)
    {
        $isActive = $request->query('is_active');
        
        $query = Sekolah::query();
        
        if ($isActive !== null) {
            $query->where('is_active', $isActive == 'true' || $isActive == '1');
        }
        
        $data = $query->orderBy('created_at', 'desc')->get();
        
        return ResponseBuilder::success(200, "Berhasil Mendapatkan Data", $data, true, false);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'nama_sekolah' => 'required|string|max:255',
            'npsn' => 'required|string|max:50|unique:sekolah',
            'alamat' => 'required|string',
            'kota' => 'required|string|max:100',
            'provinsi' => 'required|string|max:100',
            'kode_pos' => 'nullable|string|max:10',
            'no_telp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'website' => 'nullable|string|max:100',
            'kepala_sekolah' => 'nullable|string|max:100',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'is_active' => 'nullable|boolean'
        ]);

        try {
            DB::beginTransaction();
            
            $data = $request->except('logo');
            
            // Upload logo jika ada
            if ($request->hasFile('logo')) {
                $logo = $request->file('logo');
                $logoName = time() . '.' . $logo->getClientOriginalExtension();
                $path = $logo->storeAs('public/logo', $logoName);
                $data['logo'] = Storage::url($path);
            }
            
            $sekolah = Sekolah::create($data);
            
            DB::commit();
            
            return ResponseBuilder::success(201, "Berhasil Menambahkan Data", $sekolah, true);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal Menambahkan Data: " . $e->getMessage());
        }
    }

    public function show($id)
    {
        $sekolah = Sekolah::find($id);
        
        if (!$sekolah) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        return ResponseBuilder::success(200, "Berhasil Mendapatkan Data", $sekolah, true);
    }

    public function update(Request $request, $id)
    {
        $sekolah = Sekolah::find($id);
        
        if (!$sekolah) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        $this->validate($request, [
            'nama_sekolah' => 'sometimes|required|string|max:255',
            'npsn' => 'sometimes|required|string|max:50|unique:sekolah,npsn,'.$id.',id',
            'alamat' => 'sometimes|required|string',
            'kota' => 'sometimes|required|string|max:100',
            'provinsi' => 'sometimes|required|string|max:100',
            'kode_pos' => 'nullable|string|max:10',
            'no_telp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'website' => 'nullable|string|max:100',
            'kepala_sekolah' => 'nullable|string|max:100',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'is_active' => 'nullable|boolean'
        ]);
        
        try {
            DB::beginTransaction();
            
            $data = $request->except('logo');
            
            // Upload logo jika ada
            if ($request->hasFile('logo')) {
                // Hapus logo lama jika ada
                if ($sekolah->logo && Storage::exists(str_replace('/storage', 'public', $sekolah->logo))) {
                    Storage::delete(str_replace('/storage', 'public', $sekolah->logo));
                }
                
                $logo = $request->file('logo');
                $logoName = time() . '.' . $logo->getClientOriginalExtension();
                $path = $logo->storeAs('public/logo', $logoName);
                $data['logo'] = Storage::url($path);
            }
            
            $sekolah->update($data);
            
            DB::commit();
            
            return ResponseBuilder::success(200, "Berhasil Mengubah Data", $sekolah, true);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal Mengubah Data: " . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $sekolah = Sekolah::find($id);
        
        if (!$sekolah) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        try {
            // Cek apakah sekolah masih memiliki relasi dengan data lain
            if ($sekolah->users()->count() > 0 || 
                $sekolah->guru()->count() > 0 || 
                $sekolah->kelas()->count() > 0 || 
                $sekolah->siswa()->count() > 0) {
                return ResponseBuilder::error(400, "Tidak dapat menghapus sekolah yang masih memiliki data terkait");
            }
            
            $sekolah->delete();
            return ResponseBuilder::success(200, "Berhasil Menghapus Data", null, true);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal Menghapus Data: " . $e->getMessage());
        }
    }
} 