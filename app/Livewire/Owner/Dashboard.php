<?php

namespace App\Livewire\Owner;

use Livewire\Component;
use App\Livewire\Owner\Concerns\RequiresOwnerAuth;
use App\Livewire\Owner\Concerns\CachesOwnerData;
use App\Models\User;
use App\Models\Tanaman;
use App\Models\Tahapan;
use App\Models\Pembeli;
use App\Models\Panen;
use App\Models\Keuangan;
use App\Models\ActivityLog;

class Dashboard extends Component
{
    use RequiresOwnerAuth, CachesOwnerData;

    public function mount()
    {
        if ($redirect = $this->loadAuthenticatedOwner()) {
            return $redirect;
        }
        if ($redirect = $this->requireRole(['owner'])) {
            return $redirect;
        }
    }

    public function render()
    {
        $data = $this->rememberOwnerCache(
            ['tanaman', 'panen', 'keuangan', 'pembeli', 'users'],
            'dashboard:owner:bulan'.now()->format('Ym'),
            120,
            fn () => $this->hitungData()
        );

        $logs = $this->rememberOwnerCache(['activity_log'], 'activity_log:recent', 120, fn () =>
            ActivityLog::where('id_owners', $this->owner->id)->latest('id')->limit(15)->get()
        );

        return view('livewire.owner.dashboard', $data + ['logs' => $logs]);
    }

    private function hitungData(): array
    {
        $totalUser = User::where('id_owners', $this->owner->id)->count();
        $totalTanamanAktif = Tanaman::where('id_owners', $this->owner->id)->whereNull('siklus_selesai_at')->count();
        $totalPembeli = Pembeli::where('id_owners', $this->owner->id)->count();

        // Semua total di bawah dijumlahkan oleh MySQL. Versi sebelumnya menarik SELURUH
        // baris panen milik owner ke memori PHP hanya untuk di-sum() - biaya RAM dan
        // transfernya naik terus tiap ada transaksi baru, padahal hasilnya cuma 3 angka.
        $rekapSemua = Panen::rekap(Panen::query()->milikOwner($this->owner->id));
        $totalBelumDibayar = $rekapSemua->total_sisa_hutang;

        $rekapBulanIni = Panen::rekap(
            Panen::query()->milikOwner($this->owner->id)
                ->whereBetween('tanggal', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
        );
        $totalBeratPanenBulanIni = $rekapBulanIni->total_berat;
        $pendapatanPanenBulanIni = $rekapBulanIni->total_harga;

        // whereBetween dipakai menggantikan whereMonth+whereYear: memfilter kolom apa
        // adanya membuat indeks (id_owners, tanggal) terpakai, sedangkan MONTH(tanggal)
        // memaksa MySQL menghitung fungsi tiap baris sehingga indeksnya diabaikan.
        $awalBulan = now()->startOfMonth()->toDateString();
        $akhirBulan = now()->endOfMonth()->toDateString();

        $keuanganBulanIni = Keuangan::where('id_owners', $this->owner->id)
            ->whereBetween('tanggal', [$awalBulan, $akhirBulan])
            ->toBase()
            ->selectRaw("
                COALESCE(SUM(CASE WHEN jenis = 'pemasukan' THEN jumlah ELSE 0 END), 0) as pemasukan,
                COALESCE(SUM(CASE WHEN jenis = 'pengeluaran' THEN jumlah ELSE 0 END), 0) as pengeluaran
            ")
            ->first();

        $pemasukanUmumBulanIni = (float) ($keuanganBulanIni->pemasukan ?? 0);
        $pengeluaranUmumBulanIni = (float) ($keuanganBulanIni->pengeluaran ?? 0);
        $labaRugiBulanIni = ($pendapatanPanenBulanIni + $pemasukanUmumBulanIni) - $pengeluaranUmumBulanIni;

        $urutanTahap = ['semai', 'peremajaan', 'pendewasaan', 'panen'];
        $labelTahap = ['semai' => 'Semai', 'peremajaan' => 'Peremajaan', 'pendewasaan' => 'Pendewasaan', 'panen' => 'Panen'];
        // GROUP BY dikerjakan MySQL - yang kembali ke PHP cuma 1 baris per jenis tahap
        // (maksimal 4), bukan seluruh baris tahapan yang selesai bulan ini.
        $kematianPerTahapBulanIni = Tahapan::whereHas('tanaman', fn ($q) => $q->where('id_owners', $this->owner->id))
            ->whereNotNull('jumlah_lolos')
            ->whereBetween('tanggal_selesai_aktual', [$awalBulan, $akhirBulan])
            ->toBase()
            ->selectRaw('jenis, COALESCE(SUM(jumlah_awal), 0) as awal, COALESCE(SUM(jumlah_lolos), 0) as lolos')
            ->groupBy('jenis')
            ->get()
            ->map(function ($row) use ($labelTahap) {
                $awal = (int) $row->awal;
                $lolos = (int) $row->lolos;
                return [
                    'jenis' => $row->jenis,
                    'label' => $labelTahap[$row->jenis] ?? ucfirst($row->jenis),
                    'awal' => $awal,
                    'lolos' => $lolos,
                    'persen_selamat' => $awal > 0 ? round($lolos / $awal * 100, 1) : null,
                ];
            })
            ->sortBy(fn ($row) => array_search($row['jenis'], $urutanTahap))
            ->values();

        return [
            'totalUser' => $totalUser,
            'totalTanamanAktif' => $totalTanamanAktif,
            'totalPembeli' => $totalPembeli,
            'totalBelumDibayar' => $totalBelumDibayar,
            'totalBeratPanenBulanIni' => $totalBeratPanenBulanIni,
            'pendapatanPanenBulanIni' => $pendapatanPanenBulanIni,
            'pemasukanUmumBulanIni' => $pemasukanUmumBulanIni,
            'pengeluaranUmumBulanIni' => $pengeluaranUmumBulanIni,
            'labaRugiBulanIni' => $labaRugiBulanIni,
            'kematianPerTahapBulanIni' => $kematianPerTahapBulanIni,
        ];
    }
}
