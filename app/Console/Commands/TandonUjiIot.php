<?php

namespace App\Console\Commands;

use App\Models\Tandon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Alat tes lokal buat pura-pura jadi ESP32 main - kirim HTTP POST BENERAN ke endpoint
 * ingest API tandon (pakai device_token-nya), bukan cuma panggil method PHP langsung.
 * Dipakai buat verifikasi jalur ESP32->API sebelum hardware asli tersedia (poin 5).
 */
#[Signature('tandon:uji-iot {tandon : ID tandon} {--kali=1 : Berapa kali kirim} {--jeda=5 : Jeda detik antar kiriman}')]
#[Description('Kirim data sensor dummy ke endpoint ingest IoT tandon, mensimulasikan ESP32 main')]
class TandonUjiIot extends Command
{
    public function handle(): int
    {
        $tandon = Tandon::find($this->argument('tandon'));

        if (!$tandon) {
            $this->error("Tandon #{$this->argument('tandon')} tidak ditemukan.");
            return self::FAILURE;
        }

        if ($tandon->sumber_data !== 'iot' || !$tandon->device_token) {
            $this->error("Tandon '{$tandon->nama}' belum mode IoT / belum punya device_token. Aktifkan dulu di halaman Monitor Tandon.");
            return self::FAILURE;
        }

        $url = rtrim(config('app.url'), '/')."/api/tandon/{$tandon->id}/bacaan";
        $kali = max(1, (int) $this->option('kali'));
        $jeda = max(0, (int) $this->option('jeda'));

        $ppm = (float) ($tandon->ppm_terkini ?? $tandon->target_ppm);
        $ph = (float) ($tandon->ph_terkini ?? $tandon->target_ph);
        $suhu = (float) ($tandon->suhu_terkini ?? 27.0);

        for ($i = 1; $i <= $kali; $i++) {
            $ppm = max(0, min(2000, $ppm + random_int(-15, 15)));
            $ph = max(0.0, min(14.0, $ph + random_int(-10, 10) / 100));
            $suhu = max(15.0, min(40.0, $suhu + random_int(-5, 5) / 10));

            $response = Http::withHeaders(['X-Device-Token' => $tandon->device_token])
                ->post($url, [
                    'ppm' => round($ppm),
                    'ph' => round($ph, 1),
                    'suhu' => round($suhu, 1),
                ]);

            if ($response->successful()) {
                $this->info("[{$i}/{$kali}] OK - ppm=".round($ppm).", ph=".round($ph, 1).", suhu=".round($suhu, 1));
            } else {
                $this->error("[{$i}/{$kali}] GAGAL ({$response->status()}) - ".$response->body());
            }

            if ($i < $kali && $jeda > 0) {
                sleep($jeda);
            }
        }

        return self::SUCCESS;
    }
}
