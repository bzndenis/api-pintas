<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class UserSession extends Model
{
    protected $table = 'user_sessions';
    
    protected $fillable = [
        'user_id',
        'login_time',
        'last_activity',
        'duration',
        'status',
        'ip_address',
        'user_agent',
        'sekolah_id',
        'created_at',
        'updated_at'
    ];
    
    protected $casts = [
        'duration' => 'integer',
        'login_time' => 'datetime',
        'last_activity' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    public $incrementing = false;
    protected $keyType = 'string';
    
    public static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->id = Uuid::uuid4()->toString();
            if (!isset($model->status)) {
                $model->status = 'active';
            }
        });
    }
    
    public function user()
    {
        return $this->belongsTo(UserAuth::class, 'user_id');
    }
    
    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class);
    }
} 