<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tahapan extends Model
{
    protected $fillable = [
        'tanaman_id', 'jenis', 'jumlah_awal', 'durasi_rencana',
        'tanggal_mulai', 'tanggal_selesai_rencana', 'tanggal_selesai_aktual',
        'jumlah_lolos', 'status', 'catatan',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai_rencana' => 'date',
        'tanggal_selesai_aktual' => 'date',
    ];

    public function tanaman()
    {
        return $this->belongsTo(Tanaman::class);
    }

    public function getJumlahMatiAttribute(): ?int
    {
        if ($this->jumlah_lolos === null) {
            return null;
        }
        return $this->jumlah_awal - $this->jumlah_lolos;
    }

    public function getLengkapAttribute(): ?bool
    {
        if ($this->jumlah_lolos === null) {
            return null;
        }
        return $this->jumlah_lolos === $this->jumlah_awal;
    }
}
