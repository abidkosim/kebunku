<?php
namespace App\Livewire\Superadmin;

use Livewire\Component;
use App\Models\Superadmin;
use Illuminate\Support\Facades\Hash;

class Dashboard extends Component
{
    public $nama, $username, $password_baru, $search = '';
    public $nama_form, $username_form, $password_form;
    public $editId = null;
    public $showModal = false;
    public $isEditMode = false;

    public $superadmin;

    public function mount()
    {
        if (!session()->has('superadmin_id')) {
            return redirect('/superadmin/login');
        }
        $this->superadmin = Superadmin::find(session('superadmin_id'));
        $this->nama = $this->superadmin->nama;
        $this->username = $this->superadmin->username;
    }

    // --- CRUD ---
    public function openCreate()
    {
        $this->reset(['nama_form','username_form','password_form','editId']);
        $this->isEditMode = false;
        $this->showModal = true;
    }

    public function openEdit($id)
    {
        $data = Superadmin::find($id);
        $this->editId = $data->id;
        $this->nama_form = $data->nama;
        $this->username_form = $data->username;
        $this->password_form = '';
        $this->isEditMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'nama_form' => 'required',
            'username_form' => 'required|unique:superadmins,username,'.$this->editId,
            'password_form' => $this->isEditMode? 'nullable|min:6' : 'required|min:6',
        ]);

        $payload = [
            'nama' => $this->nama_form,
            'username' => $this->username_form,
        ];
        if($this->password_form){
            $payload['password'] = Hash::make($this->password_form);
        }

        if($this->isEditMode){
            Superadmin::find($this->editId)->update($payload);
            $msg = 'Data berhasil diupdate!';
        } else {
            Superadmin::create($payload);
            $msg = 'Superadmin baru berhasil ditambah!';
        }

        $this->showModal = false;
        $this->dispatch('alert-success', message: $msg);
    }

    public function delete($id)
    {
        if($id == session('superadmin_id')){
            $this->dispatch('alert-error', message: 'Tidak bisa hapus akun sendiri bro!');
            return;
        }
        Superadmin::find($id)->delete();
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
        $this->dispatch('alert-success', message: 'Profil pribadimu berhasil diupdate!');
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
        ->latest()
        ->get();

    return view('livewire.superadmin.dashboard', ['list' => $list]);
}
}