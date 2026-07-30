<?php

namespace App\Livewire\Superadmin\Auth;

use Livewire\Component;
use App\Models\Superadmin;
use Illuminate\Support\Facades\Hash;

class Login extends Component
{
    public $username = '';
    public $password = '';
    public $remember = false;
    public $showPassword = false;

    public function render()
    {
        return view('livewire.superadmin.auth.login');
    }

    public function login()
    {
        $this->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $superadmin = Superadmin::where('username', $this->username)->first();

        if (!$superadmin || !Hash::check($this->password, $superadmin->password)) {
            $this->addError('username', 'Username atau password salah!');
            return;
        }

        // Simpan session login
        session([
            'superadmin_id' => $superadmin->id,
            'superadmin_nama' => $superadmin->nama,
        ]);

        // KIRIM ALERT SUKSES KE FRONTEND
        $this->dispatch('login-success', nama: $superadmin->nama);
    }
}