<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class TahunAjaran extends Model
{
    use SoftDeletes;
    
    protected $table = 'tahun_ajaran';
    
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'id',
        'nama_tahun_ajaran',
        'tanggal_mulai',
        'tanggal_selesai',
        'sekolah_id',
        'is_active',
    ];
    
    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
    protected $dates = [
        'tanggal_mulai',
        'tanggal_selesai',
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
                $model->is_active = false;
            }
        });
    }
    
    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class);
    }
    
    public function kelas()
    {
        return $this->hasMany(Kelas::class);
    }
} 