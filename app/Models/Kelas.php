<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class Kelas extends Model
{
    use SoftDeletes;
    
    protected $table = 'kelas';
    
    protected $fillable = [
        'id',
        'nama_kelas',
        'tingkat',
        'tahun_ajaran_id',
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
    
    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }
    
    public function guru()
    {
        return $this->belongsTo(Guru::class);
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
} 