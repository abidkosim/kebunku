<?php

namespace App\Livewire\Owner\Auth;

use Livewire\Component;
use App\Models\Owner;
use App\Models\User;
use App\Support\RememberMe;
use Illuminate\Support\Facades\Hash;

class Login extends Component
{
    public $username = '';
    public $password = '';
    public $remember = false;
    public $showPassword = false;

    public function render()
    {
        return view('livewire.owner.auth.login');
    }

    public function login()
    {
        $this->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $owner = Owner::where('username', $this->username)->first();
        if ($owner && Hash::check($this->password, $owner->password)) {
            session([
                'owner_id' => $owner->id,
                'owner_nama' => $owner->nama,
            ]);
            if ($this->remember) {
                RememberMe::ingat('remember_owner', $owner);
            }
            return $this->redirect('/owner/dashboard', navigate: true);
        }

        $user = User::where('username', $this->username)->first();
        if ($user && Hash::check($this->password, $user->password)) {
            session([
                'user_id' => $user->id,
                'user_nama' => $user->nama,
                'user_role' => $user->role,
            ]);
            if ($this->remember) {
                RememberMe::ingat('remember_user', $user);
            }
            return $this->redirect('/portal/dashboard', navigate: true);
        }

        $this->addError('username', 'Username atau password salah!');
    }
}
