<?php

namespace App\Livewire\Owner;

use Livewire\Component;
use Livewire\WithPagination;
use App\Livewire\Owner\Concerns\RequiresOwnerAuth;
use App\Livewire\Owner\Concerns\CachesOwnerData;
use App\Models\Pembeli;
use App\Models\ActivityLog;

class KelolaPembeli extends Component
{
    use RequiresOwnerAuth, WithPagination, CachesOwnerData;

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

        $this->forgetOwnerCache(['pembeli', 'activity_log']);
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
        $this->forgetOwnerCache(['pembeli', 'activity_log']);
        $this->dispatch('alert-success', message: 'Pembeli dihapus');
    }

    public function render()
    {
        $cacheKey = 'pembeli:list:page'.$this->getPage().':per'.$this->perPage.':s'.md5($this->search ?? '');
        // denganRekap() menghitung total kg/transaksi/dibayar/hutang lewat sub-query
        // agregat. Sebelumnya baris ini pakai with('panens'), yang menarik SELURUH
        // riwayat panen setiap pembeli di halaman ini ke memori hanya untuk dijumlahkan.
        $list = $this->rememberOwnerCache(['pembeli', 'panen'], $cacheKey, 300, fn () =>
            $this->pembeliQuery()
                ->denganRekap()
                ->when($this->search, function ($q) {
                    $q->where('nama', 'like', '%'.$this->search.'%');
                })
                ->latest('pembeli.id')
                ->paginate($this->perPage)
        );

        $selected = $this->selectedPembeliId
            ? $this->rememberOwnerCache(['pembeli', 'panen'], "pembeli:detail:{$this->selectedPembeliId}", 300, fn () =>
                $this->pembeliQuery()->with(['panens.tanaman'])->find($this->selectedPembeliId)
            )
            : null;

        $logs = $this->rememberOwnerCache(['activity_log'], 'activity_log:recent', 120, fn () =>
            ActivityLog::where('id_owners', $this->owner->id)->latest('id')->limit(15)->get()
        );

        return view('livewire.owner.kelola-pembeli', [
            'list' => $list,
            'selected' => $selected,
            'logs' => $logs,
        ]);
    }
}
