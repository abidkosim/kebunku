<?php

namespace App\Livewire\Owner\Concerns;

use App\Models\Owner;
use App\Models\User;

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

    public function logout()
    {
        session()->forget(['owner_id', 'owner_nama', 'user_id', 'user_nama', 'user_role']);
        return $this->redirect('/', navigate: true);
    }
}
