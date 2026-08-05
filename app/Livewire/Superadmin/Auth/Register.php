<?php
namespace App\Livewire\Superadmin\Auth;

use App\Models\Superadmin;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;

class Register extends Component
{
    public $nama;
    public $username;
    public $password;
    public $showPassword = false;

    public function register()
    {
        $this->validate([
            'nama' => 'required|min:3',
            'username' => 'required|unique:superadmins,username',
            'password' => 'required|min:4',
        ]);

        Superadmin::create([
            'nama' => $this->nama,
            'username' => $this->username,
            'password' => Hash::make($this->password),
        ]);

        return redirect()->to('/superadmin/login')->with('sukses','Akun superadmin berhasil dibuat');
    }

    public function render()
    {
        return view('livewire.superadmin.auth.register');
    }
}
