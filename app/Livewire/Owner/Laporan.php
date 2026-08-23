<?php

namespace App\Livewire\Owner;

use Livewire\Component;
use App\Livewire\Owner\Concerns\RequiresOwnerAuth;
use App\Livewire\Owner\Concerns\CachesOwnerData;
use App\Models\Tanaman;
use App\Models\Tahapan;
use App\Models\Panen;
use App\Models\Pembeli;
use App\Models\Kebun;
use App\Models\Keuangan;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;

class Laporan extends Component
{
    use RequiresOwnerAuth, CachesOwnerData;

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
            Panen::query()->milikOwner($this->owner->id),
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
        $periodeSig = md5($this->dariTanggal.'|'.$this->sampaiTanggal);
        $rekap = $this->rememberOwnerCache(
            ['panen', 'tanaman', 'keuangan', 'pembeli', 'kebun'],
            "laporan:rekap:p{$periodeSig}",
            180,
            fn () => $this->hitungRekap()
        );

        $logs = $this->rememberOwnerCache(['activity_log'], 'activity_log:recent', 120, fn () =>
            ActivityLog::where('id_owners', $this->owner->id)->latest('id')->limit(15)->get()
        );

        return view('livewire.owner.laporan', $rekap + ['logs' => $logs]);
    }

    private function hitungRekap(): array
    {
        // Satu query agregat menggantikan "tarik semua baris panen lalu sum() di PHP".
        $rekapPanen = Panen::rekap($this->panenQuery());
        $totalBerat = $rekapPanen->total_berat;
        $totalPendapatanPanen = $rekapPanen->total_harga;
        $totalBelumDibayar = $rekapPanen->total_sisa_hutang;
        $totalSelesaiDipanen = $this->periode(
            Tanaman::where('id_owners', $this->owner->id)->whereNotNull('siklus_selesai_at'),
            'siklus_selesai_at'
        )->count();

        // Dua SUM digabung jadi satu query (sebelumnya dua kali round-trip ke database).
        $totalKeuangan = $this->keuanganQuery()->toBase()
            ->selectRaw("
                COALESCE(SUM(CASE WHEN jenis = 'pemasukan' THEN jumlah ELSE 0 END), 0) as pemasukan,
                COALESCE(SUM(CASE WHEN jenis = 'pengeluaran' THEN jumlah ELSE 0 END), 0) as pengeluaran
            ")
            ->first();

        $totalPemasukanUmum = (float) ($totalKeuangan->pemasukan ?? 0);
        $totalPengeluaranUmum = (float) ($totalKeuangan->pengeluaran ?? 0);
        $labaRugiBersih = ($totalPendapatanPanen + $totalPemasukanUmum) - $totalPengeluaranUmum;

        $urutanTahap = ['semai', 'peremajaan', 'pendewasaan', 'panen'];
        $labelTahap = ['semai' => 'Semai', 'peremajaan' => 'Peremajaan', 'pendewasaan' => 'Pendewasaan', 'panen' => 'Panen'];
        $kematianPerTahap = $this->tahapanSelesaiQuery()
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
                    'mati' => $awal - $lolos,
                    'persen_selamat' => $awal > 0 ? round($lolos / $awal * 100, 1) : null,
                ];
            })
            ->sortBy(fn ($row) => array_search($row['jenis'], $urutanTahap))
            ->values();

        // Dulu: memuat SELURUH pohon kebun -> meja -> tanaman -> panens ke memori PHP,
        // lalu flatMap+sum. Sekarang satu query JOIN + GROUP BY; yang kembali ke PHP
        // hanya satu baris per kebun. Filter periode diletakkan di klausa ON (bukan
        // WHERE) supaya kebun yang tidak punya panen di periode itu tetap muncul dengan
        // nilai 0, persis seperti perilaku versi lama.
        $rekapKebun = collect(
            DB::table('kebun')
                ->leftJoin('meja', 'meja.kebun_id', '=', 'kebun.id')
                ->leftJoin('tanaman', 'tanaman.meja_id', '=', 'meja.id')
                ->leftJoin('panens', function ($join) {
                    $join->on('panens.tanaman_id', '=', 'tanaman.id');
                    if ($this->dariTanggal) {
                        $join->whereDate('panens.tanggal', '>=', $this->dariTanggal);
                    }
                    if ($this->sampaiTanggal) {
                        $join->whereDate('panens.tanggal', '<=', $this->sampaiTanggal);
                    }
                })
                ->where('kebun.id_owners', $this->owner->id)
                ->groupBy('kebun.id', 'kebun.nama_kebun')
                ->orderBy('kebun.nama_kebun')
                ->selectRaw('
                    kebun.nama_kebun,
                    COUNT(DISTINCT tanaman.id) as jumlah_tanaman,
                    COALESCE(SUM(panens.berat_kg), 0) as total_berat,
                    COALESCE(SUM(CASE WHEN panens.harga_per_kg IS NULL THEN 0 ELSE ROUND(panens.berat_kg * panens.harga_per_kg, 2) END), 0) as total_pendapatan
                ')
                ->get()
        )->map(fn ($row) => [
            'nama_kebun' => $row->nama_kebun,
            'jumlah_tanaman' => (int) $row->jumlah_tanaman,
            'total_berat' => (float) $row->total_berat,
            'total_pendapatan' => (float) $row->total_pendapatan,
        ]);

        // Saldo berjalan per pembeli (kg, status, hutang sampai sekarang), sengaja tidak difilter periode.
        $rekapPembeli = Pembeli::where('id_owners', $this->owner->id)
            ->denganRekap()
            // whereHas (bukan having panens_count) - HAVING tanpa GROUP BY ditolak
            // sebagian driver, sedangkan EXISTS di sini portabel dan bisa memakai
            // indeks panens.pembeli_id.
            ->whereHas('panens')
            ->orderByDesc('total_hutang')
            ->get()
            ->map(fn ($p) => [
                'nama' => $p->nama,
                'total_kg' => $p->total_kg,
                'total_transaksi' => $p->total_transaksi,
                'total_dibayar' => $p->total_dibayar,
                'total_hutang' => $p->total_hutang,
                'status' => $p->status_hutang,
            ])
            ->values();

        $rekapKeuangan = collect(
            $this->keuanganQuery()->toBase()
                ->selectRaw('jenis, kategori, COALESCE(SUM(jumlah), 0) as total')
                ->groupBy('jenis', 'kategori')
                ->orderByDesc('total')
                ->get()
        )->map(fn ($row) => [
            'jenis' => $row->jenis,
            'kategori' => $row->kategori,
            'total' => (float) $row->total,
        ])->values();

        return [
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
            'rekapPembeli' => $rekapPembeli,
        ];
    }
}
