<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class PertemuanBulanan extends Model
{
    protected $table = 'pertemuan_bulanan';
    
    protected $fillable = [
        'id',
        'kelas_id',
        'bulan',
        'tahun',
        'total_pertemuan',
        'created_by',
        'sekolah_id',
    ];
    
    protected $casts = [
        'bulan' => 'integer',
        'tahun' => 'integer',
        'total_pertemuan' => 'integer',
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
    
    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }
    
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class);
    }
    
    public function absensiSiswa()
    {
        return $this->hasMany(AbsensiSiswa::class, 'pertemuan_id');
    }
    
    // Accessor untuk mendapatkan nama bulan
    public function getNamaBulanAttribute()
    {
        $bulanIndonesia = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];
        
        return $bulanIndonesia[$this->bulan] ?? '';
    }
} 