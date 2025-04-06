<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Pertemuan extends Model
{
    protected $table = 'pertemuan';
    
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'id',
        'kelas_id',
        'mata_pelajaran_id',
        'guru_id',
        'tanggal',
        'pertemuan_ke',
        'materi',
        'sekolah_id',
        'created_by'
    ];
    
    protected $casts = [
        'tanggal' => 'date',
        'pertemuan_ke' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    protected $dates = [
        'tanggal',
        'created_at',
        'updated_at'
    ];
    
    public static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->id = Uuid::uuid4()->toString();
        });
    }
    
    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }
    
    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
    }
    
    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }
    
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class);
    }
    
    public function absensiSiswa()
    {
        return $this->hasMany(AbsensiSiswa::class, 'pertemuan_id');
    }
} 