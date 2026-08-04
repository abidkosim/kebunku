<?php

namespace App\Jobs;

use App\Models\ActivityLog;
use App\Models\Tandon;
use App\Services\TandonIngestService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Siklus auto-dosing yang realistis (mode simulasi): pompa "menyala" durasi_dosing_detik,
 * tunggu jeda_cek_detik biar larutan campur, BARU efek dosis diterapkan & dicek ulang -
 * kalau masih di luar toleransi, diulang (sampai maks_percobaan_dosing lalu berhenti minta
 * cek manual, supaya sensor yang stuck/salah kalibrasi tidak bikin dosing tanpa henti).
 *
 * Dipisah dari SimulasikanTandon (yang urus drift alami tiap 8 detik) supaya timing dosing
 * bisa diatur independen & tidak numpang di request web - murni queue job berantai.
 */
class JalankanAutoDosing implements ShouldQueue
{
    use Queueable;

    private const TOLERANSI_PPM = 30;
    private const TOLERANSI_PH = 0.2;

    public function __construct(
        public int $tandonId,
        public string $jenis, // nutrisi | ph_up | ph_down
        public string $tahap = 'mulai', // mulai | cek
        public int $percobaanKe = 1,
    ) {
    }

    public function handle(TandonIngestService $ingest): void
    {
        $tandon = Tandon::with('kebun')->find($this->tandonId);

        if (!$tandon || !$tandon->kebun || $tandon->status_simulasi !== 'aktif' || $tandon->sumber_data !== 'simulasi') {
            return;
        }

        if ($this->tahap === 'mulai') {
            $this->mulaiDosing($tandon);
            return;
        }

        $this->cekHasilDosing($tandon, $ingest);
    }

    private function mulaiDosing(Tandon $tandon): void
    {
        // dispatch tahap berikutnya dulu SEBELUM operasi lain (konsisten pola self-redispatch
        // project ini) - supaya siklus tidak pernah mati permanen gara-gara error di tengah.
        self::dispatch($tandon->id, $this->jenis, tahap: 'cek', percobaanKe: $this->percobaanKe)
            ->delay(now()->addSeconds($tandon->durasi_dosing_detik + $tandon->jeda_cek_detik));

        $tandon->update([
            'status_pompa' => $this->jenis,
            'percobaan_dosing_saat_ini' => $this->percobaanKe,
        ]);

        $label = $this->labelJenis();
        $this->catat($tandon, "Mulai auto-dosing {$label} ke tandon '{$tandon->nama}' (pompa menyala {$tandon->durasi_dosing_detik} detik, percobaan ke-{$this->percobaanKe})");
    }

    private function cekHasilDosing(Tandon $tandon, TandonIngestService $ingest): void
    {
        $ppm = (float) $tandon->ppm_terkini;
        $ph = (float) $tandon->ph_terkini;

        $ppm = match ($this->jenis) {
            'nutrisi' => min(2000, $ppm + random_int(20, 45)),
            default => $ppm,
        };

        $ph = match ($this->jenis) {
            'ph_up' => min(14.0, $ph + round(random_int(10, 25) / 100, 1)),
            'ph_down' => max(0.0, $ph - round(random_int(10, 25) / 100, 1)),
            default => $ph,
        };

        $label = $this->labelJenis();
        $masihKurang = ($this->jenis === 'nutrisi' && $ppm < $tandon->target_ppm - self::TOLERANSI_PPM)
            || ($this->jenis === 'ph_up' && $ph < $tandon->target_ph - self::TOLERANSI_PH)
            || ($this->jenis === 'ph_down' && $ph > $tandon->target_ph + self::TOLERANSI_PH);

        if (!$masihKurang) {
            $ingest->simpanBacaan($tandon, $ppm, $ph, (float) $tandon->suhu_terkini, sumber: 'simulasi', statusPompa: null);
            $tandon->update(['percobaan_dosing_saat_ini' => 0]);
            $this->catat($tandon, "Auto-dosing {$label} tandon '{$tandon->nama}' berhasil - target tercapai");
            return;
        }

        if ($this->percobaanKe >= $tandon->maks_percobaan_dosing) {
            $ingest->simpanBacaan($tandon, $ppm, $ph, (float) $tandon->suhu_terkini, sumber: 'simulasi', statusPompa: null);
            $tandon->update(['percobaan_dosing_saat_ini' => 0]);
            $this->catat($tandon, "PERHATIAN: Auto-dosing {$label} tandon '{$tandon->nama}' GAGAL capai target setelah {$this->percobaanKe} percobaan - perlu dicek manual");
            return;
        }

        $ingest->simpanBacaan($tandon, $ppm, $ph, (float) $tandon->suhu_terkini, sumber: 'simulasi', statusPompa: $this->jenis);
        $this->catat($tandon, "Auto-dosing {$label} tandon '{$tandon->nama}' masih kurang, diulang (percobaan ke-".($this->percobaanKe + 1).")");

        self::dispatch($tandon->id, $this->jenis, tahap: 'mulai', percobaanKe: $this->percobaanKe + 1);
    }

    private function labelJenis(): string
    {
        return match ($this->jenis) {
            'nutrisi' => 'nutrisi',
            'ph_up' => 'pH Up',
            'ph_down' => 'pH Down',
            default => $this->jenis,
        };
    }

    private function catat(Tandon $tandon, string $keterangan): void
    {
        ActivityLog::catat('sistem', 0, 'Sistem Auto-Dosing', 'auto', 'Monitor Tandon', $keterangan, $tandon->kebun->id_owners);
    }
}
