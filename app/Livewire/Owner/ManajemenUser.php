<?php

namespace App\Livewire\Owner;

use Livewire\Component;
use App\Livewire\Owner\Concerns\RequiresOwnerAuth;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Hash;

class ManajemenUser extends Component
{
    use RequiresOwnerAuth;

    public $search = '';

    public $nama_form, $username_form, $password_form, $alamat_form, $role_form = 'teknisi';
    public $editId = null;
    public $showModal = false;
    public $isEditMode = false;

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
        ActivityLog::catat($this->actorType, $this->actorId, $this->actorNama, $aksi, 'User', $keterangan, $this->owner->id);
    }

    public function openCreate()
    {
        $this->reset(['nama_form', 'username_form', 'password_form', 'alamat_form', 'editId']);
        $this->role_form = 'teknisi';
        $this->isEditMode = false;
        $this->showModal = true;
    }

    public function openEdit($id)
    {
        $data = User::where('id_owners', $this->owner->id)->findOrFail($id);
        $this->editId = $data->id;
        $this->nama_form = $data->nama;
        $this->username_form = $data->username;
        $this->password_form = '';
        $this->alamat_form = $data->alamat;
        $this->role_form = $data->role;
        $this->isEditMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'nama_form' => 'required',
            'username_form' => 'required|unique:users,username,'.$this->editId,
            'password_form' => $this->isEditMode ? 'nullable|min:6' : 'required|min:6',
            'alamat_form' => 'required',
            'role_form' => 'required|in:teknisi,keuangan',
        ]);

        $payload = [
            'id_owners' => $this->owner->id,
            'nama' => $this->nama_form,
            'username' => $this->username_form,
            'alamat' => $this->alamat_form,
            'role' => $this->role_form,
        ];
        if ($this->password_form) {
            $payload['password'] = Hash::make($this->password_form);
        }

        if ($this->isEditMode) {
            User::where('id_owners', $this->owner->id)->findOrFail($this->editId)->update($payload);
            $msg = 'Data user berhasil diupdate!';
            $this->catat('update', "Mengupdate data user '{$this->nama_form}'");
        } else {
            User::create($payload);
            $msg = 'User baru berhasil ditambah!';
            $this->catat('tambah', "Menambahkan user baru '{$this->nama_form}'");
        }

        $this->showModal = false;
        $this->dispatch('alert-success', message: $msg);
    }

    public function delete($id)
    {
        $target = User::where('id_owners', $this->owner->id)->findOrFail($id);
        $namaTarget = $target->nama;
        $target->delete();
        $this->catat('hapus', "Menghapus user '{$namaTarget}'");
        $this->dispatch('alert-success', message: 'Data user berhasil dihapus');
    }

    public function render()
    {
        $list = User::where('id_owners', $this->owner->id)
            ->when($this->search, function ($q) {
                $q->where(function ($qq) {
                    $qq->where('nama', 'like', '%'.$this->search.'%')
                       ->orWhere('username', 'like', '%'.$this->search.'%');
                });
            })
            ->latest('id')
            ->get();

        $logs = ActivityLog::where('id_owners', $this->owner->id)
            ->latest('id')
            ->limit(15)
            ->get();

        return view('livewire.owner.manajemen-user', ['list' => $list, 'logs' => $logs]);
    }
}
