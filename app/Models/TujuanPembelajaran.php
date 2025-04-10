<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class TujuanPembelajaran extends Model
{
    use SoftDeletes;
    
    protected $table = 'tujuan_pembelajaran';
    
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'id',
        'kode_tp',
        'nama',
        'deskripsi',
        'bobot',
        'cp_id',
        'sekolah_id',
    ];
    
    protected $casts = [
        'bobot' => 'decimal:2',
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

            // Generate kode TP otomatis jika tidak diisi
            if (empty($model->kode_tp)) {
                $lastTP = self::where('cp_id', $model->cp_id)
                    ->where('sekolah_id', $model->sekolah_id)
                    ->orderBy('created_at', 'desc')
                    ->first();

                $counter = 1;
                if ($lastTP && preg_match('/(\d+)$/', $lastTP->kode_tp, $matches)) {
                    $counter = intval($matches[1]) + 1;
                }

                $cp = CapaianPembelajaran::find($model->cp_id);
                $model->kode_tp = 'TP.' . ($cp ? $cp->kode_cp : '') . '.' . str_pad($counter, 2, '0', STR_PAD_LEFT);
            }

            // Generate nama TP otomatis hanya jika nama tidak diisi
            if (empty($model->nama)) {
                $cp = CapaianPembelajaran::find($model->cp_id);
                $cpNama = $cp ? $cp->nama : 'CP';
                $model->nama = "TP " . $model->kode_tp . " - " . $cpNama;
            }
        });
    }
    
    public function capaianPembelajaran()
    {
        return $this->belongsTo(CapaianPembelajaran::class, 'cp_id');
    }
    
    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class);
    }
    
    public function nilaiSiswa()
    {
        return $this->hasMany(NilaiSiswa::class, 'tp_id');
    }
} 