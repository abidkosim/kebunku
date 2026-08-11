<?php

namespace App\Models;

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

    public function getTotalHutangAttribute(): float
    {
        return $this->panens
            ->filter(fn ($p) => $p->harga_per_kg !== null)
            ->sum(fn ($p) => max(0, $p->total_harga - $p->jumlah_dibayar));
    }

    public function getTotalKgAttribute(): float
    {
        return (float) $this->panens->sum(fn ($p) => (float) $p->berat_kg);
    }

    public function getTotalTransaksiAttribute(): float
    {
        return (float) $this->panens
            ->filter(fn ($p) => $p->harga_per_kg !== null)
            ->sum(fn ($p) => $p->total_harga);
    }

    public function getTotalDibayarAttribute(): float
    {
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
