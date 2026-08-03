<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Dipancarkan setiap ada perubahan data Galeri (upload/edit/hapus/thumbnail selesai diproses)
 * supaya semua browser yang lagi buka halaman Galeri untuk owner yang sama auto-update
 * tanpa perlu refresh manual. Payload sengaja kosong (cuma sinyal "ada perubahan, muat ulang
 * datanya") - data sebenarnya tetap diambil lewat request Livewire yang sudah terautentikasi,
 * bukan dari isi broadcast, supaya tidak ada data bisnis yang bocor lewat channel publik ini.
 */
class GaleriUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(public int $ownerId)
    {
    }

    public function broadcastOn(): array
    {
        return [new Channel('galeri.'.$this->ownerId)];
    }

    public function broadcastAs(): string
    {
        return 'GaleriUpdated';
    }
}
