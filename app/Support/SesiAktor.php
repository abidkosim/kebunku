<?php

namespace App\Support;

use App\Models\Owner;
use App\Models\User;

/**
 * Satu-satunya tempat "siapa yang sedang login" diselesaikan dalam satu request.
 *
 * Sebelumnya identitas yang sama diambil ulang berkali-kali dalam SATU page load:
 * sekali oleh partials/favicon.blade.php, sekali oleh komponen halamannya, dan
 * sekali lagi oleh tiap SaranMasukanModal di shell - semuanya Owner::find() dengan
 * id yang persis sama. Kelas ini menyelesaikannya sekali lalu mengingat hasilnya,
 * jadi query berulang itu hilang tanpa mengubah alur login sama sekali.
 *
 * Didaftarkan sebagai scoped binding (lihat AppServiceProvider) - artinya satu
 * instance per request, dan direset otomatis di request berikutnya.
 */
class SesiAktor
{
    private bool $sudahDiselesaikan = false;

    private ?Owner $owner = null;
    private ?string $tipe = null;      // 'owner' | 'teknisi' | 'keuangan'
    private ?int $id = null;
    private ?string $nama = null;
    private ?string $fotoUrl = null;

    /** Sesi menunjuk ke akun yang sudah tidak ada - pemanggil harus membuang sesinya. */
    private bool $sesiBasi = false;

    public function owner(): ?Owner
    {
        $this->selesaikan();

        return $this->owner;
    }

    public function tipe(): ?string
    {
        $this->selesaikan();

        return $this->tipe;
    }

    public function id(): ?int
    {
        $this->selesaikan();

        return $this->id;
    }

    public function nama(): ?string
    {
        $this->selesaikan();

        return $this->nama;
    }

    public function fotoUrl(): ?string
    {
        $this->selesaikan();

        return $this->fotoUrl;
    }

    public function terautentikasi(): bool
    {
        return $this->owner() !== null;
    }

    public function sesiBasi(): bool
    {
        $this->selesaikan();

        return $this->sesiBasi;
    }

    /**
     * Dipanggil setelah login/logout supaya hasil yang sudah diingat tidak dipakai lagi
     * di sisa request yang sama.
     */
    public function reset(): void
    {
        $this->sudahDiselesaikan = false;
        $this->owner = null;
        $this->tipe = null;
        $this->id = null;
        $this->nama = null;
        $this->fotoUrl = null;
        $this->sesiBasi = false;
    }

    private function selesaikan(): void
    {
        if ($this->sudahDiselesaikan) {
            return;
        }

        $this->sudahDiselesaikan = true;

        if (session()->has('owner_id') && $this->pasangOwner(Owner::find(session('owner_id')))) {
            return;
        }

        if (session()->has('user_id') && $this->pasangUser(User::find(session('user_id')))) {
            return;
        }

        // Belum ada sesi yang sah - coba auto-login lewat cookie "ingat saya".
        if ($owner = RememberMe::cariDariCookie('remember_owner', Owner::class)) {
            if ($this->pasangOwner($owner)) {
                session(['owner_id' => $owner->id, 'owner_nama' => $owner->nama]);

                return;
            }
        }

        if ($user = RememberMe::cariDariCookie('remember_user', User::class)) {
            if ($this->pasangUser($user)) {
                session(['user_id' => $user->id, 'user_nama' => $user->nama, 'user_role' => $user->role]);

                return;
            }
        }
    }

    private function pasangOwner(?Owner $owner): bool
    {
        if (!$owner) {
            $this->sesiBasi = true;

            return false;
        }

        $this->owner = $owner;
        $this->tipe = 'owner';
        $this->id = $owner->id;
        $this->nama = $owner->nama;
        $this->fotoUrl = $owner->foto_url;

        return true;
    }

    private function pasangUser(?User $user): bool
    {
        if (!$user) {
            $this->sesiBasi = true;

            return false;
        }

        $owner = Owner::find($user->id_owners);

        if (!$owner) {
            $this->sesiBasi = true;

            return false;
        }

        $this->owner = $owner;
        $this->tipe = $user->role; // 'teknisi' | 'keuangan'
        $this->id = $user->id;
        $this->nama = $user->nama;
        $this->fotoUrl = $user->foto_url;

        return true;
    }
}
