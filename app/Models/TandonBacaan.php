<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TandonBacaan extends Model
{
    public $timestamps = false;

    protected $fillable = ['id_tandon', 'ppm', 'ph', 'suhu', 'created_at'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function tandon()
    {
        return $this->belongsTo(Tandon::class, 'id_tandon');
    }

    /**
     * created_at WAJIB diisi eksplisit dari now() PHP (bukan default DB-level
     * "useCurrent()" di migration) - MySQL server di mesin ini ternyata pakai
     * jam sistem lokal (UTC+7), sedangkan APP_TIMEZONE Laravel UTC. Kalau
     * dibiarkan pakai default DB, timestamp-nya melenceng 7 jam dari now()
     * versi Laravel, yang bikin throttle 5-menit di SimulasikanTandon rusak
     * (baris pertama langsung dianggap "dari masa depan", jadi tidak akan
     * pernah dianggap kedaluwarsa) dan jam di grafik jadi salah baca.
     */
    protected static function booted()
    {
        static::creating(function (self $bacaan) {
            $bacaan->created_at ??= now();
        });
    }
}
