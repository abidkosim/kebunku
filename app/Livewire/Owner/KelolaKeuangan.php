<?php

namespace App\Livewire\Owner;

use Livewire\Component;
use Livewire\WithPagination;
use App\Livewire\Owner\Concerns\RequiresOwnerAuth;
use App\Models\Keuangan;
use App\Models\ActivityLog;

class KelolaKeuangan extends Component
{
    use RequiresOwnerAuth, WithPagination;

    public $perPage = 10;

    public $dariTanggal;
    public $sampaiTanggal;

    public $showModal = false;
    public $isEditMode = false;
    public $editId = null;
    public $jenis_form = 'pengeluaran';
    public $kategori_form;
    public $jumlah_form;
    public $tanggal_form;
    public $catatan_form;

    public function mount()
    {
        if ($redirect = $this->loadAuthenticatedOwner()) {
            return $redirect;
        }
        if ($redirect = $this->requireRole(['owner', 'keuangan'])) {
            return $redirect;
        }

        $this->dariTanggal = now()->startOfMonth()->toDateString();
        $this->sampaiTanggal = now()->endOfMonth()->toDateString();
    }

    private function catat(string $aksi, string $keterangan): void
    {
        ActivityLog::catat($this->actorType, $this->actorId, $this->actorNama, $aksi, 'Keuangan', $keterangan, $this->owner->id);
    }

    private function keuanganQuery()
    {
        return Keuangan::where('id_owners', $this->owner->id);
    }

    private function keuanganPeriodeQuery()
    {
        return $this->keuanganQuery()
            ->when($this->dariTanggal, fn ($q) => $q->whereDate('tanggal', '>=', $this->dariTanggal))
            ->when($this->sampaiTanggal, fn ($q) => $q->whereDate('tanggal', '<=', $this->sampaiTanggal));
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

    public function updatedDariTanggal()
    {
        $this->resetPage();
    }

    public function updatedSampaiTanggal()
    {
        $this->resetPage();
    }

    public function kategoriOptions(): array
    {
        return $this->jenis_form === 'pemasukan' ? Keuangan::KATEGORI_PEMASUKAN : Keuangan::KATEGORI_PENGELUARAN;
    }

    public function updatedJenisForm()
    {
        $this->kategori_form = null;
    }

    public function openCreate()
    {
        $this->reset(['editId', 'kategori_form', 'jumlah_form', 'catatan_form']);
        $this->isEditMode = false;
        $this->jenis_form = 'pengeluaran';
        $this->tanggal_form = now()->toDateString();
        $this->showModal = true;
    }

    public function openEdit($id)
    {
        $item = $this->keuanganQuery()->findOrFail($id);
        $this->editId = $item->id;
        $this->jenis_form = $item->jenis;
        $this->kategori_form = $item->kategori;
        $this->jumlah_form = $item->jumlah;
        $this->tanggal_form = $item->tanggal->toDateString();
        $this->catatan_form = $item->catatan;
        $this->isEditMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'jenis_form' => 'required|in:pemasukan,pengeluaran',
            'kategori_form' => 'required|in:'.implode(',', $this->kategoriOptions()),
            'jumlah_form' => 'required|numeric|min:0.01',
            'tanggal_form' => 'required|date',
            'catatan_form' => 'nullable',
        ]);

        $payload = [
            'id_owners' => $this->owner->id,
            'jenis' => $this->jenis_form,
            'kategori' => $this->kategori_form,
            'jumlah' => $this->jumlah_form,
            'tanggal' => $this->tanggal_form,
            'catatan' => $this->catatan_form,
            'dicatat_oleh' => $this->actorNama,
        ];

        $labelJenis = $this->jenis_form === 'pemasukan' ? 'pemasukan' : 'pengeluaran';

        if ($this->isEditMode) {
            $item = $this->keuanganQuery()->findOrFail($this->editId);
            $item->update($payload);
            $this->catat('update', "Mengubah catatan {$labelJenis} '{$this->kategori_form}': Rp".number_format($this->jumlah_form, 0, ',', '.'));
        } else {
            Keuangan::create($payload);
            $this->catat('tambah', "Mencatat {$labelJenis} '{$this->kategori_form}': Rp".number_format($this->jumlah_form, 0, ',', '.'));
        }

        $this->showModal = false;
        $this->dispatch('alert-success', message: 'Catatan keuangan disimpan');
    }

    public function delete($id)
    {
        $item = $this->keuanganQuery()->findOrFail($id);
        $keterangan = "Menghapus catatan {$item->jenis} '{$item->kategori}': Rp".number_format($item->jumlah, 0, ',', '.');
        $item->delete();
        $this->catat('hapus', $keterangan);
        $this->dispatch('alert-success', message: 'Catatan keuangan dihapus');
    }

    public function render()
    {
        $list = $this->keuanganPeriodeQuery()
            ->latest('tanggal')
            ->latest('id')
            ->paginate($this->perPage);

        $totalPemasukan = (float) $this->keuanganPeriodeQuery()->where('jenis', 'pemasukan')->sum('jumlah');
        $totalPengeluaran = (float) $this->keuanganPeriodeQuery()->where('jenis', 'pengeluaran')->sum('jumlah');

        $logs = ActivityLog::where('id_owners', $this->owner->id)
            ->latest('id')
            ->limit(15)
            ->get();

        return view('livewire.owner.kelola-keuangan', [
            'list' => $list,
            'totalPemasukan' => $totalPemasukan,
            'totalPengeluaran' => $totalPengeluaran,
            'saldo' => $totalPemasukan - $totalPengeluaran,
            'logs' => $logs,
        ]);
    }
}
