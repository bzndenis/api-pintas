<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class Siswa extends Model
{
    use SoftDeletes;
    
    protected $table = 'siswa';
    
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'id',
        'nisn',
        'nama',
        'jenis_kelamin',
        'kelas_id',
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
    
    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }
    
    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class);
    }
    
    public function nilai()
    {
        return $this->hasMany(NilaiSiswa::class);
    }
    
    public function absensi()
    {
        return $this->hasMany(AbsensiSiswa::class);
    }

    /**
     * Relasi ke nilai siswa
     */
    public function nilaiSiswa()
    {
        return $this->hasMany(NilaiSiswa::class, 'siswa_id');
    }
} 