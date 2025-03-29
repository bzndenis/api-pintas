<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class RekapBulanan extends Model
{
    protected $table = 'rekap_bulanan';
    protected $keyType = 'string';
    public $incrementing = false;
    
    protected $fillable = [
        'id', 
        'siswa_id', 
        'kelas_id', 
        'mata_pelajaran_id', 
        'bulan', 
        'tahun', 
        'total_pertemuan',
        'hadir', 
        'izin', 
        'sakit', 
        'absen',
        'created_by', 
        'sekolah_id'
    ];
    
    protected $casts = [
        'bulan' => 'integer',
        'tahun' => 'integer',
        'total_pertemuan' => 'integer',
        'hadir' => 'integer',
        'izin' => 'integer',
        'sakit' => 'integer',
        'absen' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    public static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->id = Uuid::uuid4()->toString();
        });
    }
    
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
    
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class);
    }
} 