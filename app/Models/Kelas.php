<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class Kelas extends Model
{
    use SoftDeletes;
    
    protected $table = 'kelas';
    
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'id',
        'nama_kelas',
        'tingkat',
        'tahun',
        'guru_id',
        'sekolah_id',
    ];
    
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    
    public static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->id = Uuid::uuid4()->toString();
        });
    }
    
    public function waliKelas()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }
    
    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class);
    }
    
    public function siswa()
    {
        return $this->hasMany(Siswa::class);
    }
    
    public function pertemuanBulanan()
    {
        return $this->hasMany(PertemuanBulanan::class);
    }

    /**
     * Relasi ke model TahunAjaran
     */
    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }

    /**
     * Relasi ke model Pertemuan
     */
    public function pertemuan()
    {
        return $this->hasMany(Pertemuan::class);
    }

    /**
     * Relasi ke model MataPelajaran melalui tabel pertemuan
     */
    public function mataPelajaran()
    {
        return $this->hasManyThrough(
            MataPelajaran::class,
            Pertemuan::class,
            'kelas_id',
            'id',
            'id',
            'mata_pelajaran_id'
        );
    }
} 