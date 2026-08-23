<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Pembeli extends Model
{
    protected $table = 'pembeli';
    protected $fillable = ['id_owners', 'nama', 'kontak'];

    public function owner()
    {
        return $this->belongsTo(Owner::class, 'id_owners');
    }

    public function panens()
    {
        return $this->hasMany(Panen::class);
    }

    /**
     * Daftar pembeli lengkap dengan total kg / transaksi / dibayar / hutang, dihitung
     * lewat sub-query agregat di SQL. Sebelumnya daftar ini pakai with('panens'), yang
     * berarti SELURUH baris panen milik SETIAP pembeli ikut ditarik ke memori hanya
     * untuk dijumlahkan - berat di RAM dan makin lambat tiap ada transaksi baru.
     */
    public function scopeDenganRekap(Builder $query): Builder
    {
        return $query
            ->select('pembeli.*')
            ->withCount('panens')
            ->addSelect([
                'total_kg' => Panen::subRekap('pembeli_id', 'pembeli.id', 'berat'),
                'total_transaksi' => Panen::subRekap('pembeli_id', 'pembeli.id', 'harga'),
                'total_dibayar' => Panen::subRekap('pembeli_id', 'pembeli.id', 'dibayar'),
                'total_hutang' => Panen::subRekap('pembeli_id', 'pembeli.id', 'hutang'),
            ]);
    }

    /**
     * Keempat accessor di bawah memakai nilai hasil agregat SQL kalau query-nya lewat
     * scopeDenganRekap() (nilainya sudah ada di $value), dan baru jatuh ke perhitungan
     * PHP atas relasi panens kalau tidak - supaya halaman detail satu pembeli, yang
     * memang butuh baris panennya, tetap bekerja seperti semula.
     */
    public function getTotalHutangAttribute($value): float
    {
        if ($value !== null) {
            return (float) $value;
        }

        return (float) $this->panens
            ->filter(fn ($p) => $p->harga_per_kg !== null)
            ->sum(fn ($p) => max(0, $p->total_harga - (float) $p->jumlah_dibayar));
    }

    public function getTotalKgAttribute($value): float
    {
        if ($value !== null) {
            return (float) $value;
        }

        return (float) $this->panens->sum(fn ($p) => (float) $p->berat_kg);
    }

    public function getTotalTransaksiAttribute($value): float
    {
        if ($value !== null) {
            return (float) $value;
        }

        return (float) $this->panens
            ->filter(fn ($p) => $p->harga_per_kg !== null)
            ->sum(fn ($p) => $p->total_harga);
    }

    public function getTotalDibayarAttribute($value): float
    {
        if ($value !== null) {
            return (float) $value;
        }

        return (float) $this->panens
            ->filter(fn ($p) => $p->harga_per_kg !== null)
            ->sum(fn ($p) => (float) $p->jumlah_dibayar);
    }

    public function getStatusHutangAttribute(): string
    {
        if ($this->total_transaksi <= 0) {
            return 'menunggu_harga';
        }
        if ($this->total_hutang <= 0) {
            return 'lunas';
        }
        if ($this->total_dibayar > 0) {
            return 'sebagian';
        }
        return 'hutang';
    }
}
