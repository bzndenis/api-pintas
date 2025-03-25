<?php
namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class User extends Model implements AuthenticatableContract, AuthorizableContract
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
        return $this->hasOne(Guru::class);
    }
    
    public function activities()
    {
        return $this->hasMany(UserActivity::class);
    }
    
    public function sessions()
    {
        return $this->hasMany(UserSession::class);
    }

    public function createdAbsensi()
    {
        return $this->hasMany(AbsensiSiswa::class, 'created_by');
    }

    public function createdNilai()
    {
        return $this->hasMany(NilaiSiswa::class, 'created_by');
    }

    public function createdPertemuan()
    {
        return $this->hasMany(PertemuanBulanan::class, 'created_by');
    }
}