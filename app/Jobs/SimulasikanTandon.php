<?php

namespace App\Jobs;

use App\Events\TandonUpdated;
use App\Models\ActivityLog;
use App\Models\Tandon;
use App\Models\TandonBacaan;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Simulasi pembacaan sensor TDS/pH/suhu untuk 1 tandon (hardware IoT belum ada,
 * jadi datanya di-generate di sini) + logika auto-dosing. Job ini men-dispatch
 * dirinya sendiri lagi dengan delay di akhir handle() - selama "php artisan
 * queue:work/listen" jalan, rantai ini terus hidup dan datanya berasa realtime
 * tanpa perlu proses scheduler terpisah. Rantai berhenti sendiri begitu
 * status_simulasi tandon diubah jadi 'berhenti' atau tandonnya dihapus.
 */
class SimulasikanTandon implements ShouldQueue
{
    use Queueable;

    private const JEDA_DETIK = 8;
    private const TOLERANSI_PPM = 30;
    private const TOLERANSI_PH = 0.2;
    private const JEDA_CATAT_RIWAYAT_MENIT = 5;
    private const SIMPAN_RIWAYAT_HARI = 7;

    public function __construct(public int $tandonId)
    {
    }

    public function handle(): void
    {
        $tandon = Tandon::with('kebun')->find($this->tandonId);

        if (!$tandon || !$tandon->kebun || $tandon->status_simulasi !== 'aktif') {
            return;
        }

        $ppm = (int) ($tandon->ppm_terkini ?? max(0, $tandon->target_ppm - 150));
        $ph = (float) ($tandon->ph_terkini ?? max(0, $tandon->target_ph - 0.5));
        $suhu = (float) ($tandon->suhu_terkini ?? 27.0);

        // drift alami: ppm perlahan turun (diserap tanaman/menguap), ph & suhu goyang kecil acak
        $ppm += random_int(-9, 4);
        $ph += random_int(-8, 8) / 100;
        $suhu += random_int(-5, 5) / 10;

        $ppm = max(0, min(2000, $ppm));
        $ph = max(0.0, min(14.0, $ph));
        $suhu = max(15.0, min(40.0, $suhu));

        $statusPompa = null;

        if ($ppm < $tandon->target_ppm - self::TOLERANSI_PPM) {
            $ppm = min(2000, $ppm + random_int(20, 45));
            $statusPompa = 'nutrisi';
            $this->catatDosing($tandon, "Auto dosing nutrisi ke tandon '{$tandon->nama}': PPM naik jadi {$ppm} (target {$tandon->target_ppm})");
        }

        if ($ph < $tandon->target_ph - self::TOLERANSI_PH) {
            $ph = min(14.0, $ph + round(random_int(10, 25) / 100, 1));
            $statusPompa = 'ph_up';
            $this->catatDosing($tandon, "Auto dosing pH Up ke tandon '{$tandon->nama}': pH naik jadi {$ph} (target {$tandon->target_ph})");
        } elseif ($ph > $tandon->target_ph + self::TOLERANSI_PH) {
            $ph = max(0.0, $ph - round(random_int(10, 25) / 100, 1));
            $statusPompa = 'ph_down';
            $this->catatDosing($tandon, "Auto dosing pH Down ke tandon '{$tandon->nama}': pH turun jadi {$ph} (target {$tandon->target_ph})");
        }

        $tandon->update([
            'ppm_terkini' => round($ppm),
            'ph_terkini' => round($ph, 1),
            'suhu_terkini' => round($suhu, 1),
            'status_pompa' => $statusPompa,
            'terakhir_baca_at' => now(),
        ]);

        // jadwalkan ulang dulu SEBELUM langkah apapun yang bisa gagal (catat riwayat,
        // broadcast) - supaya satu error di langkah lain (mis. Reverb tidak terjangkau,
        // atau error simpan riwayat) tidak pernah mematikan rantai simulasi permanen.
        self::dispatch($tandon->id)->delay(now()->addSeconds(self::JEDA_DETIK));

        try {
            $this->catatRiwayat($tandon, round($ppm), round($ph, 1), round($suhu, 1));
        } catch (\Throwable $e) {
            Log::warning("Tandon #{$tandon->id}: gagal catat riwayat sensor, simulasi tetap lanjut. ".$e->getMessage());
        }

        try {
            TandonUpdated::dispatch($tandon->kebun->id_owners);
        } catch (\Throwable $e) {
            Log::warning("Tandon #{$tandon->id}: broadcast TandonUpdated gagal, simulasi tetap lanjut. ".$e->getMessage());
        }
    }

    private function catatDosing(Tandon $tandon, string $keterangan): void
    {
        ActivityLog::catat('sistem', 0, 'Sistem Auto-Dosing', 'auto', 'Monitor Tandon', $keterangan, $tandon->kebun->id_owners);
    }

    /**
     * Riwayat sensor dicatat maks 1x per JEDA_CATAT_RIWAYAT_MENIT (bukan tiap tick 8 detik)
     * supaya cukup detail buat grafik per-jam tapi tidak membebani database untuk
     * penyimpanan sampai 1 minggu. Data yang lebih tua dari SIMPAN_RIWAYAT_HARI
     * langsung dihapus di sini juga, jadi tabelnya otomatis tidak pernah membengkak
     * tanpa perlu proses cleanup/scheduler terpisah.
     */
    private function catatRiwayat(Tandon $tandon, int $ppm, float $ph, float $suhu): void
    {
        $terakhir = TandonBacaan::where('id_tandon', $tandon->id)->latest('created_at')->first();

        if ($terakhir && $terakhir->created_at->gt(now()->subMinutes(self::JEDA_CATAT_RIWAYAT_MENIT))) {
            return;
        }

        TandonBacaan::create([
            'id_tandon' => $tandon->id,
            'ppm' => $ppm,
            'ph' => $ph,
            'suhu' => $suhu,
        ]);

        TandonBacaan::where('id_tandon', $tandon->id)
            ->where('created_at', '<', now()->subDays(self::SIMPAN_RIWAYAT_HARI))
            ->delete();
    }
}
