<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class Siswa extends Model
{
    use SoftDeletes;
    
    protected $table = 'siswa';
    
    protected $fillable = [
        'id',
        'nis',
        'nisn',
        'nama',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'nama_ortu',
        'no_telp_ortu',
        'kelas_id',
        'sekolah_id',
    ];
    
    protected $casts = [
        'tanggal_lahir' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
    protected $dates = [
        'tanggal_lahir',
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
} 