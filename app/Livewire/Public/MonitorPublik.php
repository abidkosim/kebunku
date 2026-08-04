<?php

namespace App\Livewire\Public;

use App\Models\Owner;
use App\Models\Tandon;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Halaman monitor read-only buat ditampilkan di layar TV/monitor luar jaringan -
 * TIDAK ada login, akses cuma lewat kunci_monitor si owner di URL. Tidak ada method
 * tambah/edit/hapus sama sekali di component ini, murni tampilan.
 */
class MonitorPublik extends Component
{
    public Owner $owner;

    public function mount(string $kunci)
    {
        $this->owner = Owner::where('kunci_monitor', $kunci)->firstOrFail();
    }

    #[On('echo:tandon.{owner.id},.TandonUpdated')]
    public function segarkanOtomatis()
    {
        // body kosong - kehadiran event ini cukup membuat Livewire render ulang & ambil data terbaru
    }

    public function render()
    {
        $list = Tandon::whereHas('kebun', function ($q) {
            $q->where('id_owners', $this->owner->id);
        })->with('kebun')->orderBy('nama')->get();

        return view('livewire.public.monitor-publik', [
            'list' => $list,
        ]);
    }
}
