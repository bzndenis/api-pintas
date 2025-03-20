<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class Guru extends Model
{
    use SoftDeletes;
    
    protected $table = 'guru';
    
    protected $fillable = [
        'id',
        'nip',
        'nama',
        'email',
        'no_telp',
        'user_id',
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
    
    public function user()
    {
        return $this->belongsTo(UserAuth::class, 'user_id', 'id');
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