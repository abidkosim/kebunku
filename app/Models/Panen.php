<?php

namespace App\Models;

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

    public function tanaman()
    {
        return $this->belongsTo(Tanaman::class);
    }

    public function pembeli()
    {
        return $this->belongsTo(Pembeli::class);
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
