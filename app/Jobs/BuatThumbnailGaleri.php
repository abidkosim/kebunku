<?php

namespace App\Jobs;

use App\Models\Galeri;
use App\Events\GaleriUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BuatThumbnailGaleri implements ShouldQueue
{
    use Queueable;

    private const MAKS_LEBAR = 600;
    private const MAKS_TINGGI = 600;

    public function __construct(public int $galeriId)
    {
    }

    public function handle(): void
    {
        $item = Galeri::find($this->galeriId);
        if (!$item || $item->jenis !== 'foto') {
            return;
        }

        $isi = Storage::disk('public')->get($item->file);
        $gambarAsli = @imagecreatefromstring($isi);

        if ($gambarAsli === false) {
            Log::warning("Galeri #{$item->id}: gagal generate thumbnail, format gambar tidak didukung GD.");
            $item->update(['status' => 'failed']);
            $this->lupakanCacheGaleri($item->id_owners);
            GaleriUpdated::dispatch($item->id_owners);
            return;
        }

        $lebarAsli = imagesx($gambarAsli);
        $tinggiAsli = imagesy($gambarAsli);
        $rasio = min(self::MAKS_LEBAR / $lebarAsli, self::MAKS_TINGGI / $tinggiAsli, 1);
        $lebarBaru = (int) round($lebarAsli * $rasio);
        $tinggiBaru = (int) round($tinggiAsli * $rasio);

        $thumb = imagecreatetruecolor($lebarBaru, $tinggiBaru);
        imagecopyresampled($thumb, $gambarAsli, 0, 0, 0, 0, $lebarBaru, $tinggiBaru, $lebarAsli, $tinggiAsli);

        ob_start();
        imagejpeg($thumb, null, 82);
        $isiThumbnail = ob_get_clean();

        imagedestroy($gambarAsli);
        imagedestroy($thumb);

        $pathThumbnail = 'galeri/thumbs/'.pathinfo($item->file, PATHINFO_FILENAME).'.jpg';
        Storage::disk('public')->put($pathThumbnail, $isiThumbnail);

        $item->update(['thumbnail' => $pathThumbnail, 'status' => 'ready']);
        $this->lupakanCacheGaleri($item->id_owners);
        GaleriUpdated::dispatch($item->id_owners);
    }

    public function failed(?\Throwable $exception): void
    {
        $item = Galeri::where('id', $this->galeriId)->first();
        if ($item) {
            $item->update(['status' => 'failed']);
            $this->lupakanCacheGaleri($item->id_owners);
            GaleriUpdated::dispatch($item->id_owners);
        }
    }

    /**
     * Job ini jalan di luar Livewire (queue worker), jadi tidak bisa pakai trait
     * CachesOwnerData - tag harus dicocokkan manual persis sama dengan yang dipakai
     * KelolaGaleri, supaya status "processing" -> "ready"/"failed" langsung kelihatan
     * begitu halaman re-render lewat broadcast GaleriUpdated, bukan basi sampai TTL habis.
     */
    private function lupakanCacheGaleri(int $ownerId): void
    {
        Cache::tags(["owner{$ownerId}:galeri"])->flush();
    }
}
