<?php

namespace App\Models;

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

    public function getTotalBeratPanenAttribute(): float
    {
        return (float) $this->panens->sum(fn ($p) => (float) $p->berat_kg);
    }

    public function getTotalPendapatanPanenAttribute(): float
    {
        return (float) $this->panens
            ->filter(fn ($p) => $p->harga_per_kg !== null)
            ->sum(fn ($p) => $p->total_harga);
    }
}
