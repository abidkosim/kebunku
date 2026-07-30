<?php
namespace App\Livewire\Superadmin\Auth;
use App\Models\Superadmin;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class Login extends Component
{
    #[Layout('components.layouts.guest')]
    public $username, $password;
    public $remember = true;

    public function login()
    {
        $this->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        $admin = Superadmin::where('username', $this->username)->first();

        if($admin && Hash::check($this->password, $admin->password)){
            Auth::guard('superadmin')->login($admin, $this->remember);
            return redirect()->to('/superadmin/dashboard');
        }

        $this->addError('username','Username atau password salah');
    }

    public function render()
    {
        return view('livewire.superadmin.auth.login');
    }
}