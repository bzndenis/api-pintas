<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class UserActivity extends Model
{
    protected $table = 'user_activities';
    
    protected $fillable = [
        'id',
        'user_id',
        'action',
        'ip_address',
        'user_agent',
        'duration',
        'sekolah_id',
        'created_at',
        'updated_at'
    ];
    
    protected $casts = [
        'duration' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    protected $dates = [
        'created_at',
        'updated_at',
    ];
    
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = Uuid::uuid4()->toString();
            }
        });
    }
    
    public function user()
    {
        return $this->belongsTo(UserAuth::class, 'user_id');
    }
    
    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class, 'sekolah_id');
    }
} 