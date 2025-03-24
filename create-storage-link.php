<?php

// Tentukan path untuk symlink
$targetPath = __DIR__ . '/storage/app/public';
$linkPath = __DIR__ . '/public/storage';

// Pastikan direktori target ada
if (!file_exists($targetPath)) {
    // Buat direktori jika belum ada
    if (!mkdir($targetPath, 0777, true)) {
        echo "Gagal membuat direktori target: $targetPath\n";
        exit(1);
    }
    echo "Direktori target dibuat: $targetPath\n";
}

// Hapus symlink yang sudah ada jika ada
if (file_exists($linkPath)) {
    if (is_link($linkPath)) {
        unlink($linkPath);
        echo "Symlink lama dihapus\n";
    } else {
        echo "Path sudah ada dan bukan symlink: $linkPath\n";
        exit(1);
    }
}

// Buat symlink baru
if (symlink($targetPath, $linkPath)) {
    echo "Berhasil membuat symlink storage\n";
} else {
    echo "Gagal membuat symlink: " . error_get_last()['message'] . "\n";
    
    // Alternatif: Salin direktori jika symlink gagal
    echo "Mencoba menyalin direktori sebagai alternatif...\n";
    
    if (!file_exists($linkPath)) {
        mkdir($linkPath, 0777, true);
    }
    
    // Fungsi untuk menyalin direktori
    function copyDir($src, $dst) {
        $dir = opendir($src);
        @mkdir($dst);
        while (($file = readdir($dir)) !== false) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    copyDir($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
    
    copyDir($targetPath, $linkPath);
    echo "Direktori disalin sebagai alternatif symlink\n";
} 