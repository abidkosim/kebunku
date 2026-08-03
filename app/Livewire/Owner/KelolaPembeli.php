<?php

namespace App\Livewire\Owner;

use Livewire\Component;
use Livewire\WithPagination;
use App\Livewire\Owner\Concerns\RequiresOwnerAuth;
use App\Models\Pembeli;
use App\Models\ActivityLog;

class KelolaPembeli extends Component
{
    use RequiresOwnerAuth, WithPagination;

    public $search = '';
    public $perPage = 10;

    public $selectedPembeliId = null;

    public $showModal = false;
    public $isEditMode = false;
    public $editId = null;
    public $nama_form, $kontak_form;

    public function mount()
    {
        if ($redirect = $this->loadAuthenticatedOwner()) {
            return $redirect;
        }
        if ($redirect = $this->requireRole(['owner'])) {
            return $redirect;
        }
    }

    private function catat(string $aksi, string $keterangan): void
    {
        ActivityLog::catat($this->actorType, $this->actorId, $this->actorNama, $aksi, 'Pembeli', $keterangan, $this->owner->id);
    }

    private function pembeliQuery()
    {
        return Pembeli::where('id_owners', $this->owner->id);
    }

    public function viewDetail($id)
    {
        $this->pembeliQuery()->findOrFail($id);
        $this->selectedPembeliId = $id;
    }

    public function backToList()
    {
        $this->selectedPembeliId = null;
    }

    public function openCreate()
    {
        $this->reset(['nama_form', 'kontak_form', 'editId']);
        $this->isEditMode = false;
        $this->showModal = true;
    }

    public function openEdit($id)
    {
        $pembeli = $this->pembeliQuery()->findOrFail($id);
        $this->editId = $pembeli->id;
        $this->nama_form = $pembeli->nama;
        $this->kontak_form = $pembeli->kontak;
        $this->isEditMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'nama_form' => 'required',
            'kontak_form' => 'nullable',
        ]);

        if ($this->isEditMode) {
            $pembeli = $this->pembeliQuery()->findOrFail($this->editId);
            $pembeli->update(['nama' => $this->nama_form, 'kontak' => $this->kontak_form]);
            $this->catat('update', "Mengubah data pembeli '{$pembeli->nama}'");
        } else {
            $pembeli = Pembeli::create([
                'id_owners' => $this->owner->id,
                'nama' => $this->nama_form,
                'kontak' => $this->kontak_form,
            ]);
            $this->catat('tambah', "Menambahkan pembeli baru '{$pembeli->nama}'");
        }

        $this->showModal = false;
        $this->dispatch('alert-success', message: 'Data pembeli disimpan');
    }

    public function delete($id)
    {
        $pembeli = $this->pembeliQuery()->findOrFail($id);

        if ($pembeli->panens()->exists()) {
            $this->dispatch('alert-error', message: 'Pembeli ini sudah punya riwayat transaksi panen, tidak bisa dihapus.');
            return;
        }

        $nama = $pembeli->nama;
        $pembeli->delete();
        $this->catat('hapus', "Menghapus pembeli '{$nama}'");
        $this->dispatch('alert-success', message: 'Pembeli dihapus');
    }

    public function render()
    {
        $list = $this->pembeliQuery()
            ->withCount('panens')
            ->with('panens')
            ->when($this->search, function ($q) {
                $q->where('nama', 'like', '%'.$this->search.'%');
            })
            ->latest('id')
            ->paginate($this->perPage);

        $selected = $this->selectedPembeliId
            ? $this->pembeliQuery()->with(['panens.tanaman'])->find($this->selectedPembeliId)
            : null;

        $logs = ActivityLog::where('id_owners', $this->owner->id)
            ->latest('id')
            ->limit(15)
            ->get();

        return view('livewire.owner.kelola-pembeli', [
            'list' => $list,
            'selected' => $selected,
            'logs' => $logs,
        ]);
    }
}
