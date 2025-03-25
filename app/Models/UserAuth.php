<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Laravel\Lumen\Auth\Authorizable;
use Ramsey\Uuid\Uuid;

class UserAuth extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, SoftDeletes;

    protected $table = 'users';

    protected $fillable = [
        'id',
        'username',
        'password', 
        'fullname',
        'email',
        'role',
        'sekolah_id',
        'last_login',
        'is_active',
        'remember_token'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'last_login' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
    protected $dates = [
        'last_login',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    
    public $incrementing = false;
    protected $keyType = 'string';
    
    public static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->id = Uuid::uuid4()->toString();
            if (!isset($model->is_active)) {
                $model->is_active = true;
            }
        });
    }
    
    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class);
    }
    
    public function guru()
    {
        return $this->hasOne(Guru::class, 'user_id', 'id');
    }
}
