<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Sekolah;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware("login");
    }

    public function index(Request $request)
    {
        $sekolahId = $request->query('sekolah_id');
        $group = $request->query('group');
        
        $query = Setting::with('sekolah');
        
        if ($sekolahId) {
            $query->where('sekolah_id', $sekolahId);
        }
        
        if ($group) {
            $query->where('group', $group);
        }
        
        $data = $query->orderBy('key', 'asc')->get();
        
        return ResponseBuilder::success(200, "Berhasil Mendapatkan Data", $data, true, false);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'key' => 'required|string|max:255',
            'value' => 'required|string',
            'group' => 'nullable|string|max:255',
            'sekolah_id' => 'required|exists:sekolah,id'
        ]);

        try {
            DB::beginTransaction();
            
            // Cek apakah pengaturan dengan kunci dan sekolah_id ini sudah ada
            $existing = Setting::where('key', $request->key)
                            ->where('sekolah_id', $request->sekolah_id)
                            ->first();
            
            if ($existing) {
                return ResponseBuilder::error(400, "Pengaturan dengan kunci '{$request->key}' untuk sekolah ini sudah ada");
            }
            
            // Buat pengaturan baru
            $setting = Setting::create([
                'key' => $request->key,
                'value' => $request->value,
                'group' => $request->group,
                'sekolah_id' => $request->sekolah_id
            ]);
            
            DB::commit();
            
            $setting->load('sekolah');
            
            return ResponseBuilder::success(201, "Berhasil Menambahkan Data", $setting, true);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal Menambahkan Data: " . $e->getMessage());
        }
    }

    public function show($id)
    {
        $setting = Setting::with('sekolah')->find($id);
        
        if (!$setting) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        return ResponseBuilder::success(200, "Berhasil Mendapatkan Data", $setting, true);
    }

    public function update(Request $request, $id)
    {
        $setting = Setting::find($id);
        
        if (!$setting) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        $this->validate($request, [
            'key' => 'sometimes|required|string|max:255',
            'value' => 'sometimes|required|string',
            'group' => 'nullable|string|max:255'
        ]);
        
        try {
            DB::beginTransaction();
            
            // Jika key diubah, cek apakah key baru sudah ada untuk sekolah yang sama
            if ($request->has('key') && $request->key !== $setting->key) {
                $exists = Setting::where('key', $request->key)
                               ->where('sekolah_id', $setting->sekolah_id)
                               ->where('id', '!=', $id)
                               ->exists();
                
                if ($exists) {
                    return ResponseBuilder::error(400, "Pengaturan dengan kunci '{$request->key}' untuk sekolah ini sudah ada");
                }
            }
            
            // Update pengaturan
            $setting->update($request->only(['key', 'value', 'group']));
            
            DB::commit();
            
            $setting->load('sekolah');
            
            return ResponseBuilder::success(200, "Berhasil Mengubah Data", $setting, true);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal Mengubah Data: " . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $setting = Setting::find($id);
        
        if (!$setting) {
            return ResponseBuilder::error(404, "Data Tidak ada");
        }
        
        try {
            $setting->delete();
            return ResponseBuilder::success(200, "Berhasil Menghapus Data", null, true);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal Menghapus Data: " . $e->getMessage());
        }
    }
    
    public function getByKey(Request $request)
    {
        $this->validate($request, [
            'key' => 'required|string',
            'sekolah_id' => 'required|exists:sekolah,id'
        ]);
        
        $setting = Setting::where('key', $request->key)
                        ->where('sekolah_id', $request->sekolah_id)
                        ->first();
        
        if (!$setting) {
            return ResponseBuilder::error(404, "Pengaturan dengan kunci '{$request->key}' tidak ditemukan");
        }
        
        return ResponseBuilder::success(200, "Berhasil Mendapatkan Data", $setting, true);
    }
    
    public function getByGroup(Request $request)
    {
        $this->validate($request, [
            'group' => 'required|string',
            'sekolah_id' => 'required|exists:sekolah,id'
        ]);
        
        $settings = Setting::where('group', $request->group)
                         ->where('sekolah_id', $request->sekolah_id)
                         ->get();
        
        return ResponseBuilder::success(200, "Berhasil Mendapatkan Data", $settings, true);
    }
    
    public function bulkUpdate(Request $request)
    {
        $this->validate($request, [
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'required|string',
            'settings.*.group' => 'nullable|string',
            'sekolah_id' => 'required|exists:sekolah,id'
        ]);
        
        try {
            DB::beginTransaction();
            
            $sekolahId = $request->sekolah_id;
            $updated = [];
            
            foreach ($request->settings as $item) {
                // Gunakan metode statis dari model Setting
                Setting::setValue(
                    $item['key'],
                    $item['value'],
                    $sekolahId,
                    $item['group'] ?? null
                );
                
                $setting = Setting::where('key', $item['key'])
                               ->where('sekolah_id', $sekolahId)
                               ->first();
                
                if ($setting) {
                    $updated[] = $setting;
                }
            }
            
            DB::commit();
            
            return ResponseBuilder::success(200, "Berhasil Memperbarui Pengaturan", $updated, true);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal Memperbarui Pengaturan: " . $e->getMessage());
        }
    }
} 