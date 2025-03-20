<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class TujuanPembelajaran extends Model
{
    use SoftDeletes;
    
    protected $table = 'tujuan_pembelajaran';
    
    protected $fillable = [
        'id',
        'kode_tp',
        'deskripsi',
        'bobot',
        'cp_id',
        'sekolah_id',
    ];
    
    protected $casts = [
        'bobot' => 'decimal:2',
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
    
    public function capaianPembelajaran()
    {
        return $this->belongsTo(CapaianPembelajaran::class, 'cp_id');
    }
    
    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class);
    }
    
    public function nilaiSiswa()
    {
        return $this->hasMany(NilaiSiswa::class, 'tp_id');
    }
} 