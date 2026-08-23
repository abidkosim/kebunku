<?php

namespace App\Livewire\Owner\Concerns;

use App\Models\User;
use App\Support\RememberMe;
use App\Support\SesiAktor;
use Illuminate\Auth\Access\AuthorizationException;

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

    /**
     * Resolusi identitas dialihkan ke SesiAktor (scoped per request), jadi walau satu
     * halaman memuat beberapa komponen yang semuanya butuh identitas ini, query-nya
     * cuma jalan sekali. Perilaku yang dilihat pengguna tidak berubah sama sekali:
     * urutan pengecekan (session owner -> session user -> cookie ingat saya) persis
     * seperti sebelumnya.
     */
    protected function loadAuthenticatedOwner()
    {
        // pesan yang di-flash dari redirect sebelumnya (mis. requireAksesPenuh()) - tampilkan
        // sekali lewat toast yang sama dengan alert-success/alert-error yang sudah ada.
        if ($pesan = session()->pull('alert-error')) {
            $this->dispatch('alert-error', message: $pesan);
        }

        $aktor = app(SesiAktor::class);

        if (!$aktor->terautentikasi()) {
            if ($aktor->sesiBasi()) {
                // Sesi menunjuk akun yang sudah dihapus - bersihkan supaya tidak
                // terus-menerus dicoba di request berikutnya.
                session()->forget(['owner_id', 'owner_nama', 'user_id', 'user_nama', 'user_role']);
            }

            return redirect('/');
        }

        $this->owner = $aktor->owner();
        $this->actorType = $aktor->tipe();
        $this->actorId = $aktor->id();
        $this->actorNama = $aktor->nama();
        $this->actorFotoUrl = $aktor->fotoUrl();

        return null;
    }

    /**
     * mount() hanya jalan di page load PERTAMA. Tanpa pengecekan ulang di sini, sebuah
     * komponen yang sudah ter-render tetap bisa dipakai lewat request Livewire berikutnya
     * walaupun sesinya sudah berakhir atau sudah logout - karena identitasnya ikut
     * dibawa di payload komponen, bukan dibaca ulang dari sesi. hydrate() jalan di SETIAP
     * request Livewire, jadi di sinilah sesi dicocokkan lagi dengan pemilik komponen.
     */
    public function hydrate(): void
    {
        $aktor = app(SesiAktor::class);

        $masihSah = $aktor->terautentikasi()
            && $aktor->tipe() === $this->actorType
            && (int) $aktor->id() === (int) $this->actorId
            && (int) $aktor->owner()->id === (int) ($this->owner->id ?? 0);

        if ($masihSah) {
            return;
        }

        // Hentikan request di sini juga (bukan cuma menjadwalkan redirect) supaya method
        // yang diminta browser tidak sempat dijalankan atas nama sesi yang sudah mati.
        throw new AuthorizationException('Sesi sudah berakhir, silakan login ulang.');
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
        // ID sesi diganti supaya sesi lama benar-benar mati, bukan cuma dikosongkan isinya.
        session()->regenerate();
        app(SesiAktor::class)->reset();

        return $this->redirect('/', navigate: true);
    }
}
