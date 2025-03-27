<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekapBulanan extends Model
{
    protected $table = 'rekap_bulanan';
    protected $keyType = 'string';
    public $incrementing = false;
    
    protected $fillable = [
        'id', 'siswa_id', 'kelas_id', 'mata_pelajaran_id', 
        'bulan', 'tahun', 'total_pertemuan',
        'hadir', 'izin', 'sakit', 'absen',
        'created_by', 'sekolah_id'
    ];
    
    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
    
    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }
    
    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class);
    }
} 