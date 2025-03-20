<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\UserActivity;
use App\Models\UserSession;
use Illuminate\Support\Facades\Storage;

// Import fungsi global
use function base_path;

class CleanupActivityLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activity:cleanup {--days=30} {--export-json=true} {--delete-db=false}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membersihkan log aktivitas pengguna yang lebih lama dari jumlah hari tertentu';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $days = $this->option('days');
        $exportJson = $this->option('export-json') === 'true';
        $deleteDb = $this->option('delete-db') === 'true';
        
        $cutoffDate = Carbon::now()->subDays($days);
        
        $this->info("Mulai membersihkan log aktivitas yang lebih lama dari {$days} hari ({$cutoffDate->format('Y-m-d')})");
        
        // Export ke JSON sebelum menghapus
        if ($exportJson) {
            $this->exportActivitiesToJson($cutoffDate);
            $this->exportSessionsToJson($cutoffDate);
        }
        
        // Hapus dari database jika diminta
        if ($deleteDb) {
            $this->deleteActivitiesFromDb($cutoffDate);
            $this->deleteSessionsFromDb($cutoffDate);
        }
        
        // Hapus file JSON yang sudah terlalu lama
        $this->removeOldJsonFiles($cutoffDate);
        
        $this->info("Pembersihan log aktivitas selesai.");
        
        return 0;
    }
    
    /**
     * Export aktivitas ke file JSON sebelum dihapus
     */
    private function exportActivitiesToJson($cutoffDate)
    {
        $this->info("Mengekspor aktivitas lama ke file JSON...");
        
        // Ambil semua aktivitas lama
        $activities = UserActivity::where('created_at', '<', $cutoffDate)
            ->orderBy('created_at')
            ->chunk(1000, function ($chunk) {
                // Kelompokkan berdasarkan tanggal
                $grouped = $chunk->groupBy(function($activity) {
                    return Carbon::parse($activity->created_at)->format('Y-m-d');
                });
                
                foreach ($grouped as $date => $items) {
                    $filename = base_path('public') . "/logs/archives/activities/{$date}.json";
                    
                    // Simpan ke file
                    $this->saveToJsonFile($filename, $items->toArray());
                }
            });
        
        $this->info("Ekspor aktivitas lama selesai");
    }
    
    /**
     * Export sesi ke file JSON sebelum dihapus
     */
    private function exportSessionsToJson($cutoffDate)
    {
        $this->info("Mengekspor sesi lama ke file JSON...");
        
        // Ambil semua sesi lama
        UserSession::where('login_time', '<', $cutoffDate)
            ->orderBy('login_time')
            ->chunk(1000, function ($chunk) {
                // Kelompokkan berdasarkan tanggal
                $grouped = $chunk->groupBy(function($session) {
                    return Carbon::parse($session->login_time)->format('Y-m-d');
                });
                
                foreach ($grouped as $date => $items) {
                    $filename = base_path('public') . "/logs/archives/sessions/{$date}.json";
                    
                    // Simpan ke file
                    $this->saveToJsonFile($filename, $items->toArray());
                }
            });
        
        $this->info("Ekspor sesi lama selesai");
    }
    
    /**
     * Simpan data ke file JSON
     */
    private function saveToJsonFile($filename, $data)
    {
        // Periksa apakah file sudah ada
        if (file_exists($filename)) {
            // Baca file yang sudah ada
            $currentData = json_decode(file_get_contents($filename), true);
            if (!is_array($currentData)) {
                $currentData = [];
            }
            
            // Tambahkan data baru
            $currentData = array_merge($currentData, $data);
            
            // Simpan kembali file
            file_put_contents($filename, json_encode($currentData, JSON_PRETTY_PRINT));
        } else {
            // Buat direktori jika belum ada
            $directory = dirname($filename);
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
            
            // Simpan data ke file baru
            file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));
        }
    }
    
    /**
     * Hapus aktivitas lama dari database
     */
    private function deleteActivitiesFromDb($cutoffDate)
    {
        $this->info("Menghapus aktivitas lama dari database...");
        
        $count = UserActivity::where('created_at', '<', $cutoffDate)->count();
        UserActivity::where('created_at', '<', $cutoffDate)->delete();
        
        $this->info("Berhasil menghapus {$count} aktivitas lama");
    }
    
    /**
     * Hapus sesi lama dari database
     */
    private function deleteSessionsFromDb($cutoffDate)
    {
        $this->info("Menghapus sesi lama dari database...");
        
        $count = UserSession::where('login_time', '<', $cutoffDate)->count();
        UserSession::where('login_time', '<', $cutoffDate)->delete();
        
        $this->info("Berhasil menghapus {$count} sesi lama");
    }
    
    /**
     * Hapus file JSON lama
     */
    private function removeOldJsonFiles($cutoffDate)
    {
        $this->info("Menghapus file JSON lama...");
        
        // Cari semua file JSON dalam direktori logs/activities
        $files = glob(base_path('public') . '/logs/activities/*.json');
        
        $deletedCount = 0;
        
        foreach ($files as $file) {
            $date = pathinfo($file, PATHINFO_FILENAME);
            $fileDate = Carbon::createFromFormat('Y-m-d', $date);
            
            // Hapus file jika lebih lama dari cutoff date
            if ($fileDate->lt($cutoffDate)) {
                unlink($file);
                $deletedCount++;
            }
        }
        
        $this->info("Berhasil menghapus {$deletedCount} file log");
    }
} 