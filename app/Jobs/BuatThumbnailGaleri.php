<?php

namespace App\Jobs;

use App\Models\Galeri;
use App\Events\GaleriUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Support\Thumbnail;

class BuatThumbnailGaleri implements ShouldQueue
{
    use Queueable;

    private const MAKS_LEBAR = 600;

    public function __construct(public int $galeriId)
    {
    }

    public function handle(): void
    {
        $item = Galeri::find($this->galeriId);
        if (!$item || $item->jenis !== 'foto') {
            return;
        }

        $pathThumbnail = Thumbnail::buat($item->file, 'galeri/thumbs', self::MAKS_LEBAR, 82);

        if ($pathThumbnail === null) {
            Log::warning("Galeri #{$item->id}: gagal generate thumbnail, format gambar tidak didukung GD.");
            $item->update(['status' => 'failed']);
            $this->lupakanCacheGaleri($item->id_owners);
            GaleriUpdated::dispatch($item->id_owners);
            return;
        }

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
