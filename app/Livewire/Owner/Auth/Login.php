<?php

namespace App\Livewire\Owner\Auth;

use Livewire\Component;
use App\Models\Owner;
use App\Models\User;
use App\Support\RememberMe;
use App\Support\SesiAktor;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class Login extends Component
{
    public $username = '';
    public $password = '';
    public $remember = false;
    public $showPassword = false;

    /** Maksimal percobaan gagal sebelum dikunci sementara. */
    private const MAKS_PERCOBAAN = 5;
    private const DURASI_KUNCI_DETIK = 60;

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

        // Tanpa pembatasan ini, halaman login bisa dicoba-coba tanpa henti (tebak
        // password otomatis) - tiap percobaan juga memaksa satu hash bcrypt di server,
        // jadi selain soal keamanan ini juga jadi cara termurah membebani CPU VPS.
        $kunci = $this->kunciPembatas();

        if (RateLimiter::tooManyAttempts($kunci, self::MAKS_PERCOBAAN)) {
            $detik = RateLimiter::availableIn($kunci);
            $this->addError('username', "Terlalu banyak percobaan gagal. Coba lagi dalam {$detik} detik.");

            return null;
        }

        $owner = Owner::where('username', $this->username)->first();
        if ($owner && Hash::check($this->password, $owner->password)) {
            RateLimiter::clear($kunci);
            $this->mulaiSesi(['owner_id' => $owner->id, 'owner_nama' => $owner->nama]);

            if ($this->remember) {
                RememberMe::ingat('remember_owner', $owner);
            }

            return $this->redirect('/owner/dashboard', navigate: true);
        }

        $user = User::where('username', $this->username)->first();
        if ($user && Hash::check($this->password, $user->password)) {
            RateLimiter::clear($kunci);
            $this->mulaiSesi(['user_id' => $user->id, 'user_nama' => $user->nama, 'user_role' => $user->role]);

            if ($this->remember) {
                RememberMe::ingat('remember_user', $user);
            }

            return $this->redirect('/portal/dashboard', navigate: true);
        }

        RateLimiter::hit($kunci, self::DURASI_KUNCI_DETIK);
        $this->addError('username', 'Username atau password salah!');

        return null;
    }

    /**
     * ID sesi WAJIB diganti tepat setelah login berhasil. Kalau tidak, ID sesi yang
     * sudah dipegang seseorang sebelum korban login (mis. diselipkan lewat link) tetap
     * berlaku setelah korban login - alias sesi korban ikut terpakai orang lain
     * (session fixation).
     */
    private function mulaiSesi(array $data): void
    {
        session()->regenerate();
        session($data);
        app(SesiAktor::class)->reset();
    }

    private function kunciPembatas(): string
    {
        return 'login:'.Str::lower((string) $this->username).'|'.request()->ip();
    }
}
