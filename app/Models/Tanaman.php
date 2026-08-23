<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Tanaman extends Model
{
    protected $table = 'tanaman';
    protected $fillable = ['id_owners', 'meja_id', 'nama_tanaman', 'catatan', 'siklus_selesai_at'];

    protected $casts = [
        'siklus_selesai_at' => 'datetime',
    ];

    public function owner()
    {
        return $this->belongsTo(Owner::class, 'id_owners');
    }

    public function meja()
    {
        return $this->belongsTo(Meja::class);
    }

    public function tahapans()
    {
        return $this->hasMany(Tahapan::class);
    }

    public function jadwals()
    {
        return $this->hasMany(Jadwal::class);
    }

    public function panens()
    {
        return $this->hasMany(Panen::class);
    }

    public function tahapanAktif()
    {
        return $this->tahapans->firstWhere('status', 'berjalan');
    }

    public function getStatusAttribute(): string
    {
        if ($this->siklus_selesai_at) {
            return 'Selesai (Dipanen)';
        }

        $latest = $this->tahapans->sortByDesc('id')->first();

        if (!$latest) {
            return 'Baru';
        }

        if ($latest->status === 'berjalan') {
            return 'Sedang '.ucfirst($latest->jenis);
        }

        if ($latest->jenis === 'panen') {
            return 'Siap Tutup Siklus';
        }

        if ($latest->jenis === 'pendewasaan') {
            return 'Siap Panen';
        }

        return 'Menunggu Tahap Selanjutnya';
    }

    public function getProgressAttribute(): string
    {
        $selesai = $this->tahapans->where('status', 'selesai')->count();
        return $selesai > 0 ? "{$selesai} tahap selesai" : 'Belum mulai';
    }

    /**
     * Total panen per tanaman lewat sub-query agregat. Tanpa ini, daftar tanaman di
     * halaman Panen memicu satu query lazy-load relasi panens untuk SETIAP baris yang
     * ditampilkan (N+1) hanya untuk menjumlahkan beratnya.
     */
    public function scopeDenganRekapPanen(Builder $query): Builder
    {
        return $query
            ->select('tanaman.*')
            ->addSelect([
                'total_berat_panen' => Panen::subRekap('tanaman_id', 'tanaman.id', 'berat'),
                'total_pendapatan_panen' => Panen::subRekap('tanaman_id', 'tanaman.id', 'harga'),
            ]);
    }

    public function getTotalBeratPanenAttribute($value): float
    {
        if ($value !== null) {
            return (float) $value;
        }

        return (float) $this->panens->sum(fn ($p) => (float) $p->berat_kg);
    }

    public function getTotalPendapatanPanenAttribute($value): float
    {
        if ($value !== null) {
            return (float) $value;
        }

        return (float) $this->panens
            ->filter(fn ($p) => $p->harga_per_kg !== null)
            ->sum(fn ($p) => $p->total_harga);
    }
}
