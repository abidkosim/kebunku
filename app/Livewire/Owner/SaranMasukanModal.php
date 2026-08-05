<?php

namespace App\Livewire\Owner;

use App\Livewire\Owner\Concerns\RequiresOwnerAuth;
use App\Models\Saran;
use Livewire\Component;

/**
 * Selalu di-embed sekali di shell owner/staff (lihat components/owner/shell.blade.php
 * & components/staff/shell.blade.php) - tombol "Saran & Masukan" di bawah sidebar,
 * bukan halaman tersendiri.
 */
class SaranMasukanModal extends Component
{
    use RequiresOwnerAuth;

    public $showModal = false;
    public $pesan = '';

    public function mount()
    {
        if ($redirect = $this->loadAuthenticatedOwner()) {
            return $redirect;
        }
    }

    public function buka()
    {
        $this->showModal = true;
    }

    public function kirim()
    {
        $this->validate([
            'pesan' => 'required|string|min:5|max:2000',
        ]);

        Saran::create([
            'id_owners' => $this->owner->id,
            'actor_type' => $this->actorType,
            'actor_id' => $this->actorId,
            'actor_nama' => $this->actorNama,
            'pesan' => $this->pesan,
        ]);

        $this->reset('pesan');
        $this->showModal = false;
        $this->dispatch('alert-success', message: 'Terima kasih, saran & masukan Anda sudah terkirim');
    }

    public function render()
    {
        return view('livewire.owner.saran-masukan-modal');
    }
}
