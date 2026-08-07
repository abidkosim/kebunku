<?php

namespace App\Livewire\Owner;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use App\Livewire\Owner\Concerns\RequiresOwnerAuth;
use App\Livewire\Owner\Concerns\CachesOwnerData;
use App\Models\Tanaman;
use App\Models\Tahapan;
use App\Models\Kebun;
use App\Models\Meja;
use App\Models\ActivityLog;
use Carbon\Carbon;

/**
 * Halaman "Kelola Tanaman": daftar tanaman + siklus pertumbuhan (semai, peremajaan,
 * pendewasaan) sampai berstatus "Siap Panen". Semprot ada di halamannya sendiri,
 * dan tahap Panen (yang bersifat transaksi berulang) dikelola di halaman Panen.
 */
class ManajemenTanaman extends Component
{
    use RequiresOwnerAuth, WithPagination, CachesOwnerData;

    public $search = '';
    public $perPage = 10;

    // Modal Tanaman
    public $showModalTanaman = false;
    public $isEditModeTanaman = false;
    public $editTanamanId = null;
    public $namaTanaman_form, $kebunId_form, $mejaId_form, $catatanTanaman_form;

    // Detail view
    public $selectedTanamanId = null;

    // Modal Tahap (mulai tahap baru / edit tahap)
    public $showModalTahap = false;
    public $tahapMode = 'mulai'; // mulai | edit
    public $editTahapId = null;
    public $jenisTahap_form;
    public $jumlahAwal_form;
    public $jumlahAwalTerkunci = false; // true jika diwariskan dari tahap sebelumnya (tidak bisa diubah)
    public $durasiRencana_form;
    public $tanggalMulai_form;
    public $catatanTahap_form;

    // Modal Tandai Selesai (input jumlah lolos/hidup)
    public $showModalSelesai = false;
    public $tahapUntukSelesaiId = null;
    public $jumlahLolos_form;
    public $tanggalSelesaiAktual_form;
    public $catatanSelesai_form;

    public function mount()
    {
        if ($redirect = $this->loadAuthenticatedOwner()) {
            return $redirect;
        }
        if ($redirect = $this->requireRole(['owner', 'teknisi'])) {
            return $redirect;
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    private function catat(string $aksi, string $keterangan): void
    {
        ActivityLog::catat($this->actorType, $this->actorId, $this->actorNama, $aksi, 'Tanaman', $keterangan, $this->owner->id);
    }

    private function tanamanQuery()
    {
        return Tanaman::where('id_owners', $this->owner->id);
    }

    private function tahapanQuery()
    {
        return Tahapan::whereHas('tanaman', fn ($q) => $q->where('id_owners', $this->owner->id));
    }

    // --- CRUD Tanaman ---
    public function openCreateTanaman()
    {
        $this->reset(['namaTanaman_form', 'kebunId_form', 'mejaId_form', 'catatanTanaman_form', 'editTanamanId']);
        $this->isEditModeTanaman = false;
        $this->showModalTanaman = true;
    }

    public function openEditTanaman($id)
    {
        $data = $this->tanamanQuery()->findOrFail($id);
        $this->editTanamanId = $data->id;
        $this->namaTanaman_form = $data->nama_tanaman;
        $this->kebunId_form = $data->meja->kebun_id;
        $this->mejaId_form = $data->meja_id;
        $this->catatanTanaman_form = $data->catatan;
        $this->isEditModeTanaman = true;
        $this->showModalTanaman = true;
    }

    #[Computed]
    public function mejaTersedia()
    {
        if (!$this->kebunId_form) {
            return collect();
        }

        $terpakai = Tanaman::where('id_owners', $this->owner->id)
            ->whereNull('siklus_selesai_at')
            ->when($this->isEditModeTanaman, fn ($q) => $q->where('id', '!=', $this->editTanamanId))
            ->pluck('meja_id');

        return Meja::where('kebun_id', $this->kebunId_form)
            ->whereNotIn('id', $terpakai)
            ->orderBy('nomor')
            ->get();
    }

    public function saveTanaman()
    {
        $this->validate([
            'namaTanaman_form' => 'required',
            'kebunId_form' => 'required',
            'mejaId_form' => 'required',
            'catatanTanaman_form' => 'nullable',
        ]);

        $meja = Meja::whereHas('kebun', fn ($q) => $q->where('id_owners', $this->owner->id))
            ->findOrFail($this->mejaId_form);

        $sudahTerpakai = Tanaman::where('meja_id', $meja->id)
            ->whereNull('siklus_selesai_at')
            ->when($this->isEditModeTanaman, fn ($q) => $q->where('id', '!=', $this->editTanamanId))
            ->exists();

        if ($sudahTerpakai) {
            $this->addError('mejaId_form', 'Meja ini sudah terpakai tanaman lain.');
            return;
        }

        $payload = [
            'id_owners' => $this->owner->id,
            'meja_id' => $meja->id,
            'nama_tanaman' => $this->namaTanaman_form,
            'catatan' => $this->catatanTanaman_form,
        ];

        if ($this->isEditModeTanaman) {
            $this->tanamanQuery()->findOrFail($this->editTanamanId)->update($payload);
            $msg = 'Data tanaman berhasil diupdate!';
            $this->catat('update', "Mengupdate data tanaman '{$this->namaTanaman_form}'");
        } else {
            Tanaman::create($payload);
            $msg = 'Tanaman baru berhasil ditambah! Lanjut klik "Mulai Semai" di halaman detail.';
            $this->catat('tambah', "Menambahkan tanaman baru '{$this->namaTanaman_form}' di meja #{$meja->nomor}");
        }

        $this->forgetOwnerCache(['tanaman', 'activity_log']);
        $this->showModalTanaman = false;
        $this->dispatch('alert-success', message: $msg);
    }

    public function deleteTanaman($id)
    {
        $target = $this->tanamanQuery()->findOrFail($id);
        $nama = $target->nama_tanaman;
        $target->delete();
        $this->catat('hapus', "Menghapus tanaman '{$nama}'");
        $this->forgetOwnerCache(['tanaman', 'activity_log']);
        if ($this->selectedTanamanId == $id) {
            $this->selectedTanamanId = null;
        }
        $this->dispatch('alert-success', message: 'Data tanaman berhasil dihapus, meja sudah bisa dipakai lagi');
    }

    // --- Detail navigation ---
    public function viewDetail($id)
    {
        $this->tanamanQuery()->findOrFail($id);
        $this->selectedTanamanId = $id;
    }

    public function backToList()
    {
        $this->selectedTanamanId = null;
    }

    // --- Tahapan (siklus pertumbuhan: semai, peremajaan, pendewasaan) ---
    public function openMulaiTahap($jenis)
    {
        $this->reset(['editTahapId', 'catatanTahap_form', 'durasiRencana_form']);
        $this->tahapMode = 'mulai';
        $this->jenisTahap_form = $jenis;
        $this->tanggalMulai_form = now()->toDateString();

        if ($jenis === 'semai') {
            $this->jumlahAwal_form = null;
            $this->jumlahAwalTerkunci = false;
        } else {
            $tahapSebelumnya = $this->tanamanQuery()->findOrFail($this->selectedTanamanId)
                ->tahapans()->latest('id')->first();
            $this->jumlahAwal_form = $tahapSebelumnya?->jumlah_lolos;
            $this->jumlahAwalTerkunci = true;
        }

        $this->showModalTahap = true;
    }

    public function openEditTahap($id)
    {
        $data = $this->tahapanQuery()->findOrFail($id);
        $this->tahapMode = 'edit';
        $this->editTahapId = $data->id;
        $this->jenisTahap_form = $data->jenis;
        $this->jumlahAwal_form = $data->jumlah_awal;
        $this->jumlahAwalTerkunci = $data->jenis !== 'semai';
        $this->durasiRencana_form = $data->durasi_rencana;
        $this->tanggalMulai_form = $data->tanggal_mulai->format('Y-m-d');
        $this->catatanTahap_form = $data->catatan;
        $this->showModalTahap = true;
    }

    public function saveTahap()
    {
        $this->validate([
            'jumlahAwal_form' => 'required|integer|min:1',
            'durasiRencana_form' => 'required|integer|min:1',
            'tanggalMulai_form' => 'required|date',
            'catatanTahap_form' => 'nullable',
        ]);

        $tanggalSelesaiRencana = Carbon::parse($this->tanggalMulai_form)->addDays((int) $this->durasiRencana_form);

        if ($this->tahapMode === 'mulai') {
            $tanaman = $this->tanamanQuery()->findOrFail($this->selectedTanamanId);
            Tahapan::create([
                'tanaman_id' => $tanaman->id,
                'jenis' => $this->jenisTahap_form,
                'jumlah_awal' => $this->jumlahAwal_form,
                'durasi_rencana' => $this->durasiRencana_form,
                'tanggal_mulai' => $this->tanggalMulai_form,
                'tanggal_selesai_rencana' => $tanggalSelesaiRencana,
                'status' => 'berjalan',
                'catatan' => $this->catatanTahap_form,
            ]);
            $this->catat('tambah', "Memulai tahap {$this->jenisTahap_form} untuk tanaman '{$tanaman->nama_tanaman}' dengan {$this->jumlahAwal_form} tanaman");
        } else {
            $tahap = $this->tahapanQuery()->findOrFail($this->editTahapId);
            $tahap->update([
                'jumlah_awal' => $this->jumlahAwal_form,
                'durasi_rencana' => $this->durasiRencana_form,
                'tanggal_mulai' => $this->tanggalMulai_form,
                'tanggal_selesai_rencana' => $tanggalSelesaiRencana,
                'catatan' => $this->catatanTahap_form,
            ]);
            $this->catat('update', "Mengupdate tahap {$tahap->jenis} untuk tanaman '{$tahap->tanaman->nama_tanaman}'");
        }

        $this->forgetOwnerCache(['tanaman', 'activity_log']);
        $this->showModalTahap = false;
        $this->dispatch('alert-success', message: 'Tahap berhasil disimpan');
    }

    public function openTandaiSelesai($id)
    {
        $tahap = $this->tahapanQuery()->findOrFail($id);
        $this->tahapUntukSelesaiId = $tahap->id;
        $this->jumlahLolos_form = $tahap->jumlah_awal;
        $this->tanggalSelesaiAktual_form = now()->toDateString();
        $this->catatanSelesai_form = null;
        $this->showModalSelesai = true;
    }

    public function simpanSelesai()
    {
        $tahap = $this->tahapanQuery()->findOrFail($this->tahapUntukSelesaiId);

        $this->validate([
            'jumlahLolos_form' => "required|integer|min:0|max:{$tahap->jumlah_awal}",
            'tanggalSelesaiAktual_form' => 'required|date',
            'catatanSelesai_form' => 'nullable',
        ]);

        $tahap->update([
            'status' => 'selesai',
            'jumlah_lolos' => $this->jumlahLolos_form,
            'tanggal_selesai_aktual' => $this->tanggalSelesaiAktual_form,
            'catatan' => $this->catatanSelesai_form ?: $tahap->catatan,
        ]);

        $mati = $tahap->jumlah_awal - $this->jumlahLolos_form;
        $keterangan = $mati > 0
            ? "Menyelesaikan tahap {$tahap->jenis} tanaman '{$tahap->tanaman->nama_tanaman}': {$this->jumlahLolos_form}/{$tahap->jumlah_awal} hidup ({$mati} mati)"
            : "Menyelesaikan tahap {$tahap->jenis} tanaman '{$tahap->tanaman->nama_tanaman}': lengkap {$this->jumlahLolos_form}/{$tahap->jumlah_awal} hidup";
        $this->catat('update', $keterangan);
        $this->forgetOwnerCache(['tanaman', 'activity_log']);

        $this->showModalSelesai = false;
        $this->dispatch('alert-success', message: 'Tahap ditandai selesai');
    }

    public function batalkanSelesaiTahap($id)
    {
        $tahap = $this->tahapanQuery()->findOrFail($id);
        $adaTahapSetelahnya = Tahapan::where('tanaman_id', $tahap->tanaman_id)->where('id', '>', $tahap->id)->exists();

        if ($adaTahapSetelahnya) {
            $this->dispatch('alert-error', message: 'Tidak bisa dibatalkan, sudah ada tahap berikutnya yang dimulai.');
            return;
        }

        $tahap->update(['status' => 'berjalan', 'tanggal_selesai_aktual' => null, 'jumlah_lolos' => null]);
        $this->catat('update', "Membatalkan status selesai tahap {$tahap->jenis} untuk tanaman '{$tahap->tanaman->nama_tanaman}'");
        $this->forgetOwnerCache(['tanaman', 'activity_log']);
        $this->dispatch('alert-success', message: 'Status tahap dikembalikan ke berjalan');
    }

    public function render()
    {
        $cacheKey = 'tanaman:list:page'.$this->getPage().':per'.$this->perPage.':s'.md5($this->search ?? '');
        $list = $this->rememberOwnerCache(['tanaman'], $cacheKey, 300, fn () =>
            $this->tanamanQuery()
                ->with(['tahapans', 'meja.kebun'])
                ->when($this->search, function ($q) {
                    $q->where('nama_tanaman', 'like', '%'.$this->search.'%');
                })
                ->latest('id')
                ->paginate($this->perPage)
        );

        $selected = $this->selectedTanamanId
            ? $this->rememberOwnerCache(['tanaman'], "tanaman:detail:{$this->selectedTanamanId}", 300, fn () =>
                $this->tanamanQuery()->with(['tahapans', 'meja.kebun'])->find($this->selectedTanamanId)
            )
            : null;

        $kebunList = $this->rememberOwnerCache(['kebun'], 'kebun:dropdown', 300, fn () =>
            Kebun::where('id_owners', $this->owner->id)->orderBy('nama_kebun')->get()
        );

        $logs = $this->rememberOwnerCache(['activity_log'], 'activity_log:recent', 120, fn () =>
            ActivityLog::where('id_owners', $this->owner->id)->latest('id')->limit(15)->get()
        );

        return view('livewire.owner.manajemen-tanaman', [
            'list' => $list,
            'selected' => $selected,
            'kebunList' => $kebunList,
            'logs' => $logs,
        ]);
    }
}
