<?php

namespace App\Livewire\Staff;

use Livewire\Component;
use App\Livewire\Owner\Concerns\RequiresOwnerAuth;
use App\Livewire\Owner\Concerns\CachesOwnerData;
use App\Models\Tanaman;
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
        if ($redirect = $this->requireRole(['teknisi', 'keuangan'])) {
            return $redirect;
        }
    }

    public function render()
    {
        $data = $this->rememberOwnerCache(
            ['tanaman', 'panen', 'keuangan'],
            "dashboard:staff:{$this->actorType}:bulan".now()->format('Ym'),
            120,
            fn () => $this->hitungData()
        );

        $logs = $this->rememberOwnerCache(['activity_log'], 'activity_log:recent', 120, fn () =>
            ActivityLog::where('id_owners', $this->owner->id)->latest('id')->limit(15)->get()
        );

        return view('livewire.staff.dashboard', array_merge($data, ['logs' => $logs]));
    }

    private function hitungData(): array
    {
        $data = [];

        if ($this->actorType === 'teknisi') {
            $data['totalTanamanAktif'] = Tanaman::where('id_owners', $this->owner->id)->whereNull('siklus_selesai_at')->count();
            $data['siapPanen'] = Tanaman::where('id_owners', $this->owner->id)
                ->whereNull('siklus_selesai_at')
                ->whereHas('tahapans', fn ($q) => $q->where('jenis', 'pendewasaan')->where('status', 'selesai'))
                ->whereDoesntHave('tahapans', fn ($q) => $q->where('jenis', 'panen'))
                ->count();
        }

        if ($this->actorType === 'keuangan') {
            $keuanganBulanIni = Keuangan::where('id_owners', $this->owner->id)
                ->whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year)
                ->get();

            $pemasukanUmum = (float) $keuanganBulanIni->where('jenis', 'pemasukan')->sum('jumlah');
            $pengeluaranUmum = (float) $keuanganBulanIni->where('jenis', 'pengeluaran')->sum('jumlah');

            $panens = Panen::whereHas('tanaman', fn ($q) => $q->where('id_owners', $this->owner->id))->get();
            $panensBulanIni = $panens->filter(fn ($p) => $p->tanggal->isSameMonth(now()));

            $pendapatanPanenBulanIni = (float) $panensBulanIni->filter(fn ($p) => $p->harga_per_kg !== null)->sum(fn ($p) => $p->total_harga);

            $data['pemasukanUmumBulanIni'] = $pemasukanUmum;
            $data['pengeluaranUmumBulanIni'] = $pengeluaranUmum;
            $data['pendapatanPanenBulanIni'] = $pendapatanPanenBulanIni;
            $data['labaRugiBulanIni'] = ($pendapatanPanenBulanIni + $pemasukanUmum) - $pengeluaranUmum;
            $data['totalBelumDibayar'] = (float) $panens->filter(fn ($p) => $p->harga_per_kg !== null)->sum(fn ($p) => $p->sisa_hutang);
            $data['menungguHarga'] = $panens->filter(fn ($p) => $p->harga_per_kg === null)->count();
            $data['rekapKategoriBulanIni'] = $keuanganBulanIni
                ->groupBy('kategori')
                ->map(fn ($rows) => ['jenis' => $rows->first()->jenis, 'kategori' => $rows->first()->kategori, 'total' => (float) $rows->sum('jumlah')])
                ->sortByDesc('total')
                ->take(4)
                ->values();
        }

        return $data;
    }
}
