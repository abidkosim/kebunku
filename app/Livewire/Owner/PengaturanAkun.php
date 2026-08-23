<?php

namespace App\Livewire\Owner;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Livewire\Owner\Concerns\RequiresOwnerAuth;
use App\Livewire\Owner\Concerns\CachesOwnerData;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class PengaturanAkun extends Component
{
    use RequiresOwnerAuth, WithFileUploads, CachesOwnerData;

    public $nama_form;
    public $namaUsaha_form;
    public $username_form;
    public $alamat_form;
    public $passwordBaru_form;
    public $passwordKonfirmasi_form;

    public $foto_upload;
    public $fotoUrlSaatIni;

    public function mount()
    {
        if ($redirect = $this->loadAuthenticatedOwner()) {
            return $redirect;
        }
        if ($redirect = $this->requireRole(['owner', 'teknisi', 'keuangan'])) {
            return $redirect;
        }

        $akun = $this->akunSaya();
        $this->nama_form = $akun->nama;
        $this->username_form = $akun->username;
        $this->alamat_form = $akun->alamat;
        $this->fotoUrlSaatIni = $akun->foto_url;

        if ($this->actorType === 'owner') {
            $this->namaUsaha_form = $akun->nama_usaha;
        }
    }

    private function akunSaya()
    {
        return $this->actorType === 'owner' ? $this->owner : User::findOrFail($this->actorId);
    }

    public function save()
    {
        $akun = $this->akunSaya();
        $isOwner = $this->actorType === 'owner';
        $tabel = $isOwner ? 'owners' : 'users';

        $rules = [
            'nama_form' => 'required',
            'username_form' => "required|unique:{$tabel},username,{$akun->id}",
            'alamat_form' => 'required',
            'passwordBaru_form' => 'nullable|min:6',
            'foto_upload' => 'nullable|image|max:2048',
        ];
        if ($isOwner) {
            $rules['namaUsaha_form'] = 'required';
        }
        if ($this->passwordBaru_form) {
            $rules['passwordKonfirmasi_form'] = 'required|same:passwordBaru_form';
        }

        $this->validate($rules);

        $payload = [
            'nama' => $this->nama_form,
            'username' => $this->username_form,
            'alamat' => $this->alamat_form,
        ];
        if ($isOwner) {
            $payload['nama_usaha'] = $this->namaUsaha_form;
        }
        if ($this->passwordBaru_form) {
            $payload['password'] = Hash::make($this->passwordBaru_form);
        }
        if ($this->foto_upload) {
            if ($akun->foto) {
                Storage::disk('public')->delete($akun->foto);
            }
            $payload['foto'] = $this->foto_upload->store('profil', 'public');
        }

        $akun->update($payload);

        if ($isOwner) {
            session(['owner_nama' => $akun->nama]);
        } else {
            session(['user_nama' => $akun->nama]);
        }
        $this->actorNama = $akun->nama;
        $this->fotoUrlSaatIni = $akun->fresh()->foto_url;
        $this->foto_upload = null;
        $this->passwordBaru_form = null;
        $this->passwordKonfirmasi_form = null;

        ActivityLog::catat($this->actorType, $this->actorId, $this->actorNama, 'update', 'Profil', 'Memperbarui profil sendiri', $this->owner->id);
        // Tanpa ini, dropdown "Aktivitas Terbaru" di halaman lain masih menampilkan feed
        // lama sampai TTL cache-nya habis - satu-satunya modul yang lupa mem-flush.
        // Tag 'users' ikut dibuang karena nama/foto akun yang baru saja diubah juga
        // tampil di daftar Manajemen User.
        $this->forgetOwnerCache(['activity_log', 'users']);

        $this->dispatch('alert-success', message: 'Profil berhasil diperbarui');
    }

    public function render()
    {
        // Disamakan dengan modul lain: feed notifikasi yang identik ini di-cache dengan
        // tag & TTL yang sama, bukan query mentah tiap render.
        $logs = $this->rememberOwnerCache(['activity_log'], 'activity_log:recent', 120, fn () =>
            ActivityLog::where('id_owners', $this->owner->id)->latest('id')->limit(15)->get()
        );

        return view('livewire.owner.pengaturan-akun', ['logs' => $logs]);
    }
}
