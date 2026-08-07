<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tahapan extends Model
{
    protected $fillable = [
        'tanaman_id', 'jenis', 'jumlah_awal', 'durasi_rencana',
        'tanggal_mulai', 'tanggal_selesai_rencana', 'tanggal_selesai_aktual',
        'jumlah_lolos', 'status', 'catatan', 'notif_h2_shown_at',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai_rencana' => 'date',
        'tanggal_selesai_aktual' => 'date',
        'notif_h2_shown_at' => 'date',
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

    /**
     * Sisa hari menuju tanggal_selesai_rencana. Positif = masih ada waktu, 0 = hari ini
     * tenggatnya, negatif = sudah lewat. Null kalau tahap ini tidak punya rencana durasi
     * (jenis 'panen') atau sudah selesai - progress/tenggat cuma relevan buat tahap aktif.
     *
     * Pakai selisih timestamp manual (bukan Carbon::diffInDays) supaya arah tanda (+/-)
     * tidak tergantung versi Carbon yang dipakai - konvensi tanda diffInDays berubah-ubah
     * antar versi major, jadi lebih aman dihitung sendiri di sini.
     */
    public function getSisaHariAttribute(): ?int
    {
        if ($this->status !== 'berjalan' || !$this->tanggal_selesai_rencana) {
            return null;
        }

        $hariIni = now()->startOfDay()->timestamp;
        $target = $this->tanggal_selesai_rencana->copy()->startOfDay()->timestamp;

        return (int) round(($target - $hariIni) / 86400);
    }

    /**
     * Persentase waktu yang sudah terlewat dari durasi_rencana (0-100). Dikunci maksimal
     * 100 walau sudah lewat tenggat - kondisi "lewat tenggat" ditandai lewat sisa_hari
     * negatif, bukan progress di atas 100%, supaya bar-nya tidak "meluber" secara visual.
     */
    public function getProgressPersenAttribute(): ?int
    {
        if ($this->status !== 'berjalan' || !$this->durasi_rencana || !$this->tanggal_mulai) {
            return null;
        }

        $mulai = $this->tanggal_mulai->copy()->startOfDay()->timestamp;
        $hariIni = now()->startOfDay()->timestamp;
        $terlewat = max(0, (int) round(($hariIni - $mulai) / 86400));

        return (int) min(100, round($terlewat / $this->durasi_rencana * 100));
    }

    /**
     * True kalau tahap ini H-2 (sisa <=2 hari) atau sudah lewat tenggat - dipakai buat
     * warna progress bar (merah) dan buat nentuin apakah tahap ini masuk daftar modal
     * peringatan.
     */
    public function getHampirHabisAttribute(): bool
    {
        return $this->sisa_hari !== null && $this->sisa_hari <= 2;
    }
}
