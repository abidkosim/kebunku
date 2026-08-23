<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Log kunjungan ke kebun (bukan absen jam-kerja). Setiap baris = satu kunjungan:
 * foto + lokasi GPS + jam, direkam otomatis lewat App\Livewire\Owner\KelolaAbsensi
 * saat itu juga. Tidak ada method update/delete yang dipakai di aplikasi ini -
 * catatannya sengaja permanen sebagai dokumentasi.
 */
class Absensi extends Model
{
    protected $table = 'absensi';

    protected $fillable = [
        'id_owners', 'id_kebun', 'actor_type', 'actor_id', 'actor_nama',
        'foto', 'lokasi_lat', 'lokasi_lng', 'kegiatan',
    ];

    protected $casts = [
        'lokasi_lat' => 'decimal:7',
        'lokasi_lng' => 'decimal:7',
    ];

    public function owner()
    {
        return $this->belongsTo(Owner::class, 'id_owners');
    }

    public function kebun()
    {
        return $this->belongsTo(Kebun::class, 'id_kebun');
    }

    public function getFotoUrlAttribute(): string
    {
        return asset('storage/'.$this->foto);
    }

    public function getLokasiMapsUrlAttribute(): string
    {
        return "https://www.google.com/maps?q={$this->lokasi_lat},{$this->lokasi_lng}";
    }
}
