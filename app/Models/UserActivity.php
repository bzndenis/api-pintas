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
    
    public static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->id = Uuid::uuid4()->toString();
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