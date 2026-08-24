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
use App\Models\User;

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
 *
 * Filter (search, per-Teknisi, per-Kebun, periode) + rekap per karyawan ditambahkan
 * supaya rekap ini tetap terkelola begitu jumlah Teknisi & kunjungan bertambah banyak -
 * tanpa itu daftar cuma jadi timeline panjang yang tidak praktis dipantau satu-satu.
 */
class KelolaAbsensi extends Component
{
    use RequiresOwnerAuth, WithFileUploads, WithPagination, CachesOwnerData;

    public $perPage = 10;

    public $search = '';
    public $filterTeknisiId = '';
    public $filterKebunId = '';
    public $dariTanggal;
    public $sampaiTanggal;

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

        $this->dariTanggal = now()->startOfMonth()->toDateString();
        $this->sampaiTanggal = now()->endOfMonth()->toDateString();
    }

    public function setPeriode(string $preset)
    {
        [$this->dariTanggal, $this->sampaiTanggal] = match ($preset) {
            'bulan-ini' => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
            'tahun-ini' => [now()->startOfYear()->toDateString(), now()->endOfYear()->toDateString()],
            'semua' => [null, null],
            default => [$this->dariTanggal, $this->sampaiTanggal],
        };
        $this->resetPage();
    }

    public function filterKeTeknisi($teknisiId): void
    {
        // Diklik dari kartu rekap per karyawan - klik lagi pada karyawan yang sama
        // untuk membatalkan filter (toggle), bukan cuma satu arah.
        $this->filterTeknisiId = ((string) $this->filterTeknisiId === (string) $teknisiId) ? '' : $teknisiId;
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function updatedFilterTeknisiId()
    {
        $this->resetPage();
    }

    public function updatedFilterKebunId()
    {
        $this->resetPage();
    }

    public function updatedDariTanggal()
    {
        $this->resetPage();
    }

    public function updatedSampaiTanggal()
    {
        $this->resetPage();
    }

    public function resetFilter(): void
    {
        $this->reset(['search', 'filterTeknisiId', 'filterKebunId']);
        $this->setPeriode('bulan-ini');
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

    private function absensiQuery()
    {
        return Absensi::where('id_owners', $this->owner->id)
            ->when($this->search, fn ($q) => $q->where('kegiatan', 'like', '%'.$this->search.'%'))
            ->when($this->filterTeknisiId, fn ($q) => $q->where('actor_id', $this->filterTeknisiId))
            ->when($this->filterKebunId, fn ($q) => $q->where('id_kebun', $this->filterKebunId))
            ->when($this->dariTanggal, fn ($q) => $q->whereDate('created_at', '>=', $this->dariTanggal))
            ->when($this->sampaiTanggal, fn ($q) => $q->whereDate('created_at', '<=', $this->sampaiTanggal));
    }

    public function render()
    {
        $cacheKey = 'absensi:list:page'.$this->getPage().':per'.$this->perPage
            .':f'.md5($this->search.'|'.$this->filterTeknisiId.'|'.$this->filterKebunId.'|'.$this->dariTanggal.'|'.$this->sampaiTanggal);

        $list = $this->rememberOwnerCache(['absensi'], $cacheKey, 300, fn () =>
            $this->absensiQuery()
                ->with('kebun:id,nama_kebun')
                ->latest('id')
                ->paginate($this->perPage)
        );

        // Rekap jumlah kunjungan per Teknisi UNTUK PERIODE TERPILIH (tanggal saja -
        // sengaja tidak ikut filter Teknisi/Kebun/pencarian, supaya kartu ini selalu
        // jadi gambaran menyeluruh buat membandingkan semua karyawan sekaligus, bukan
        // ikut menyempit begitu salah satu karyawan sedang difilter). Klik satu kartu
        // untuk mempersempit daftar di bawah ke orang itu (lihat filterKeTeknisi()).
        $periodeSig = md5($this->dariTanggal.'|'.$this->sampaiTanggal);
        // ->map(fn ($row) => [...]) di sini SENGAJA mengubah hasil query mentah (stdClass
        // dari toBase()->selectRaw()) jadi array PHP biasa SEBELUM masuk cache. Tanpa ini,
        // Collection<stdClass> yang di-cache gagal di-unserialize saat dibaca ulang dari
        // Redis - stdClass tidak ada di whitelist config/cache.php's serializable_classes
        // (persis gotcha yang sama dengan item 53h, ditemukan lewat verifikasi browser
        // sungguhan). Menambahkan stdClass ke whitelist itu sendiri dihindari - itu class
        // generik yang bisa dipakai object manapun, jadi risikonya lebih luas daripada
        // mendaftarkan model spesifik. Pola array-sebelum-cache ini sama seperti yang
        // sudah dipakai di Laporan::hitungRekap() untuk rekapKebun/rekapKeuangan.
        $rekapTeknisi = $this->rememberOwnerCache(['absensi'], "absensi:rekap-teknisi:p{$periodeSig}", 300, fn () =>
            collect(
                Absensi::where('id_owners', $this->owner->id)
                    ->when($this->dariTanggal, fn ($q) => $q->whereDate('created_at', '>=', $this->dariTanggal))
                    ->when($this->sampaiTanggal, fn ($q) => $q->whereDate('created_at', '<=', $this->sampaiTanggal))
                    ->toBase()
                    ->selectRaw('actor_id, actor_nama, COUNT(*) as jumlah, MAX(created_at) as terakhir')
                    ->groupBy('actor_id', 'actor_nama')
                    ->orderByDesc('jumlah')
                    ->get()
            )->map(fn ($row) => [
                'actor_id' => (int) $row->actor_id,
                'actor_nama' => $row->actor_nama,
                'jumlah' => (int) $row->jumlah,
                'terakhir' => $row->terakhir,
            ])
        );

        $teknisiList = $this->rememberOwnerCache(['users'], 'absensi:teknisi-dropdown', 300, fn () =>
            User::where('id_owners', $this->owner->id)->where('role', 'teknisi')->orderBy('nama')->get(['id', 'nama'])
        );

        $kebunList = $this->rememberOwnerCache(['kebun'], 'kebun:dropdown', 300, fn () =>
            Kebun::where('id_owners', $this->owner->id)->orderBy('nama_kebun')->get(['id', 'nama_kebun'])
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
            'rekapTeknisi' => $rekapTeknisi,
            'teknisiList' => $teknisiList,
            'kebunList' => $kebunList,
            'kebunKoordinat' => $kebunKoordinat,
            'logs' => $logs,
        ]);
    }
}
