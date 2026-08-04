<?php

namespace App\Services;

use App\Events\TandonUpdated;
use App\Models\Tandon;
use App\Models\TandonBacaan;
use Illuminate\Support\Facades\Log;

/**
 * Titik simpan tunggal untuk "1 pembacaan sensor tandon selesai", dipakai baik oleh
 * SimulasikanTandon (mode simulasi, angka dari random-walk) maupun TandonIngestController
 * (mode iot, angka asli dari ESP32 lewat API) - supaya throttle riwayat, retensi, dan
 * broadcast realtime-nya konsisten untuk kedua sumber data, bukan diduplikasi di 2 tempat.
 */
class TandonIngestService
{
    private const JEDA_CATAT_RIWAYAT_MENIT = 5;
    private const SIMPAN_RIWAYAT_HARI = 7;

    public function simpanBacaan(Tandon $tandon, float $ppm, float $ph, float $suhu, string $sumber, ?string $statusPompa = null): void
    {
        $tandon->update([
            'ppm_terkini' => round($ppm),
            'ph_terkini' => round($ph, 1),
            'suhu_terkini' => round($suhu, 1),
            'status_pompa' => $statusPompa,
            'terakhir_baca_at' => now(),
        ]);

        try {
            $this->catatRiwayat($tandon, round($ppm), round($ph, 1), round($suhu, 1));
        } catch (\Throwable $e) {
            Log::warning("Tandon #{$tandon->id}: gagal catat riwayat sensor ({$sumber}), simulasi/ingest tetap lanjut. ".$e->getMessage());
        }

        try {
            TandonUpdated::dispatch($tandon->kebun->id_owners);
        } catch (\Throwable $e) {
            Log::warning("Tandon #{$tandon->id}: broadcast TandonUpdated gagal ({$sumber}). ".$e->getMessage());
        }
    }

    /**
     * Riwayat sensor dicatat maks 1x per JEDA_CATAT_RIWAYAT_MENIT (bukan tiap pembacaan)
     * supaya cukup detail buat grafik per-jam tapi tidak membebani database. Data yang
     * lebih tua dari SIMPAN_RIWAYAT_HARI langsung dihapus di sini juga.
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
