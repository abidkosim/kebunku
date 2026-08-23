<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Panen extends Model
{
    protected $fillable = [
        'tanaman_id', 'pembeli_id', 'pemanen', 'tanggal',
        'berat_kg', 'harga_per_kg', 'jumlah_dibayar', 'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'berat_kg' => 'decimal:2',
        'harga_per_kg' => 'decimal:2',
        'jumlah_dibayar' => 'decimal:2',
    ];

    /**
     * Ekspresi SQL untuk total_harga satu baris panen. Sengaja dijadikan satu konstanta
     * supaya rumusnya tidak pernah berbeda antara perhitungan PHP (accessor di bawah)
     * dan perhitungan SQL (rekap()) - keduanya harus selalu menghasilkan angka sama.
     */
    private const SQL_TOTAL_HARGA = 'ROUND(berat_kg * harga_per_kg, 2)';

    /**
     * max(0, total_harga - jumlah_dibayar). Ditulis pakai CASE WHEN, bukan GREATEST(),
     * karena GREATEST hanya ada di MySQL - CASE WHEN jalan di semua driver termasuk
     * SQLite yang dipakai test suite.
     */
    private const SQL_SISA_HUTANG = 'CASE WHEN '.self::SQL_TOTAL_HARGA.' - jumlah_dibayar > 0'
        .' THEN '.self::SQL_TOTAL_HARGA.' - jumlah_dibayar ELSE 0 END';

    public function tanaman()
    {
        return $this->belongsTo(Tanaman::class);
    }

    public function pembeli()
    {
        return $this->belongsTo(Pembeli::class);
    }

    public function scopeMilikOwner(Builder $query, int $ownerId): Builder
    {
        return $query->whereHas('tanaman', fn ($q) => $q->where('id_owners', $ownerId));
    }

    /**
     * Rekap uang & berat DIHITUNG DI DATABASE, bukan dengan ->get() lalu sum() di PHP.
     *
     * Versi lama memuat SELURUH baris panen milik owner ke memori hanya untuk
     * menjumlahkannya - biaya RAM & transfer datanya tumbuh terus seiring jumlah
     * transaksi, padahal yang dipakai cuma beberapa angka total. Di sini MySQL yang
     * menjumlahkan, PHP cuma menerima satu baris hasil.
     *
     * Baris dengan harga_per_kg NULL (panen yang harganya belum ditentukan) dihitung
     * nol untuk semua kolom rupiah - sama persis dengan filter(harga_per_kg !== null)
     * pada versi PHP-nya.
     */
    public static function rekap(Builder $query): object
    {
        $totalHarga = self::SQL_TOTAL_HARGA;
        $sisaHutang = self::SQL_SISA_HUTANG;

        $baris = $query->toBase()->selectRaw("
            COALESCE(SUM(berat_kg), 0) as total_berat,
            COALESCE(SUM(CASE WHEN harga_per_kg IS NULL THEN 0 ELSE {$totalHarga} END), 0) as total_harga,
            COALESCE(SUM(CASE WHEN harga_per_kg IS NULL THEN 0 ELSE jumlah_dibayar END), 0) as total_dibayar,
            COALESCE(SUM(CASE WHEN harga_per_kg IS NULL THEN 0 ELSE ({$sisaHutang}) END), 0) as total_sisa_hutang,
            COALESCE(SUM(CASE WHEN harga_per_kg IS NULL THEN 1 ELSE 0 END), 0) as jumlah_menunggu_harga,
            COUNT(*) as jumlah_transaksi
        ")->first();

        return (object) [
            'total_berat' => (float) ($baris->total_berat ?? 0),
            'total_harga' => (float) ($baris->total_harga ?? 0),
            'total_dibayar' => (float) ($baris->total_dibayar ?? 0),
            'total_sisa_hutang' => (float) ($baris->total_sisa_hutang ?? 0),
            'jumlah_menunggu_harga' => (int) ($baris->jumlah_menunggu_harga ?? 0),
            'jumlah_transaksi' => (int) ($baris->jumlah_transaksi ?? 0),
        ];
    }

    /**
     * Sub-query agregat per pembeli/tanaman, dipakai lewat addSelect() supaya daftar
     * bisa menampilkan total tanpa meng-eager-load seluruh baris panen tiap barisnya.
     */
    public static function subRekap(string $kolomRelasi, string $kolomInduk, string $jenis): Builder
    {
        $totalHarga = self::SQL_TOTAL_HARGA;
        $sisaHutang = self::SQL_SISA_HUTANG;

        $ekspresi = match ($jenis) {
            'berat' => 'COALESCE(SUM(berat_kg), 0)',
            'harga' => "COALESCE(SUM(CASE WHEN harga_per_kg IS NULL THEN 0 ELSE {$totalHarga} END), 0)",
            'dibayar' => "COALESCE(SUM(CASE WHEN harga_per_kg IS NULL THEN 0 ELSE jumlah_dibayar END), 0)",
            'hutang' => "COALESCE(SUM(CASE WHEN harga_per_kg IS NULL THEN 0 ELSE ({$sisaHutang}) END), 0)",
        };

        return static::selectRaw($ekspresi)->whereColumn($kolomRelasi, $kolomInduk);
    }

    public function getTotalHargaAttribute(): ?float
    {
        if ($this->harga_per_kg === null) {
            return null;
        }
        return round((float) $this->berat_kg * (float) $this->harga_per_kg, 2);
    }

    public function getSisaHutangAttribute(): ?float
    {
        if ($this->total_harga === null) {
            return null;
        }
        return max(0, round($this->total_harga - (float) $this->jumlah_dibayar, 2));
    }

    public function getStatusPembayaranAttribute(): string
    {
        if ($this->harga_per_kg === null) {
            return 'menunggu_harga';
        }
        if ((float) $this->jumlah_dibayar >= $this->total_harga) {
            return 'lunas';
        }
        if ((float) $this->jumlah_dibayar > 0) {
            return 'sebagian';
        }
        return 'hutang';
    }
}
