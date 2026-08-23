<?php

namespace App\Livewire\Owner;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Livewire\Owner\Concerns\RequiresOwnerAuth;
use App\Livewire\Owner\Concerns\CachesOwnerData;
use App\Models\Absensi;
use App\Models\ActivityLog;
use App\Models\Kebun;

/**
 * Log kunjungan ke kebun (BUKAN absen jam-kerja masuk/pulang) - satu Teknisi datang
 * ke lokasi, foto dirinya di sana + lokasi GPS terdeteksi otomatis + jam saat itu
 * juga, opsional catatan kegiatan. Dipakai bersama oleh Owner (rekap lihat-saja,
 * lihat semua Teknisi) dan Teknisi (rekap yang sama + form catat kunjungan baru) -
 * pola sama seperti modul lain di app ini, shell-nya berbeda lewat requireRole().
 *
 * SENGAJA TIDAK ADA method update/delete: begitu tercatat, sebuah kunjungan tidak
 * bisa diubah/dihapus siapa pun (termasuk Owner) - supaya tetap bisa dipercaya
 * sebagai dokumentasi riil, bukan catatan yang bisa "dirapikan" belakangan.
 *
 * Kunjungan WAJIB berada dalam radius Kebun::RADIUS_ABSENSI_METER dari kebun terdekat
 * milik owner yang sudah punya koordinat - kalau owner belum mengisi koordinat kebun
 * manapun, fitur catat kunjungan terkunci total sampai itu diisi (lihat Kelola Kebun).
 */
class KelolaAbsensi extends Component
{
    use RequiresOwnerAuth, WithFileUploads, WithPagination, CachesOwnerData;

    public $perPage = 10;

    public $showModal = false;
    public $foto_upload;
    public $kegiatan_form;

    // Diisi lewat JS (Geolocation API) sebelum submit - lihat script di view.
    public $lokasiLat_form;
    public $lokasiLng_form;
    public $lokasiError_form;

    public function mount()
    {
        if ($redirect = $this->loadAuthenticatedOwner()) {
            return $redirect;
        }
        if ($redirect = $this->requireRole(['owner', 'teknisi'])) {
            return $redirect;
        }
    }

    public function openCatat()
    {
        if ($this->actorType !== 'teknisi') {
            return;
        }

        $this->reset(['foto_upload', 'kegiatan_form', 'lokasiLat_form', 'lokasiLng_form', 'lokasiError_form']);
        $this->resetErrorBag();
        $this->showModal = true;
    }

    /**
     * Nama method SENGAJA bukan "upload" - itu nama yang dipakai internal Livewire
     * JS ($wire.upload) dan menabrak wire:submit kalau dipakai (lihat catatan di
     * app\Livewire\Owner\KelolaGaleri, bug yang sama pernah kejadian di modul itu).
     *
     * Guard peran DITEGAKKAN DI SINI (bukan cuma disembunyikan lewat @if di Blade) -
     * tombol "Catat Kunjungan" memang tidak dirender untuk Owner, tapi itu saja tidak
     * cukup: method Livewire tetap bisa dipanggil lewat request yang dipalsukan kalau
     * tidak dicek ulang di server. Hanya Teknisi yang boleh mencatat kunjungan.
     *
     * Validasi radius kebun DITEGAKKAN DI SINI JUGA, bukan cuma di JS (lihat status
     * lokasi real-time di kelola-absensi.blade.php) - JS cuma memberi feedback instan
     * di layar, bisa saja dimatikan/dilewati lewat devtools, jadi jarak yang benar-benar
     * menentukan lolos-tidaknya kunjungan selalu dihitung ulang di server dari titik
     * GPS yang dikirim, terhadap data kebun yang tersimpan di database.
     */
    public function simpanAbsensi()
    {
        if ($this->actorType !== 'teknisi') {
            return;
        }

        $this->validate([
            'foto_upload' => 'required|image|max:8192',
            'lokasiLat_form' => 'required|numeric|between:-90,90',
            'lokasiLng_form' => 'required|numeric|between:-180,180',
            'kegiatan_form' => 'nullable|string|max:1000',
        ], [
            'lokasiLat_form.required' => 'Lokasi belum terdeteksi. Aktifkan izin lokasi lalu coba lagi.',
            'lokasiLng_form.required' => 'Lokasi belum terdeteksi. Aktifkan izin lokasi lalu coba lagi.',
        ]);

        $terdekat = Kebun::terdekatDenganKoordinat(
            $this->owner->id,
            (float) $this->lokasiLat_form,
            (float) $this->lokasiLng_form
        );

        if (!$terdekat) {
            $this->addError('lokasiLat_form', 'Owner belum mengatur titik lokasi kebun manapun. Minta Owner mengisi koordinat kebun dulu di menu Kelola Kebun & Meja, baru absen bisa dilakukan.');
            return;
        }

        if ($terdekat->jarak_meter > Kebun::RADIUS_ABSENSI_METER) {
            $jarak = (int) round($terdekat->jarak_meter);
            $this->addError('lokasiLat_form', "Anda berjarak {$jarak}m dari kebun terdekat ({$terdekat->kebun->nama_kebun}). Absen hanya bisa dilakukan dalam radius ".Kebun::RADIUS_ABSENSI_METER.'m.');
            return;
        }

        $path = $this->foto_upload->store('absensi', 'public');

        Absensi::create([
            'id_owners' => $this->owner->id,
            'id_kebun' => $terdekat->kebun->id,
            'actor_type' => $this->actorType,
            'actor_id' => $this->actorId,
            'actor_nama' => $this->actorNama,
            'foto' => $path,
            'lokasi_lat' => $this->lokasiLat_form,
            'lokasi_lng' => $this->lokasiLng_form,
            'kegiatan' => $this->kegiatan_form,
        ]);

        ActivityLog::catat(
            $this->actorType,
            $this->actorId,
            $this->actorNama,
            'tambah',
            'Absensi',
            "Mencatat kunjungan ke {$terdekat->kebun->nama_kebun}".($this->kegiatan_form ? ": {$this->kegiatan_form}" : ''),
            $this->owner->id
        );

        $this->forgetOwnerCache(['absensi', 'activity_log']);
        $this->showModal = false;
        $this->dispatch('alert-success', message: 'Kunjungan berhasil dicatat');
    }

    public function render()
    {
        $cacheKey = 'absensi:list:page'.$this->getPage();
        $list = $this->rememberOwnerCache(['absensi'], $cacheKey, 300, fn () =>
            Absensi::where('id_owners', $this->owner->id)
                ->with('kebun:id,nama_kebun')
                ->latest('id')
                ->paginate($this->perPage)
        );

        // Cuma kebun yang SUDAH punya koordinat - inilah yang dipakai form Teknisi
        // buat menghitung jarak ke kebun terdekat secara real-time (lihat script di
        // view). Kalau kosong, Blade menampilkan pesan "Owner belum atur kebun".
        $kebunKoordinat = $this->rememberOwnerCache(['kebun'], 'kebun:koordinat', 300, fn () =>
            Kebun::where('id_owners', $this->owner->id)
                ->whereNotNull('lat')->whereNotNull('lng')
                ->get(['id', 'nama_kebun', 'lat', 'lng'])
                ->map(fn ($k) => [
                    'id' => $k->id,
                    'nama' => $k->nama_kebun,
                    'lat' => (float) $k->lat,
                    'lng' => (float) $k->lng,
                ])
        );

        $logs = $this->rememberOwnerCache(['activity_log'], 'activity_log:recent', 120, fn () =>
            ActivityLog::where('id_owners', $this->owner->id)->latest('id')->limit(15)->get()
        );

        return view('livewire.owner.kelola-absensi', [
            'list' => $list,
            'kebunKoordinat' => $kebunKoordinat,
            'logs' => $logs,
        ]);
    }
}
