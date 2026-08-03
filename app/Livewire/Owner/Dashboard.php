<?php

namespace App\Livewire\Owner;

use Livewire\Component;
use App\Livewire\Owner\Concerns\RequiresOwnerAuth;
use App\Models\User;
use App\Models\Tanaman;
use App\Models\Tahapan;
use App\Models\Pembeli;
use App\Models\Panen;
use App\Models\Keuangan;
use App\Models\ActivityLog;

class Dashboard extends Component
{
    use RequiresOwnerAuth;

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
        $totalUser = User::where('id_owners', $this->owner->id)->count();
        $totalTanamanAktif = Tanaman::where('id_owners', $this->owner->id)->whereNull('siklus_selesai_at')->count();
        $totalPembeli = Pembeli::where('id_owners', $this->owner->id)->count();

        $panens = Panen::whereHas('tanaman', fn ($q) => $q->where('id_owners', $this->owner->id))->get();
        $totalBelumDibayar = (float) $panens->filter(fn ($p) => $p->harga_per_kg !== null)->sum(fn ($p) => $p->sisa_hutang);

        $panensBulanIni = $panens->filter(fn ($p) => $p->tanggal->isSameMonth(now()));
        $totalBeratPanenBulanIni = (float) $panensBulanIni->sum(fn ($p) => (float) $p->berat_kg);
        $pendapatanPanenBulanIni = (float) $panensBulanIni->filter(fn ($p) => $p->harga_per_kg !== null)->sum(fn ($p) => $p->total_harga);

        $pemasukanUmumBulanIni = (float) Keuangan::where('id_owners', $this->owner->id)
            ->where('jenis', 'pemasukan')
            ->whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year)
            ->sum('jumlah');
        $pengeluaranUmumBulanIni = (float) Keuangan::where('id_owners', $this->owner->id)
            ->where('jenis', 'pengeluaran')
            ->whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year)
            ->sum('jumlah');
        $labaRugiBulanIni = ($pendapatanPanenBulanIni + $pemasukanUmumBulanIni) - $pengeluaranUmumBulanIni;

        $urutanTahap = ['semai', 'peremajaan', 'pendewasaan', 'panen'];
        $labelTahap = ['semai' => 'Semai', 'peremajaan' => 'Peremajaan', 'pendewasaan' => 'Pendewasaan', 'panen' => 'Panen'];
        $kematianPerTahapBulanIni = Tahapan::whereHas('tanaman', fn ($q) => $q->where('id_owners', $this->owner->id))
            ->whereNotNull('jumlah_lolos')
            ->whereMonth('tanggal_selesai_aktual', now()->month)->whereYear('tanggal_selesai_aktual', now()->year)
            ->get()
            ->groupBy('jenis')
            ->map(function ($rows, $jenis) use ($labelTahap) {
                $awal = (int) $rows->sum('jumlah_awal');
                $lolos = (int) $rows->sum('jumlah_lolos');
                return [
                    'label' => $labelTahap[$jenis] ?? ucfirst($jenis),
                    'awal' => $awal,
                    'lolos' => $lolos,
                    'persen_selamat' => $awal > 0 ? round($lolos / $awal * 100, 1) : null,
                ];
            })
            ->sortBy(fn ($row, $jenis) => array_search($jenis, $urutanTahap))
            ->values();

        $logs = ActivityLog::where('id_owners', $this->owner->id)
            ->latest('id')
            ->limit(15)
            ->get();

        return view('livewire.owner.dashboard', [
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
            'logs' => $logs,
        ]);
    }
}
