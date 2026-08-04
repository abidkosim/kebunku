<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tandon extends Model
{
    protected $fillable = [
        'id_kebun',
        'nama',
        'target_ppm',
        'target_ph',
        'ppm_terkini',
        'ph_terkini',
        'suhu_terkini',
        'status_simulasi',
        'status_pompa',
        'terakhir_baca_at',
        'sumber_data',
        'device_token',
        'durasi_dosing_detik',
        'jeda_cek_detik',
        'maks_percobaan_dosing',
        'percobaan_dosing_saat_ini',
    ];

    protected $casts = [
        'terakhir_baca_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function (self $tandon) {
            $tandon->device_token ??= Str::random(40);
        });
    }

    public function kebun()
    {
        return $this->belongsTo(Kebun::class, 'id_kebun');
    }

    public function bacaans()
    {
        return $this->hasMany(TandonBacaan::class, 'id_tandon');
    }
}
