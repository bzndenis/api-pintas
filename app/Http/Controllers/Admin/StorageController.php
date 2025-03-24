<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Artisan;

class StorageController extends Controller
{
    public function createStorageLink()
    {
        try {
            // Tentukan path untuk symlink
            $targetPath = storage_path('app/public');
            $linkPath = base_path('public/storage');
            
            // Hapus symlink yang sudah ada jika ada
            if (file_exists($linkPath)) {
                if (is_link($linkPath)) {
                    unlink($linkPath);
                } else {
                    return ResponseBuilder::error(400, "Path sudah ada dan bukan symlink");
                }
            }
            
            // Buat symlink baru
            if (symlink($targetPath, $linkPath)) {
                return ResponseBuilder::success(200, "Berhasil membuat symlink storage");
            } else {
                return ResponseBuilder::error(500, "Gagal membuat symlink");
            }
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Error: " . $e->getMessage());
        }
    }
} 