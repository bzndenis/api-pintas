<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class NilaiSiswa extends Model
{
    protected $table = 'nilai_siswa';
    
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'id',
        'siswa_id',
        'tp_id',
        'nilai',
        'created_by',
        'sekolah_id'
    ];
    
    protected $casts = [
        'nilai' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    protected $dates = [
        'created_at',
        'updated_at'
    ];
    
    protected $enumJenisNilai = ['S-1', 'S-2', 'S-3', 'S-4', 'S-5', 'S-6', 'S-7'];
    
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
    
    public function tujuanPembelajaran()
    {
        return $this->belongsTo(TujuanPembelajaran::class, 'tp_id');
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
} 