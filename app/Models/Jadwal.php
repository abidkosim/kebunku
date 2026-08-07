<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    // Tabel jadwals sekarang khusus aktivitas semprot (berulang) per tanaman.
    protected $fillable = ['tanaman_id', 'tanggal_rencana', 'tanggal_selesai', 'status', 'catatan', 'notif_h2_shown_at'];

    protected $casts = [
        'tanggal_rencana' => 'date',
        'tanggal_selesai' => 'date',
        'notif_h2_shown_at' => 'date',
    ];

    public function tanaman()
    {
        return $this->belongsTo(Tanaman::class);
    }

    /**
     * Sisa hari menuju tanggal_rencana. Sama seperti Tahapan::sisa_hari - dihitung manual
     * dari timestamp supaya tidak tergantung konvensi tanda Carbon::diffInDays.
     */
    public function getSisaHariAttribute(): ?int
    {
        if ($this->status === 'selesai' || !$this->tanggal_rencana) {
            return null;
        }

        $hariIni = now()->startOfDay()->timestamp;
        $target = $this->tanggal_rencana->copy()->startOfDay()->timestamp;

        return (int) round(($target - $hariIni) / 86400);
    }

    /**
     * Progress menuju tanggal_rencana, dihitung dari saat jadwal ini DIBUAT (created_at)
     * sampai tanggal_rencana - beda dengan Tahapan yang punya tanggal_mulai eksplisit,
     * jadwal semprot cuma punya satu tanggal target, jadi created_at dipakai sebagai
     * titik awal "hitung mundur"-nya.
     */
    public function getProgressPersenAttribute(): ?int
    {
        if ($this->status === 'selesai' || !$this->tanggal_rencana) {
            return null;
        }

        $mulai = $this->created_at->copy()->startOfDay()->timestamp;
        $target = $this->tanggal_rencana->copy()->startOfDay()->timestamp;
        $hariIni = now()->startOfDay()->timestamp;

        $totalHari = max(1, (int) round(($target - $mulai) / 86400));
        $terlewat = max(0, (int) round(($hariIni - $mulai) / 86400));

        return (int) min(100, round($terlewat / $totalHari * 100));
    }

    public function getHampirHabisAttribute(): bool
    {
        return $this->sisa_hari !== null && $this->sisa_hari <= 2;
    }
}
