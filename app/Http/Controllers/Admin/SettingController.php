<?php

namespace App\Http\Controllers\Admin;

use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;

class SettingController extends BaseAdminController
{
    public function index(Request $request)
    {
        try {
            $query = Setting::where('sekolah_id', Auth::user()->sekolah_id);
            
            if ($request->group) {
                $query->where('group', $request->group);
            }
            
            $settings = $query->get();
            
            // Kelompokkan setting berdasarkan group
            $grouped = $settings->groupBy('group');
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan pengaturan", $grouped);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'key' => 'required|string|max:255',
            'value' => 'required|string',
            'group' => 'required|string|max:255'
        ]);

        try {
            $data = $request->all();
            $data['sekolah_id'] = Auth::user()->sekolah_id;
            
            // Cek apakah setting dengan key tersebut sudah ada
            $existing = Setting::where('sekolah_id', Auth::user()->sekolah_id)
                ->where('key', $request->key)
                ->first();
                
            if ($existing) {
                return ResponseBuilder::error(400, "Setting dengan key tersebut sudah ada");
            }
            
            $setting = Setting::create($data);
            
            return ResponseBuilder::success(201, "Berhasil menambahkan pengaturan", $setting);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal menambahkan data: " . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'value' => 'required|string',
            'group' => 'required|string|max:255'
        ]);

        try {
            $setting = Setting::where('sekolah_id', Auth::user()->sekolah_id)
                ->find($id);
            
            if (!$setting) {
                return ResponseBuilder::error(404, "Pengaturan tidak ditemukan");
            }
            
            $setting->update($request->only(['value', 'group']));
            
            return ResponseBuilder::success(200, "Berhasil mengupdate pengaturan", $setting);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mengupdate data: " . $e->getMessage());
        }
    }
} 