<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Dipancarkan setiap ada pembacaan sensor baru / auto-dosing / perubahan target
 * di sebuah tandon, supaya halaman Monitor Tandon yang lagi dibuka owner
 * auto-update tanpa refresh. Payload sengaja cuma ownerId, sama seperti pola
 * GaleriUpdated - data sebenarnya tetap diambil lewat request Livewire yang
 * sudah terautentikasi.
 */
class TandonUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(public int $ownerId)
    {
    }

    public function broadcastOn(): array
    {
        return [new Channel('tandon.'.$this->ownerId)];
    }

    public function broadcastAs(): string
    {
        return 'TandonUpdated';
    }
}
