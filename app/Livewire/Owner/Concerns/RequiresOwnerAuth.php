<?php

namespace App\Livewire\Owner\Concerns;

use App\Models\Owner;
use App\Models\User;
use App\Support\RememberMe;

/**
 * Auth resolver dipakai lintas panel Owner & Staff (teknisi/keuangan).
 * $owner selalu berarti scope data bisnis (pemilik kebun), siapapun yang login.
 * $actorType/$actorNama berarti SIAPA yang sedang login: 'owner', 'teknisi', atau 'keuangan'.
 */
trait RequiresOwnerAuth
{
    public $owner;
    public $actorType;
    public $actorId;
    public $actorNama;
    public $actorFotoUrl;

    protected function loadAuthenticatedOwner()
    {
        // pesan yang di-flash dari redirect sebelumnya (mis. requireAksesPenuh()) - tampilkan
        // sekali lewat toast yang sama dengan alert-success/alert-error yang sudah ada.
        if ($pesan = session()->pull('alert-error')) {
            $this->dispatch('alert-error', message: $pesan);
        }

        if (session()->has('owner_id')) {
            $this->owner = Owner::find(session('owner_id'));

            if (!$this->owner) {
                session()->forget(['owner_id', 'owner_nama']);
                return redirect('/');
            }

            $this->actorType = 'owner';
            $this->actorId = $this->owner->id;
            $this->actorNama = $this->owner->nama;
            $this->actorFotoUrl = $this->owner->foto_url;
            return;
        }

        if (session()->has('user_id')) {
            $user = User::find(session('user_id'));

            if (!$user) {
                session()->forget(['user_id', 'user_nama', 'user_role']);
                return redirect('/');
            }

            $this->owner = Owner::find($user->id_owners);

            if (!$this->owner) {
                session()->forget(['user_id', 'user_nama', 'user_role']);
                return redirect('/');
            }

            $this->actorType = $user->role; // 'teknisi' | 'keuangan'
            $this->actorId = $user->id;
            $this->actorNama = $user->nama;
            $this->actorFotoUrl = $user->foto_url;
            return;
        }

        // belum ada session - coba auto-login lewat cookie "ingat saya"
        if ($owner = RememberMe::cariDariCookie('remember_owner', Owner::class)) {
            session(['owner_id' => $owner->id, 'owner_nama' => $owner->nama]);
            $this->owner = $owner;
            $this->actorType = 'owner';
            $this->actorId = $owner->id;
            $this->actorNama = $owner->nama;
            $this->actorFotoUrl = $owner->foto_url;
            return;
        }

        if ($user = RememberMe::cariDariCookie('remember_user', User::class)) {
            $this->owner = Owner::find($user->id_owners);

            if (!$this->owner) {
                return redirect('/');
            }

            session(['user_id' => $user->id, 'user_nama' => $user->nama, 'user_role' => $user->role]);
            $this->actorType = $user->role;
            $this->actorId = $user->id;
            $this->actorNama = $user->nama;
            $this->actorFotoUrl = $user->foto_url;
            return;
        }

        return redirect('/');
    }

    /**
     * Batasi akses komponen ini ke role tertentu saja. Kalau tidak sesuai,
     * arahkan ke "rumah" masing-masing role supaya tetap UX-friendly (bukan 403 mentah).
     */
    protected function requireRole(array $allowedRoles)
    {
        if (in_array($this->actorType, $allowedRoles, true)) {
            return;
        }

        return match ($this->actorType) {
            'owner' => redirect('/owner/dashboard'),
            'teknisi', 'keuangan' => redirect('/portal/dashboard'),
            default => redirect('/'),
        };
    }

    /**
     * Batasi akses komponen ini ke owner yang trial/langganannya masih aktif.
     * Dipakai fitur premium (Monitor Tandon, Galeri) - lihat Owner::punyaAksesPenuh().
     */
    protected function requireAksesPenuh()
    {
        if ($this->owner->punyaAksesPenuh()) {
            return;
        }

        session()->flash('alert-error', 'Trial/langganan Anda sudah berakhir. Hubungi admin untuk upgrade ke Pro.');

        return match ($this->actorType) {
            'owner' => redirect('/owner/dashboard'),
            'teknisi', 'keuangan' => redirect('/portal/dashboard'),
            default => redirect('/'),
        };
    }

    /**
     * Dipanggil begitu dropdown notifikasi dibuka - satu feed aktivitas dipakai bersama
     * seluruh actor bisnis ini (owner+teknisi+keuangan, lihat ActivityLog::catat()), jadi
     * "sudah dibaca" juga disimpan di level owner (bukan per-actor) supaya siapapun yang
     * buka dropdown ini langsung menghilangkan titik merahnya untuk semua yang login di
     * bisnis yang sama - konsisten dengan feed yang memang digabung, bukan per-orang.
     */
    public function tandaiNotifikasiDibaca(): void
    {
        $this->owner->update(['notifikasi_dibaca_at' => now()]);
    }

    public function logout()
    {
        if ($this->actorType === 'owner' && $this->owner) {
            RememberMe::lupakan('remember_owner', $this->owner);
        } elseif (in_array($this->actorType, ['teknisi', 'keuangan'], true) && $this->actorId) {
            RememberMe::lupakan('remember_user', User::find($this->actorId));
        }

        session()->forget(['owner_id', 'owner_nama', 'user_id', 'user_nama', 'user_role']);
        return $this->redirect('/', navigate: true);
    }
}
