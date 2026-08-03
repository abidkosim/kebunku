<?php

namespace App\Livewire\Owner;

use Livewire\Component;
use App\Livewire\Owner\Concerns\RequiresOwnerAuth;
use App\Models\Kebun;
use App\Models\Meja;
use App\Models\ActivityLog;

class KelolaKebun extends Component
{
    use RequiresOwnerAuth;

    public $showModalKebun = false;
    public $isEditModeKebun = false;
    public $editKebunId = null;
    public $namaKebun_form;
    public $jumlahMeja_form = 10;

    public function mount()
    {
        if ($redirect = $this->loadAuthenticatedOwner()) {
            return $redirect;
        }
        if ($redirect = $this->requireRole(['owner', 'teknisi'])) {
            return $redirect;
        }
    }

    private function catat(string $aksi, string $keterangan): void
    {
        ActivityLog::catat($this->actorType, $this->actorId, $this->actorNama, $aksi, 'Kebun', $keterangan, $this->owner->id);
    }

    private function kebunQuery()
    {
        return Kebun::where('id_owners', $this->owner->id);
    }

    public function openCreateKebun()
    {
        $this->reset(['namaKebun_form', 'editKebunId']);
        $this->jumlahMeja_form = 10;
        $this->isEditModeKebun = false;
        $this->showModalKebun = true;
    }

    public function openEditKebun($id)
    {
        $data = $this->kebunQuery()->findOrFail($id);
        $this->editKebunId = $data->id;
        $this->namaKebun_form = $data->nama_kebun;
        $this->isEditModeKebun = true;
        $this->showModalKebun = true;
    }

    public function saveKebun()
    {
        if ($this->isEditModeKebun) {
            $this->validate(['namaKebun_form' => 'required']);
            $this->kebunQuery()->findOrFail($this->editKebunId)->update(['nama_kebun' => $this->namaKebun_form]);
            $this->catat('update', "Mengupdate nama kebun jadi '{$this->namaKebun_form}'");
        } else {
            $this->validate([
                'namaKebun_form' => 'required',
                'jumlahMeja_form' => 'required|integer|min:1|max:100',
            ]);
            $kebun = Kebun::create(['id_owners' => $this->owner->id, 'nama_kebun' => $this->namaKebun_form]);
            for ($i = 1; $i <= $this->jumlahMeja_form; $i++) {
                Meja::create(['kebun_id' => $kebun->id, 'nomor' => $i]);
            }
            $this->catat('tambah', "Menambahkan kebun baru '{$this->namaKebun_form}' dengan {$this->jumlahMeja_form} meja");
        }

        $this->showModalKebun = false;
        $this->dispatch('alert-success', message: 'Kebun berhasil disimpan');
    }

    public function deleteKebun($id)
    {
        $kebun = $this->kebunQuery()->with('meja.tanaman')->findOrFail($id);
        $terpakai = $kebun->meja->contains(fn ($m) => $m->tanaman->contains(fn ($t) => is_null($t->siklus_selesai_at)));

        if ($terpakai) {
            $this->dispatch('alert-error', message: 'Tidak bisa hapus kebun, masih ada meja yang terpakai tanaman.');
            return;
        }

        $nama = $kebun->nama_kebun;
        $kebun->delete();
        $this->catat('hapus', "Menghapus kebun '{$nama}'");
        $this->dispatch('alert-success', message: 'Kebun berhasil dihapus');
    }

    public function tambahMeja($kebunId)
    {
        $kebun = $this->kebunQuery()->findOrFail($kebunId);
        $nomorBaru = ($kebun->meja()->max('nomor') ?? 0) + 1;
        Meja::create(['kebun_id' => $kebun->id, 'nomor' => $nomorBaru]);
        $this->catat('tambah', "Menambahkan meja #{$nomorBaru} di kebun '{$kebun->nama_kebun}'");
        $this->dispatch('alert-success', message: 'Meja baru ditambahkan');
    }

    public function hapusMeja($mejaId)
    {
        $meja = Meja::whereHas('kebun', function ($q) {
            $q->where('id_owners', $this->owner->id);
        })->with('tanaman')->findOrFail($mejaId);

        if ($meja->tanaman->contains(fn ($t) => is_null($t->siklus_selesai_at))) {
            $this->dispatch('alert-error', message: 'Meja ini masih terpakai, tidak bisa dihapus.');
            return;
        }

        $namaKebun = $meja->kebun->nama_kebun;
        $nomor = $meja->nomor;
        $meja->delete();
        $this->catat('hapus', "Menghapus meja #{$nomor} di kebun '{$namaKebun}'");
        $this->dispatch('alert-success', message: 'Meja berhasil dihapus');
    }

    public function render()
    {
        $list = $this->kebunQuery()->with('meja.tanaman')->latest('id')->get();

        $logs = ActivityLog::where('id_owners', $this->owner->id)
            ->latest('id')
            ->limit(15)
            ->get();

        return view('livewire.owner.kelola-kebun', ['list' => $list, 'logs' => $logs]);
    }
}
