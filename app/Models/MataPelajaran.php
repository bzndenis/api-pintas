<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class MataPelajaran extends Model
{
    use SoftDeletes;
    
    protected $table = 'mata_pelajaran';
    
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'id',
        'kode_mapel',
        'nama_mapel',
        'tingkat',
        'sekolah_id',
        'guru_id',
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
    
    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class);
    }
    
    public function capaianPembelajaran()
    {
        return $this->hasMany(CapaianPembelajaran::class, 'mapel_id');
    }
    
    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function pertemuanBulanan()
    {
        return $this->hasMany(PertemuanBulanan::class, 'mata_pelajaran_id');
    }

    public function kelas()
    {
        return $this->belongsToMany(Kelas::class, 'pertemuan', 'mata_pelajaran_id', 'kelas_id');
    }
} 