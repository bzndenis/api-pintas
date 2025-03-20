<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class User extends Model
{
    use SoftDeletes;
    
    protected $table = 'users';
    
    protected $fillable = [
        'id',
        'email',
        'password', 
        'role',
        'sekolah_id',
        'nama_lengkap',
        'no_telepon',
        'alamat_sekolah',
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
}