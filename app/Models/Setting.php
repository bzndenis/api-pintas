<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Setting extends Model
{
    protected $table = 'settings';
    
    protected $fillable = [
        'id',
        'key',
        'value',
        'group',
        'sekolah_id',
    ];
    
    protected $casts = [
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
    
    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class);
    }
    
    // Metode statis untuk mendapatkan nilai pengaturan berdasarkan kunci
    public static function getValue($key, $sekolahId, $default = null)
    {
        $setting = self::where('key', $key)
                        ->where('sekolah_id', $sekolahId)
                        ->first();
        
        return $setting ? $setting->value : $default;
    }
    
    // Metode statis untuk menyimpan atau memperbarui nilai pengaturan
    public static function setValue($key, $value, $sekolahId, $group = null)
    {
        $setting = self::where('key', $key)
                        ->where('sekolah_id', $sekolahId)
                        ->first();
        
        if ($setting) {
            $setting->update([
                'value' => $value,
                'group' => $group ?? $setting->group
            ]);
        } else {
            self::create([
                'key' => $key,
                'value' => $value,
                'group' => $group,
                'sekolah_id' => $sekolahId
            ]);
        }
    }
} 