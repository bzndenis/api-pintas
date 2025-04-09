<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class CapaianPembelajaran extends Model
{
    use SoftDeletes;
    
    protected $table = 'capaian_pembelajaran';
    
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'id',
        'kode_cp',
        'deskripsi',
        'mapel_id',
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
            
            // Generate kode CP otomatis jika tidak diisi
            if (empty($model->kode_cp)) {
                $lastCP = self::where('mapel_id', $model->mapel_id)
                    ->where('sekolah_id', $model->sekolah_id)
                    ->orderBy('created_at', 'desc')
                    ->first();

                $counter = 1;
                if ($lastCP && preg_match('/(\d+)$/', $lastCP->kode_cp, $matches)) {
                    $counter = intval($matches[1]) + 1;
                }

                $mataPelajaran = MataPelajaran::find($model->mapel_id);
                $kodeMapel = $mataPelajaran ? $mataPelajaran->kode : 'MP';
                $model->kode_cp = 'CP.' . $kodeMapel . '.' . str_pad($counter, 2, '0', STR_PAD_LEFT);
            }
        });
    }
    
    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class, 'mapel_id');
    }
    
    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class);
    }
    
    public function tujuanPembelajaran()
    {
        return $this->hasMany(TujuanPembelajaran::class, 'cp_id');
    }
} 