<?php

namespace App\Jobs;

use App\Models\Tandon;
use App\Services\TandonIngestService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Simulasi pembacaan sensor TDS/pH/suhu untuk 1 tandon (hardware IoT belum ada,
 * jadi datanya di-generate di sini). Job ini men-dispatch dirinya sendiri lagi
 * dengan delay di akhir handle() - selama "php artisan queue:work/listen" jalan,
 * rantai ini terus hidup dan datanya berasa realtime tanpa perlu proses scheduler
 * terpisah. Rantai berhenti sendiri begitu status_simulasi tandon diubah jadi
 * 'berhenti' atau tandonnya dihapus.
 *
 * Auto-dosing (kalau ppm/pH di luar toleransi) DIPISAH ke App\Jobs\JalankanAutoDosing
 * (siklus pompa nyala -> tunggu -> cek -> retry, punya timing sendiri) - job ini cuma
 * TRIGGER-nya, dan berhenti sentuh ppm/pH/suhu selama siklus dosing itu masih berjalan
 * (ditandai status_pompa terisi) supaya dua job tidak rebutan update baris yang sama.
 */
class SimulasikanTandon implements ShouldQueue
{
    use Queueable;

    private const JEDA_DETIK = 8;
    private const TOLERANSI_PPM = 30;
    private const TOLERANSI_PH = 0.2;

    public function __construct(public int $tandonId)
    {
    }

    public function handle(TandonIngestService $ingest): void
    {
        $tandon = Tandon::with('kebun')->find($this->tandonId);

        if (!$tandon || !$tandon->kebun || $tandon->status_simulasi !== 'aktif' || $tandon->sumber_data !== 'simulasi') {
            return;
        }

        // jadwalkan ulang dulu SEBELUM operasi apapun yang bisa gagal - supaya satu error
        // di langkah lain tidak pernah mematikan rantai simulasi permanen.
        self::dispatch($tandon->id)->delay(now()->addSeconds(self::JEDA_DETIK));

        if ($tandon->status_pompa !== null) {
            // siklus auto-dosing (JalankanAutoDosing) sedang pegang tandon ini - jangan
            // sentuh ppm/pH/suhu di tick ini, cukup jaga heartbeat 8 detik di atas.
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

        $ingest->simpanBacaan($tandon, $ppm, $ph, $suhu, sumber: 'simulasi', statusPompa: null);

        if ($ppm < $tandon->target_ppm - self::TOLERANSI_PPM) {
            JalankanAutoDosing::dispatch($tandon->id, jenis: 'nutrisi');
        } elseif ($ph < $tandon->target_ph - self::TOLERANSI_PH) {
            JalankanAutoDosing::dispatch($tandon->id, jenis: 'ph_up');
        } elseif ($ph > $tandon->target_ph + self::TOLERANSI_PH) {
            JalankanAutoDosing::dispatch($tandon->id, jenis: 'ph_down');
        }
    }
}
