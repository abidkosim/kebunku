<?php
namespace App\Livewire\Superadmin\Auth;
use App\Models\Superadmin;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Hash;

class Register extends Component
{
    #[Layout('components.layouts.guest')]
    public $nama, $username, $password;

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

        return redirect()->to('/superadmin/login')->with('sukses','Akun superadmin berhasil dibuat, silakan login');
    }

    public function render()
    {
        return view('livewire.superadmin.auth.register');
    }
}