<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class Sekolah extends Model
{
    use SoftDeletes;
    
    protected $table = 'sekolah';
    
    protected $fillable = [
        'id',
        'nama_sekolah',
        'npsn',
        'alamat',
        'kota',
        'provinsi',
        'kode_pos',
        'no_telp',
        'email',
        'website',
        'kepala_sekolah',
        'logo',
        'is_active',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
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
            if (!isset($model->is_active)) {
                $model->is_active = true;
            }
        });
    }
    
    public function users()
    {
        return $this->hasMany(User::class);
    }
    
    public function guru()
    {
        return $this->hasMany(Guru::class);
    }
    
    public function tahunAjaran()
    {
        return $this->hasMany(TahunAjaran::class);
    }
    
    public function kelas()
    {
        return $this->hasMany(Kelas::class);
    }
    
    public function siswa()
    {
        return $this->hasMany(Siswa::class);
    }
    
    public function mapel()
    {
        return $this->hasMany(MataPelajaran::class);
    }
    
    public function settings()
    {
        return $this->hasMany(Setting::class);
    }
} 