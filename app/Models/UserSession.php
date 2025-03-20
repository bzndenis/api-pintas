<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class UserSession extends Model
{
    protected $table = 'user_sessions';
    
    protected $fillable = [
        'id',
        'user_id',
        'login_time',
        'last_activity',
        'duration',
        'status',
        'ip_address',
        'user_agent',
        'sekolah_id',
    ];
    
    protected $casts = [
        'duration' => 'integer',
        'login_time' => 'datetime',
        'last_activity' => 'datetime',
        'status' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    protected $dates = [
        'login_time',
        'last_activity',
        'created_at',
        'updated_at',
    ];
    
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
        return $this->belongsTo(User::class);
    }
    
    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class);
    }
} 