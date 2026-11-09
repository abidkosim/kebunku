<?php

namespace App\Jobs;

use App\Models\Absensi;
use App\Support\Thumbnail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

/**
 * Membuat thumbnail foto kunjungan di background, supaya staff yang absen tidak perlu
 * menunggu proses resize foto kamera (bisa 5-8 MB) saat menekan Simpan.
 */
class BuatThumbnailAbsensi implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $absensiId)
    {
    }

    public function handle(): void
    {
        $item = Absensi::find($this->absensiId);
        if (!$item || !$item->foto) {
            return;
        }

        $path = Thumbnail::buat($item->foto, 'absensi/thumbs', 240, 78);
        if (!$path) {
            return;
        }

        // updateQuietly: kolom thumbnail tidak perlu memicu observer/event apa pun.
        $item->updateQuietly(['thumbnail' => $path]);

        // Tag harus sama persis dengan yang dipakai KelolaAbsensi (CachesOwnerData).
        Cache::tags(["owner{$item->id_owners}:absensi"])->flush();
    }
}
