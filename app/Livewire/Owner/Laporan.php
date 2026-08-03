<?php

namespace App\Livewire\Owner;

use Livewire\Component;
use App\Livewire\Owner\Concerns\RequiresOwnerAuth;
use App\Models\Tanaman;
use App\Models\Tahapan;
use App\Models\Panen;
use App\Models\Pembeli;
use App\Models\Kebun;
use App\Models\Keuangan;
use App\Models\ActivityLog;

class Laporan extends Component
{
    use RequiresOwnerAuth;

    public $dariTanggal;
    public $sampaiTanggal;

    public function mount()
    {
        if ($redirect = $this->loadAuthenticatedOwner()) {
            return $redirect;
        }
        if ($redirect = $this->requireRole(['owner', 'keuangan'])) {
            return $redirect;
        }
    }

    public function setPeriode(string $preset)
    {
        [$this->dariTanggal, $this->sampaiTanggal] = match ($preset) {
            'bulan-ini' => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
            'tahun-ini' => [now()->startOfYear()->toDateString(), now()->endOfYear()->toDateString()],
            'semua' => [null, null],
            default => [$this->dariTanggal, $this->sampaiTanggal],
        };
    }

    private function periode($query, string $kolomTanggal)
    {
        return $query
            ->when($this->dariTanggal, fn ($q) => $q->whereDate($kolomTanggal, '>=', $this->dariTanggal))
            ->when($this->sampaiTanggal, fn ($q) => $q->whereDate($kolomTanggal, '<=', $this->sampaiTanggal));
    }

    private function panenQuery()
    {
        return $this->periode(
            Panen::whereHas('tanaman', fn ($q) => $q->where('id_owners', $this->owner->id)),
            'tanggal'
        );
    }

    private function tahapanSelesaiQuery()
    {
        return $this->periode(
            Tahapan::whereHas('tanaman', fn ($q) => $q->where('id_owners', $this->owner->id))->whereNotNull('jumlah_lolos'),
            'tanggal_selesai_aktual'
        );
    }

    private function keuanganQuery()
    {
        return $this->periode(Keuangan::where('id_owners', $this->owner->id), 'tanggal');
    }

    public function render()
    {
        $panens = $this->panenQuery()->get();
        $totalBerat = (float) $panens->sum(fn ($p) => (float) $p->berat_kg);
        $totalPendapatanPanen = (float) $panens->filter(fn ($p) => $p->harga_per_kg !== null)->sum(fn ($p) => $p->total_harga);
        $totalBelumDibayar = (float) $panens->filter(fn ($p) => $p->harga_per_kg !== null)->sum(fn ($p) => $p->sisa_hutang);
        $totalSelesaiDipanen = $this->periode(
            Tanaman::where('id_owners', $this->owner->id)->whereNotNull('siklus_selesai_at'),
            'siklus_selesai_at'
        )->count();

        $totalPemasukanUmum = (float) $this->keuanganQuery()->where('jenis', 'pemasukan')->sum('jumlah');
        $totalPengeluaranUmum = (float) $this->keuanganQuery()->where('jenis', 'pengeluaran')->sum('jumlah');
        $labaRugiBersih = ($totalPendapatanPanen + $totalPemasukanUmum) - $totalPengeluaranUmum;

        $urutanTahap = ['semai', 'peremajaan', 'pendewasaan', 'panen'];
        $labelTahap = ['semai' => 'Semai', 'peremajaan' => 'Peremajaan', 'pendewasaan' => 'Pendewasaan', 'panen' => 'Panen'];
        $kematianPerTahap = $this->tahapanSelesaiQuery()
            ->get()
            ->groupBy('jenis')
            ->map(function ($rows, $jenis) use ($labelTahap) {
                $awal = (int) $rows->sum('jumlah_awal');
                $lolos = (int) $rows->sum('jumlah_lolos');
                return [
                    'jenis' => $jenis,
                    'label' => $labelTahap[$jenis] ?? ucfirst($jenis),
                    'awal' => $awal,
                    'lolos' => $lolos,
                    'mati' => $awal - $lolos,
                    'persen_selamat' => $awal > 0 ? round($lolos / $awal * 100, 1) : null,
                ];
            })
            ->sortBy(fn ($row) => array_search($row['jenis'], $urutanTahap))
            ->values();

        $rekapKebun = Kebun::where('id_owners', $this->owner->id)
            ->with(['meja.tanaman' => fn ($q) => $q->with(['panens' => fn ($p) => $this->periode($p, 'tanggal')])])
            ->orderBy('nama_kebun')
            ->get()
            ->map(function ($kebun) {
                $tanamanList = $kebun->meja->flatMap->tanaman;
                $panensKebun = $tanamanList->flatMap->panens;
                return [
                    'nama_kebun' => $kebun->nama_kebun,
                    'jumlah_tanaman' => $tanamanList->count(),
                    'total_berat' => (float) $panensKebun->sum(fn ($p) => (float) $p->berat_kg),
                    'total_pendapatan' => (float) $panensKebun->filter(fn ($p) => $p->harga_per_kg !== null)->sum(fn ($p) => $p->total_harga),
                ];
            });

        // Hutang adalah saldo berjalan (belum dibayar sampai sekarang), sengaja tidak difilter periode.
        $pembeliHutangTerbesar = Pembeli::where('id_owners', $this->owner->id)
            ->with('panens')
            ->get()
            ->map(fn ($p) => ['nama' => $p->nama, 'total_hutang' => $p->total_hutang])
            ->filter(fn ($p) => $p['total_hutang'] > 0)
            ->sortByDesc('total_hutang')
            ->take(5)
            ->values();

        $rekapKeuangan = $this->keuanganQuery()
            ->get()
            ->groupBy(fn ($k) => $k->jenis.'|'.$k->kategori)
            ->map(fn ($rows) => [
                'jenis' => $rows->first()->jenis,
                'kategori' => $rows->first()->kategori,
                'total' => (float) $rows->sum('jumlah'),
            ])
            ->sortByDesc('total')
            ->values();

        $logs = ActivityLog::where('id_owners', $this->owner->id)
            ->latest('id')
            ->limit(15)
            ->get();

        return view('livewire.owner.laporan', [
            'totalBerat' => $totalBerat,
            'totalPendapatanPanen' => $totalPendapatanPanen,
            'totalBelumDibayar' => $totalBelumDibayar,
            'totalSelesaiDipanen' => $totalSelesaiDipanen,
            'totalPemasukanUmum' => $totalPemasukanUmum,
            'totalPengeluaranUmum' => $totalPengeluaranUmum,
            'labaRugiBersih' => $labaRugiBersih,
            'kematianPerTahap' => $kematianPerTahap,
            'rekapKebun' => $rekapKebun,
            'rekapKeuangan' => $rekapKeuangan,
            'pembeliHutangTerbesar' => $pembeliHutangTerbesar,
            'logs' => $logs,
        ]);
    }
}
