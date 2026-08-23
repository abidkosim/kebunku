<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kebun extends Model
{
    protected $table = 'kebun';
    protected $fillable = ['id_owners', 'nama_kebun', 'lat', 'lng'];

    protected $casts = [
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
    ];

    /** Radius maksimum (meter) supaya sebuah kunjungan Absensi dianggap "di kebun ini". */
    public const RADIUS_ABSENSI_METER = 20;

    public function owner()
    {
        return $this->belongsTo(Owner::class, 'id_owners');
    }

    public function meja()
    {
        return $this->hasMany(Meja::class);
    }

    public function tandons()
    {
        return $this->hasMany(Tandon::class, 'id_kebun');
    }

    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'id_kebun');
    }

    public function getPunyaKoordinatAttribute(): bool
    {
        return $this->lat !== null && $this->lng !== null;
    }

    public function getKoordinatUrlAttribute(): ?string
    {
        if (!$this->punya_koordinat) {
            return null;
        }

        return "https://www.google.com/maps?q={$this->lat},{$this->lng}";
    }

    /**
     * Kebun milik owner ini yang PALING DEKAT dengan satu titik GPS, di antara kebun
     * yang sudah punya koordinat (kebun tanpa koordinat dilewati - tidak bisa dihitung
     * jaraknya). Dihitung di PHP (bukan SQL) karena jumlah kebun per owner kecil -
     * loop biasa jauh lebih sederhana daripada Haversine dalam raw SQL untuk data
     * sekecil ini, tanpa kehilangan apa-apa dari sisi performa.
     *
     * Return null kalau owner belum punya SATU PUN kebun dengan koordinat - pemanggil
     * (KelolaAbsensi) memakai ini sebagai sinyal "belum bisa absen sama sekali".
     */
    public static function terdekatDenganKoordinat(int $ownerId, float $lat, float $lng): ?object
    {
        $daftar = static::where('id_owners', $ownerId)
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->get();

        if ($daftar->isEmpty()) {
            return null;
        }

        return $daftar
            ->map(fn (self $kebun) => (object) [
                'kebun' => $kebun,
                'jarak_meter' => self::jarakMeter($lat, $lng, (float) $kebun->lat, (float) $kebun->lng),
            ])
            ->sortBy('jarak_meter')
            ->first();
    }

    /**
     * Jarak antara dua titik GPS dalam meter (formula Haversine, radius bumi rata-rata
     * 6.371.000m). Dipakai server-side sebagai sumber kebenaran untuk validasi radius -
     * versi JS di kelola-absensi.blade.php cuma untuk feedback instan di layar, TIDAK
     * pernah dipercaya sebagai validasi final.
     */
    public static function jarakMeter(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $radiusBumi = 6371000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $radiusBumi * $c;
    }
}
