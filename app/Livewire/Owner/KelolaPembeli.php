<?php

namespace App\Livewire\Owner;

use Livewire\Component;
use Livewire\WithPagination;
use App\Livewire\Owner\Concerns\RequiresOwnerAuth;
use App\Livewire\Owner\Concerns\CachesOwnerData;
use App\Models\Pembeli;
use App\Models\Panen;
use App\Models\ActivityLog;

class KelolaPembeli extends Component
{
    use RequiresOwnerAuth, WithPagination, CachesOwnerData;

    public $search = '';
    public $perPage = 10;

    // Filter periode (berdasarkan tanggal PANEN, bukan tanggal pembeli dibuat - pembeli
    // sendiri tidak punya tanggal) + status hutang. Default KOSONG/"Semua" (BUKAN
    // "Bulan Ini" seperti modul Absensi) - hutang bukan sesuatu yang relevan cuma
    // sebulan, seorang pembeli yang berhutang dari 3 bulan lalu tetap harus kelihatan
    // di kartu ringkasan supaya Owner tidak salah kira semua sudah lunas.
    public $dariTanggal = null;
    public $sampaiTanggal = null;
    public $filterStatus = '';

    public $selectedPembeliId = null;

    public $showModal = false;
    public $isEditMode = false;
    public $editId = null;
    public $nama_form, $kontak_form;

    public function mount()
    {
        if ($redirect = $this->loadAuthenticatedOwner()) {
            return $redirect;
        }
        if ($redirect = $this->requireRole(['owner'])) {
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
        $this->resetPage();
    }

    public function updatedFilterStatus()
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
        $this->reset(['search', 'filterStatus']);
        $this->setPeriode('semua');
    }

    private function catat(string $aksi, string $keterangan): void
    {
        ActivityLog::catat($this->actorType, $this->actorId, $this->actorNama, $aksi, 'Pembeli', $keterangan, $this->owner->id);
    }

    private function pembeliQuery()
    {
        return Pembeli::where('id_owners', $this->owner->id);
    }

    /**
     * ID pembeli yang cocok dengan $filterStatus (dalam periode dariTanggal/sampaiTanggal
     * yang sama seperti daftar). status_hutang adalah accessor PHP di atas kolom hasil
     * SUB-QUERY (bukan aggregate langsung di query luar), jadi TIDAK BISA disaring pakai
     * HAVING - lihat catatan di Laporan::render(): "HAVING tanpa GROUP BY ditolak
     * sebagian driver". Solusinya: tarik id+agregat SAJA (tanpa pagination) lewat query
     * terpisah yang ringan, saring statusnya di PHP, baru dipakai sebagai whereIn() di
     * query utama yang di-paginate - jadi pagination tetap benar dan portabel lintas
     * driver (SQLite untuk test, MariaDB di produksi).
     */
    private function pembeliIdUntukStatus(): ?array
    {
        if (!$this->filterStatus) {
            return null;
        }

        return $this->pembeliQuery()
            ->denganRekap($this->dariTanggal, $this->sampaiTanggal)
            ->get()
            ->filter(fn ($p) => $p->status_hutang === $this->filterStatus)
            ->pluck('id')
            ->all();
    }

    public function viewDetail($id)
    {
        $this->pembeliQuery()->findOrFail($id);
        $this->selectedPembeliId = $id;
    }

    public function backToList()
    {
        $this->selectedPembeliId = null;
    }

    public function openCreate()
    {
        $this->reset(['nama_form', 'kontak_form', 'editId']);
        $this->isEditMode = false;
        $this->showModal = true;
    }

    public function openEdit($id)
    {
        $pembeli = $this->pembeliQuery()->findOrFail($id);
        $this->editId = $pembeli->id;
        $this->nama_form = $pembeli->nama;
        $this->kontak_form = $pembeli->kontak;
        $this->isEditMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'nama_form' => 'required',
            'kontak_form' => 'nullable',
        ]);

        if ($this->isEditMode) {
            $pembeli = $this->pembeliQuery()->findOrFail($this->editId);
            $pembeli->update(['nama' => $this->nama_form, 'kontak' => $this->kontak_form]);
            $this->catat('update', "Mengubah data pembeli '{$pembeli->nama}'");
        } else {
            $pembeli = Pembeli::create([
                'id_owners' => $this->owner->id,
                'nama' => $this->nama_form,
                'kontak' => $this->kontak_form,
            ]);
            $this->catat('tambah', "Menambahkan pembeli baru '{$pembeli->nama}'");
        }

        $this->forgetOwnerCache(['pembeli', 'activity_log']);
        $this->showModal = false;
        $this->dispatch('alert-success', message: 'Data pembeli disimpan');
    }

    public function delete($id)
    {
        $pembeli = $this->pembeliQuery()->findOrFail($id);

        if ($pembeli->panens()->exists()) {
            $this->dispatch('alert-error', message: 'Pembeli ini sudah punya riwayat transaksi panen, tidak bisa dihapus.');
            return;
        }

        $nama = $pembeli->nama;
        $pembeli->delete();
        $this->catat('hapus', "Menghapus pembeli '{$nama}'");
        $this->forgetOwnerCache(['pembeli', 'activity_log']);
        $this->dispatch('alert-success', message: 'Pembeli dihapus');
    }

    public function render()
    {
        $periodeSig = md5($this->dariTanggal.'|'.$this->sampaiTanggal);

        $cacheKey = 'pembeli:list:page'.$this->getPage().':per'.$this->perPage
            .':f'.md5($this->search.'|'.$this->filterStatus.'|'.$periodeSig);
        // denganRekap() menghitung total kg/transaksi/dibayar/hutang lewat sub-query
        // agregat. Sebelumnya baris ini pakai with('panens'), yang menarik SELURUH
        // riwayat panen setiap pembeli di halaman ini ke memori hanya untuk dijumlahkan.
        // pembeliIdUntukStatus() SENGAJA dipanggil DI DALAM closure ini (bukan di luar
        // sebelum cache lookup) - supaya query tambahannya cuma jalan saat cache MISS,
        // bukan tiap render meski hasil $list-nya sendiri sudah tersimpan di cache.
        $list = $this->rememberOwnerCache(['pembeli', 'panen'], $cacheKey, 300, function () {
            $filterIdStatus = $this->pembeliIdUntukStatus();

            return $this->pembeliQuery()
                ->denganRekap($this->dariTanggal, $this->sampaiTanggal)
                ->when($this->search, function ($q) {
                    $q->where('nama', 'like', '%'.$this->search.'%');
                })
                ->when($filterIdStatus !== null, fn ($q) => $q->whereIn('pembeli.id', $filterIdStatus))
                ->latest('pembeli.id')
                ->paginate($this->perPage);
        });

        // Kartu ringkasan di atas daftar (total pembeli, total kg, kg menunggu harga,
        // kg belum lunas, total hutang Rp) - SENGAJA cuma ikut filter periode, TIDAK
        // ikut search/filterStatus, supaya kartu ini selalu jadi gambaran menyeluruh
        // untuk periode terpilih (pola sama seperti $rekapTeknisi di KelolaAbsensi -
        // lihat catatan di sana). Panen::rekap() dipakai apa adanya (sudah teruji dari
        // Dashboard/Laporan), cuma ditambah filter tanggal & id_owners lewat milikOwner().
        $ringkasan = $this->rememberOwnerCache(['pembeli', 'panen'], "pembeli:ringkasan:p{$periodeSig}", 300, function () {
            $rekap = Panen::rekap(
                Panen::query()->milikOwner($this->owner->id)
                    ->when($this->dariTanggal, fn ($q) => $q->whereDate('tanggal', '>=', $this->dariTanggal))
                    ->when($this->sampaiTanggal, fn ($q) => $q->whereDate('tanggal', '<=', $this->sampaiTanggal))
            );

            return [
                'total_pembeli' => Pembeli::where('id_owners', $this->owner->id)->count(),
                'total_kg' => $rekap->total_berat,
                'kg_menunggu_harga' => $rekap->kg_menunggu_harga,
                'kg_belum_lunas' => $rekap->kg_belum_lunas,
                'total_hutang' => $rekap->total_sisa_hutang,
            ];
        });

        $selected = $this->selectedPembeliId
            ? $this->rememberOwnerCache(['pembeli', 'panen'], "pembeli:detail:{$this->selectedPembeliId}", 300, fn () =>
                $this->pembeliQuery()->with(['panens.tanaman'])->find($this->selectedPembeliId)
            )
            : null;

        $logs = $this->rememberOwnerCache(['activity_log'], 'activity_log:recent', 120, fn () =>
            ActivityLog::where('id_owners', $this->owner->id)->latest('id')->limit(15)->get()
        );

        return view('livewire.owner.kelola-pembeli', [
            'list' => $list,
            'ringkasan' => $ringkasan,
            'selected' => $selected,
            'logs' => $logs,
        ]);
    }
}
