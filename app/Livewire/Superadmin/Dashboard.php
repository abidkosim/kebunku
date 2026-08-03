<?php
namespace App\Livewire\Superadmin;

use Livewire\Component;
use App\Models\Superadmin;
use App\Models\Owner;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Hash;

class Dashboard extends Component
{
    public $nama, $username, $password_baru, $search = '';
    public $nama_form, $username_form, $password_form, $akses_form = 'full';
    public $editId = null;
    public $showModal = false;
    public $isEditMode = false;

    public $searchOwner = '';
    public $namaOwner_form, $namaUsahaOwner_form, $usernameOwner_form, $passwordOwner_form, $alamatOwner_form;
    public $editIdOwner = null;
    public $showModalOwner = false;
    public $isEditModeOwner = false;

    public $superadmin;

    public function mount()
    {
        if (!session()->has('superadmin_id')) {
            return redirect('/superadmin/login');
        }
        $this->superadmin = Superadmin::find(session('superadmin_id'));

        if (!$this->superadmin) {
            session()->forget('superadmin_id');
            return redirect('/superadmin/login');
        }

        $this->nama = $this->superadmin->nama;
        $this->username = $this->superadmin->username;
    }

    public function isReadOnly(): bool
    {
        return $this->superadmin?->akses === 'read_only';
    }

    private function blockIfReadOnly(): bool
    {
        if ($this->isReadOnly()) {
            $this->dispatch('alert-error', message: 'Akun read-only tidak bisa melakukan perubahan data.');
            return true;
        }
        return false;
    }

    private function catat(string $aksi, string $modul, string $keterangan): void
    {
        ActivityLog::catat('superadmin', $this->superadmin->id, $this->superadmin->nama, $aksi, $modul, $keterangan);
    }

    // --- CRUD ---
    public function openCreate()
    {
        if ($this->blockIfReadOnly()) return;
        $this->reset(['nama_form','username_form','password_form','editId']);
        $this->akses_form = 'full';
        $this->isEditMode = false;
        $this->showModal = true;
    }

    public function openEdit($id)
    {
        if ($this->blockIfReadOnly()) return;
        $data = Superadmin::find($id);
        $this->editId = $data->id;
        $this->nama_form = $data->nama;
        $this->username_form = $data->username;
        $this->password_form = '';
        $this->akses_form = $data->akses;
        $this->isEditMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        if ($this->blockIfReadOnly()) return;

        $this->validate([
            'nama_form' => 'required',
            'username_form' => 'required|unique:superadmins,username,'.$this->editId,
            'password_form' => $this->isEditMode? 'nullable|min:6' : 'required|min:6',
            'akses_form' => 'required|in:full,read_only',
        ]);

        $payload = [
            'nama' => $this->nama_form,
            'username' => $this->username_form,
            'akses' => $this->akses_form,
        ];
        if($this->password_form){
            $payload['password'] = Hash::make($this->password_form);
        }

        if($this->isEditMode){
            Superadmin::find($this->editId)->update($payload);
            $msg = 'Data berhasil diupdate!';
            $this->catat('update', 'Superadmin', "Mengupdate data superadmin '{$this->nama_form}'");
        } else {
            Superadmin::create($payload);
            $msg = 'Superadmin baru berhasil ditambah!';
            $this->catat('tambah', 'Superadmin', "Menambahkan superadmin baru '{$this->nama_form}'");
        }

        $this->showModal = false;
        $this->dispatch('alert-success', message: $msg);
    }

    public function delete($id)
    {
        if ($this->blockIfReadOnly()) return;
        if($id == session('superadmin_id')){
            $this->dispatch('alert-error', message: 'Tidak bisa hapus akun sendiri bro!');
            return;
        }
        $target = Superadmin::find($id);
        $namaTarget = $target->nama;
        $target->delete();
        $this->catat('hapus', 'Superadmin', "Menghapus superadmin '{$namaTarget}'");
        $this->dispatch('alert-success', message: 'Data berhasil dihapus');
    }

    public function updateAkunPribadi()
    {
        $this->validate([
            'nama' => 'required',
            'username' => 'required|unique:superadmins,username,'.$this->superadmin->id,
        ]);
        $this->superadmin->update(['nama'=>$this->nama,'username'=>$this->username]);
        if($this->password_baru){
            $this->superadmin->update(['password'=>Hash::make($this->password_baru)]);
            $this->password_baru = '';
        }
        session(['superadmin_nama'=>$this->nama]);
        $this->catat('update', 'Profil', 'Memperbarui profil sendiri');
        $this->dispatch('alert-success', message: 'Profil pribadimu berhasil diupdate!');
    }

    // --- CRUD OWNER ---
public function openCreateOwner()
{
    if ($this->blockIfReadOnly()) return;
    $this->reset(['namaOwner_form','namaUsahaOwner_form','usernameOwner_form','passwordOwner_form','alamatOwner_form','editIdOwner']);
    $this->isEditModeOwner = false;
    $this->showModalOwner = true;
}

public function openEditOwner($id)
{
    if ($this->blockIfReadOnly()) return;
    $data = Owner::find($id);
    $this->editIdOwner = $data->id;
    $this->namaOwner_form = $data->nama;
    $this->namaUsahaOwner_form = $data->nama_usaha;
    $this->usernameOwner_form = $data->username;
    $this->passwordOwner_form = '';
    $this->alamatOwner_form = $data->alamat;
    $this->isEditModeOwner = true;
    $this->showModalOwner = true;
}

public function saveOwner()
{
    if ($this->blockIfReadOnly()) return;

    $this->validate([
        'namaOwner_form' => 'required',
        'namaUsahaOwner_form' => 'required',
        'usernameOwner_form' => 'required|unique:owners,username,'.$this->editIdOwner,
        'passwordOwner_form' => $this->isEditModeOwner ? 'nullable|min:6' : 'required|min:6',
        'alamatOwner_form' => 'required',
    ]);

    $payload = [
        'nama' => $this->namaOwner_form,
        'nama_usaha' => $this->namaUsahaOwner_form,
        'username' => $this->usernameOwner_form,
        'alamat' => $this->alamatOwner_form,
    ];
    if ($this->passwordOwner_form) {
        $payload['password'] = Hash::make($this->passwordOwner_form);
    }

    if ($this->isEditModeOwner) {
        Owner::find($this->editIdOwner)->update($payload);
        $msg = 'Data owner berhasil diupdate!';
        $this->catat('update', 'Owner', "Mengupdate data owner '{$this->namaOwner_form}'");
    } else {
        Owner::create($payload);
        $msg = 'Owner baru berhasil ditambah!';
        $this->catat('tambah', 'Owner', "Menambahkan owner baru '{$this->namaOwner_form}' ({$this->namaUsahaOwner_form})");
    }

    $this->showModalOwner = false;
    $this->dispatch('alert-success', message: $msg);
}

public function deleteOwner($id)
{
    if ($this->blockIfReadOnly()) return;
    $target = Owner::find($id);
    $namaTarget = $target->nama;
    $target->delete();
    $this->catat('hapus', 'Owner', "Menghapus owner '{$namaTarget}'");
    $this->dispatch('alert-success', message: 'Data owner berhasil dihapus');
}

    public function logout()
    {
        session()->forget(['superadmin_id', 'superadmin_nama']);
        return $this->redirect('/superadmin/login', navigate: true);
    }

    public function render()
{
    $list = Superadmin::query()
        ->when($this->search, function($q){
            $q->where(function($qq){
                $qq->where('nama', 'like', '%'.$this->search.'%')
                   ->orWhere('username', 'like', '%'.$this->search.'%');
            });
        })
        ->latest('id')
        ->get();

    $listOwner = Owner::query()
        ->when($this->searchOwner, function ($q) {
            $q->where(function ($qq) {
                $qq->where('nama', 'like', '%'.$this->searchOwner.'%')
                   ->orWhere('nama_usaha', 'like', '%'.$this->searchOwner.'%')
                   ->orWhere('username', 'like', '%'.$this->searchOwner.'%');
            });
        })
        ->latest('id')
        ->get();

    $logs = ActivityLog::latest('id')->limit(15)->get();

    return view('livewire.superadmin.dashboard', ['list' => $list, 'listOwner' => $listOwner, 'logs' => $logs]);
}
}