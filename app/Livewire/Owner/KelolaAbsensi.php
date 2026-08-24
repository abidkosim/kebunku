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
use Illuminate\Support\Carbon;

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
 *
 * Kalender Kunjungan (Owner saja, item 59) menampilkan rekap yang sama dalam bentuk
 * grid satu bulan (siapa absen di tanggal berapa) - lebih cepat dibaca sekilas
 * dibanding menggulir tabel daftar untuk melihat pola kehadiran sebulan. Menghormati
 * filter Teknisi/Kebun/pencarian yang sama, tapi punya navigasi bulan sendiri
 * (kalenderBulan/kalenderTahun), terpisah dari dariTanggal/sampaiTanggal di atas.
 *
 * VISIBILITAS ANTAR-TEKNISI DIBATASI: seorang Teknisi HANYA melihat kunjungannya
 * sendiri - tidak bisa melihat siapa/kapan/kemana Teknisi lain absen. Owner tetap
 * melihat & memfilter semua karyawan (itu tujuan halaman manajemen ini). Pembatasan
 * ini DITEGAKKAN DI QUERY (absensiQuery() & rekap di render()), bukan cuma
 * menyembunyikan dropdown filter di Blade - dropdown "Filter Karyawan" memang tidak
 * dirender untuk Teknisi, tapi kalau filterTeknisiId dipaksa lewat request Livewire
 * yang dipalsukan, query tetap mengabaikannya dan memaksa scope ke actorId sendiri.
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

    // Bulan yang sedang ditampilkan di Kalender Kunjungan (Owner saja) - SENGAJA
    // terpisah dari dariTanggal/sampaiTanggal di atas (yang punya preset "Tahun
    // Ini"/"Semua"), karena kalender selalu menampilkan TEPAT satu bulan lewat
    // navigasi sebelumnya/berikutnya sendiri, bukan mengikuti rentang tanggal bebas.
    public $kalenderBulan;
    public $kalenderTahun;

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
        $this->kalenderBulan = now()->month;
        $this->kalenderTahun = now()->year;
    }

    public function kalenderSebelumnya(): void
    {
        $geser = Carbon::createFromDate($this->kalenderTahun, $this->kalenderBulan, 1)->subMonthNoOverflow();
        $this->kalenderBulan = $geser->month;
        $this->kalenderTahun = $geser->year;
    }

    public function kalenderBerikutnya(): void
    {
        $geser = Carbon::createFromDate($this->kalenderTahun, $this->kalenderBulan, 1)->addMonthNoOverflow();
        $this->kalenderBulan = $geser->month;
        $this->kalenderTahun = $geser->year;
    }

    public function kalenderBulanIni(): void
    {
        $this->kalenderBulan = now()->month;
        $this->kalenderTahun = now()->year;
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
        // Cuma Owner yang boleh memfilter ke karyawan tertentu - Teknisi tidak pernah
        // melihat kartu rekap karyawan lain untuk diklik (lihat kelola-absensi.blade.php),
        // tapi dijaga juga di sini kalau method ini dipanggil paksa lewat request palsu.
        if ($this->actorType !== 'owner') {
            return;
        }

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
        $this->kalenderBulanIni();
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

    /**
     * Teknisi: paksa scope ke dirinya sendiri, ABAIKAN nilai filterTeknisiId apa pun
     * (dropdown-nya memang tidak dirender untuk Teknisi, tapi ini yang jadi pertahanan
     * sebenarnya kalau propertinya dipaksa lewat request Livewire palsu). Owner: filter
     * bebas seperti biasa (termasuk "Semua Karyawan" = tidak difilter). Dipakai bersama
     * oleh absensiQuery() (daftar+pagination) dan query Kalender Kunjungan di bawah,
     * supaya keduanya konsisten menghormati filter yang sama.
     */
    private function filterAktorEfektif()
    {
        return $this->actorType === 'teknisi' ? $this->actorId : $this->filterTeknisiId;
    }

    private function absensiQuery()
    {
        $filterAktorId = $this->filterAktorEfektif();

        return Absensi::where('id_owners', $this->owner->id)
            ->when($this->search, fn ($q) => $q->where('kegiatan', 'like', '%'.$this->search.'%'))
            ->when($filterAktorId, fn ($q) => $q->where('actor_id', $filterAktorId))
            ->when($this->filterKebunId, fn ($q) => $q->where('id_kebun', $this->filterKebunId))
            ->when($this->dariTanggal, fn ($q) => $q->whereDate('created_at', '>=', $this->dariTanggal))
            ->when($this->sampaiTanggal, fn ($q) => $q->whereDate('created_at', '<=', $this->sampaiTanggal));
    }

    public function render()
    {
        // Penanda actor ditambahkan ke KEDUA cache key di bawah, KHUSUS untuk Teknisi.
        // KRUSIAL: tanpa ini, Teknisi A membuka halaman lebih dulu akan meng-cache hasil
        // query di key yang IDENTIK dengan yang dipakai Teknisi B (karena filterTeknisiId
        // selalu kosong untuk keduanya - dropdown-nya memang tidak dirender) - Teknisi B
        // kemudian akan disodori HASIL CACHE MILIK TEKNISI A dari Redis yang sama.
        // Ditemukan & diperbaiki saat menambahkan pembatasan visibilitas ini, bukan
        // sekadar teori - persis kelas bug yang sama dengan "kebocoran cache lintas
        // actor" yang harus selalu dicek tiap kali sebuah query jadi ter-scope per-user.
        $penandaAktor = $this->actorType === 'teknisi' ? ':aktor'.$this->actorId : '';

        $cacheKey = 'absensi:list:page'.$this->getPage().':per'.$this->perPage
            .':f'.md5($this->search.'|'.$this->filterTeknisiId.'|'.$this->filterKebunId.'|'.$this->dariTanggal.'|'.$this->sampaiTanggal)
            .$penandaAktor;

        $list = $this->rememberOwnerCache(['absensi'], $cacheKey, 300, fn () =>
            $this->absensiQuery()
                ->with('kebun:id,nama_kebun')
                ->latest('id')
                ->paginate($this->perPage)
        );

        // Rekap jumlah kunjungan per Teknisi UNTUK PERIODE TERPILIH (tanggal saja -
        // sengaja tidak ikut filter Kebun/pencarian, supaya kartu ini selalu jadi
        // gambaran menyeluruh buat Owner membandingkan semua karyawan sekaligus, bukan
        // ikut menyempit begitu salah satu karyawan sedang difilter). Klik satu kartu
        // untuk mempersempit daftar di bawah ke orang itu (lihat filterKeTeknisi()).
        // UNTUK TEKNISI: query ini JUGA di-scope ke dirinya sendiri (cuma akan
        // menghasilkan 1 baris) - Blade menyembunyikan section kartu rekap untuk
        // Teknisi sama sekali, tapi query-nya tetap dijaga di sini sebagai lapis kedua.
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
        $rekapTeknisi = $this->rememberOwnerCache(['absensi'], "absensi:rekap-teknisi:p{$periodeSig}{$penandaAktor}", 300, fn () =>
            collect(
                Absensi::where('id_owners', $this->owner->id)
                    ->when($this->actorType === 'teknisi', fn ($q) => $q->where('actor_id', $this->actorId))
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

        // --- Kalender Kunjungan (Owner saja - lihat kelola-absensi.blade.php) ---
        // Direkap PER HARI PER TEKNISI untuk satu bulan (kalenderBulan/kalenderTahun),
        // menghormati filter search/Teknisi/Kebun yang sama seperti daftar di bawahnya
        // (TIDAK ikut dariTanggal/sampaiTanggal - kalender punya navigasi bulan sendiri).
        // filterAktorEfektif() dipakai LANGSUNG di kunci cache (bukan lewat $penandaAktor
        // seperti list/rekap di atas) - untuk Teknisi nilainya sudah otomatis jadi
        // actorId sendiri, jadi cache key ini sudah unik per-Teknisi tanpa perlu
        // penanda tambahan (menghindari kelas bug kebocoran cache yang sama seperti
        // item 58: pastikan setiap query yang ter-scope per-actor, cache key-nya juga
        // ikut berbeda per-actor).
        $filterAktorEfektif = $this->filterAktorEfektif();
        $kalenderKey = 'absensi:kalender:'.$this->kalenderTahun.'-'.$this->kalenderBulan
            .':f'.md5($this->search.'|'.$filterAktorEfektif.'|'.$this->filterKebunId);

        // ->map(...) ke array biasa SEBELUM cache - alasan sama seperti $rekapTeknisi
        // di atas (stdClass dari toBase()->selectRaw() gagal di-unserialize dari Redis).
        $kalenderPerHari = $this->rememberOwnerCache(['absensi'], $kalenderKey, 300, fn () =>
            collect(
                Absensi::where('id_owners', $this->owner->id)
                    ->whereYear('created_at', $this->kalenderTahun)
                    ->whereMonth('created_at', $this->kalenderBulan)
                    ->when($this->search, fn ($q) => $q->where('kegiatan', 'like', '%'.$this->search.'%'))
                    ->when($filterAktorEfektif, fn ($q) => $q->where('actor_id', $filterAktorEfektif))
                    ->when($this->filterKebunId, fn ($q) => $q->where('id_kebun', $this->filterKebunId))
                    ->toBase()
                    ->selectRaw('DATE(created_at) as tgl, actor_id, actor_nama, COUNT(*) as jumlah')
                    ->groupBy('tgl', 'actor_id', 'actor_nama')
                    ->orderBy('actor_nama')
                    ->get()
            )->map(fn ($row) => [
                'tgl' => (string) $row->tgl,
                'actor_id' => (int) $row->actor_id,
                'actor_nama' => $row->actor_nama,
                'jumlah' => (int) $row->jumlah,
            ])->groupBy('tgl')
        );

        // Grid minggu dibangun FRESH tiap render (bukan ikut di-cache) - murni
        // aritmetika tanggal lokal tanpa query tambahan, jadi tidak ada alasan
        // menyimpan objek Carbon ke Redis (whitelist serializable_classes/item 53h -
        // lebih aman array/scalar polos saja yang masuk cache).
        $kalenderAwalBulan = Carbon::createFromDate($this->kalenderTahun, $this->kalenderBulan, 1)->startOfDay();
        $kursor = $kalenderAwalBulan->copy()->startOfWeek(Carbon::MONDAY);
        $gridAkhir = $kalenderAwalBulan->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $kalenderMinggu = [];
        while ($kursor->lte($gridAkhir)) {
            $minggu = [];
            for ($i = 0; $i < 7; $i++) {
                $kunciHari = $kursor->format('Y-m-d');
                $minggu[] = [
                    'tgl' => $kunciHari,
                    'tanggal' => $kursor->day,
                    'dalamBulan' => $kursor->month === (int) $this->kalenderBulan,
                    'hariIni' => $kursor->isToday(),
                    'entri' => $kalenderPerHari->get($kunciHari, collect()),
                ];
                $kursor->addDay();
            }
            $kalenderMinggu[] = $minggu;
        }

        $namaBulan = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
        $kalenderLabel = $namaBulan[$this->kalenderBulan].' '.$this->kalenderTahun;

        // Dropdown "Filter Karyawan" cuma dipakai Blade untuk Owner (tidak dirender
        // untuk Teknisi sama sekali) - daftar nama semua Teknisi ini sendiri bukan data
        // rahasia (nama rekan kerja, bukan riwayat kunjungan mereka), jadi aman dikirim
        // ke siapa pun tanpa perlu di-scope per-actor.
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
            'kalenderMinggu' => $kalenderMinggu,
            'kalenderLabel' => $kalenderLabel,
        ]);
    }
}
