<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class AbsensiSiswa extends Model
{
    protected $table = 'absensi_siswa';
    
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'id',
        'siswa_id',
        'pertemuan_id',
        'hadir',
        'izin',
        'sakit',
        'absen',
        'created_by',
        'sekolah_id',
    ];
    
    protected $casts = [
        'hadir' => 'integer',
        'izin' => 'integer',
        'sakit' => 'integer',
        'absen' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    protected $dates = [
        'created_at',
        'updated_at',
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
    
    public function pertemuan()
    {
        return $this->belongsTo(PertemuanBulanan::class, 'pertemuan_id');
    }
    
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class);
    }
    
    // Accessor untuk mendapatkan total kehadiran
    public function getTotalKehadiranAttribute()
    {
        return $this->hadir + $this->izin + $this->sakit + $this->absen;
    }
    
    // Accessor untuk mendapatkan persentase kehadiran
    public function getPersentaseKehadiranAttribute()
    {
        $total = $this->getTotalKehadiranAttribute();
        return $total > 0 ? round(($this->hadir / $total) * 100, 2) : 0;
    }
} 