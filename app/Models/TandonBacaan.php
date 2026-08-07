<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

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

        // Grafik riwayat di MonitorTandon di-cache (query-nya berat, baris ini cuma
        // masuk ~tiap 5 menit per tandon lewat throttle di SimulasikanTandon) - begitu
        // ada baris baru masuk, cache grafik punya tandon ini WAJIB dianggap basi
        // supaya titik data terbaru langsung kelihatan, bukan nunggu TTL habis.
        static::created(function (self $bacaan) {
            $idOwners = $bacaan->tandon?->kebun?->id_owners;
            if ($idOwners) {
                Cache::tags(["owner{$idOwners}:tandon_bacaan:{$bacaan->id_tandon}"])->flush();
            }
        });
    }
}
