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
            // Rentang tanggal eksplisit (bukan whereMonth/whereYear) supaya indeks
            // (id_owners, tanggal) benar-benar terpakai, dan semua penjumlahan
            // dikerjakan MySQL - bukan dengan menarik seluruh baris ke memori PHP.
            $awalBulan = now()->startOfMonth()->toDateString();
            $akhirBulan = now()->endOfMonth()->toDateString();

            $keuanganQuery = fn () => Keuangan::where('id_owners', $this->owner->id)
                ->whereBetween('tanggal', [$awalBulan, $akhirBulan]);

            $totalKeuangan = $keuanganQuery()->toBase()
                ->selectRaw("
                    COALESCE(SUM(CASE WHEN jenis = 'pemasukan' THEN jumlah ELSE 0 END), 0) as pemasukan,
                    COALESCE(SUM(CASE WHEN jenis = 'pengeluaran' THEN jumlah ELSE 0 END), 0) as pengeluaran
                ")
                ->first();

            $pemasukanUmum = (float) ($totalKeuangan->pemasukan ?? 0);
            $pengeluaranUmum = (float) ($totalKeuangan->pengeluaran ?? 0);

            $rekapSemua = Panen::rekap(Panen::query()->milikOwner($this->owner->id));
            $rekapBulanIni = Panen::rekap(
                Panen::query()->milikOwner($this->owner->id)
                    ->whereBetween('tanggal', [$awalBulan, $akhirBulan])
            );

            $pendapatanPanenBulanIni = $rekapBulanIni->total_harga;

            $data['pemasukanUmumBulanIni'] = $pemasukanUmum;
            $data['pengeluaranUmumBulanIni'] = $pengeluaranUmum;
            $data['pendapatanPanenBulanIni'] = $pendapatanPanenBulanIni;
            $data['labaRugiBulanIni'] = ($pendapatanPanenBulanIni + $pemasukanUmum) - $pengeluaranUmum;
            $data['totalBelumDibayar'] = $rekapSemua->total_sisa_hutang;
            $data['menungguHarga'] = $rekapSemua->jumlah_menunggu_harga;
            $data['rekapKategoriBulanIni'] = $keuanganQuery()->toBase()
                ->selectRaw('jenis, kategori, COALESCE(SUM(jumlah), 0) as total')
                ->groupBy('jenis', 'kategori')
                ->orderByDesc('total')
                ->limit(4)
                ->get()
                ->map(fn ($row) => ['jenis' => $row->jenis, 'kategori' => $row->kategori, 'total' => (float) $row->total]);
        }

        return $data;
    }
}
