<?php

namespace App\Livewire\Owner;

use Livewire\Component;
use Livewire\WithPagination;
use App\Livewire\Owner\Concerns\RequiresOwnerAuth;
use App\Livewire\Owner\Concerns\CachesOwnerData;
use App\Models\Tanaman;
use App\Models\Jadwal;
use App\Models\ActivityLog;

class JadwalSemprot extends Component
{
    use RequiresOwnerAuth, WithPagination, CachesOwnerData;

    public $search = '';
    public $perPage = 10;

    public $showModal = false;
    public $isNew = false;
    public $editId = null;
    public $tanamanId_form;
    public $tanggalRencana_form, $tanggalSelesai_form, $status_form = 'belum', $catatan_form;

    // Modal peringatan tenggat (H-2)
    public $showPeringatanTenggat = false;
    public $itemPeringatanTenggat = [];

    public function mount()
    {
        if ($redirect = $this->loadAuthenticatedOwner()) {
            return $redirect;
        }
        if ($redirect = $this->requireRole(['owner', 'teknisi'])) {
            return $redirect;
        }

        $this->cekPeringatanTenggat();
    }

    /**
     * Sama seperti ManajemenTanaman::cekPeringatanTenggat() - dipanggil sekali tiap page
     * load, cuma tampil 1x per hari per jadwal (lihat komentar versi Tahapan-nya untuk
     * alasan lengkap).
     */
    private function cekPeringatanTenggat(): void
    {
        $routePrefix = $this->actorType === 'owner' ? 'owner' : 'portal';

        $jadwalUrgent = Jadwal::whereHas('tanaman', fn ($q) => $q->where('id_owners', $this->owner->id))
            ->where('status', 'belum')
            ->where(fn ($q) => $q->whereNull('notif_h2_shown_at')->orWhereDate('notif_h2_shown_at', '<', now()->toDateString()))
            ->with('tanaman')
            ->get()
            ->filter(fn ($j) => $j->hampir_habis);

        if ($jadwalUrgent->isEmpty()) {
            return;
        }

        $this->itemPeringatanTenggat = $jadwalUrgent
            ->sortBy('sisa_hari')
            ->map(fn ($j) => [
                'label' => $j->tanaman->nama_tanaman,
                'subLabel' => 'Jadwal semprot: '.$j->tanggal_rencana->format('d M Y'),
                'sisaHari' => $j->sisa_hari,
                'link' => route($routePrefix.'.tanaman.semprot'),
            ])
            ->values()
            ->all();

        Jadwal::whereIn('id', $jadwalUrgent->pluck('id'))->update(['notif_h2_shown_at' => now()->toDateString()]);

        $this->showPeringatanTenggat = true;
    }

    public function tutupPeringatanTenggat(): void
    {
        $this->showPeringatanTenggat = false;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    private function catat(string $aksi, string $keterangan): void
    {
        ActivityLog::catat($this->actorType, $this->actorId, $this->actorNama, $aksi, 'Semprot', $keterangan, $this->owner->id);
    }

    private function jadwalQuery()
    {
        return Jadwal::whereHas('tanaman', fn ($q) => $q->where('id_owners', $this->owner->id));
    }

    public function openCreate()
    {
        $this->reset(['tanamanId_form', 'tanggalSelesai_form', 'catatan_form', 'editId']);
        $this->isNew = true;
        $this->tanggalRencana_form = now()->toDateString();
        $this->status_form = 'belum';
        $this->showModal = true;
    }

    public function openEdit($id)
    {
        $data = $this->jadwalQuery()->findOrFail($id);
        $this->isNew = false;
        $this->editId = $data->id;
        $this->tanamanId_form = $data->tanaman_id;
        $this->tanggalRencana_form = $data->tanggal_rencana->format('Y-m-d');
        $this->tanggalSelesai_form = $data->tanggal_selesai?->format('Y-m-d');
        $this->status_form = $data->status;
        $this->catatan_form = $data->catatan;
        $this->showModal = true;
    }

    public function save()
    {
        $rules = [
            'tanggalRencana_form' => 'required|date',
            'tanggalSelesai_form' => 'nullable|date',
            'status_form' => 'required|in:belum,selesai',
            'catatan_form' => 'nullable',
        ];
        if ($this->isNew) {
            $rules['tanamanId_form'] = 'required';
        }
        $this->validate($rules);

        $payload = [
            'tanggal_rencana' => $this->tanggalRencana_form,
            'tanggal_selesai' => $this->status_form === 'selesai' ? ($this->tanggalSelesai_form ?: now()->toDateString()) : null,
            'status' => $this->status_form,
            'catatan' => $this->catatan_form,
        ];

        if ($this->isNew) {
            $tanaman = Tanaman::where('id_owners', $this->owner->id)->findOrFail($this->tanamanId_form);
            Jadwal::create($payload + ['tanaman_id' => $tanaman->id]);
            $this->catat('tambah', "Menambahkan jadwal semprot untuk tanaman '{$tanaman->nama_tanaman}'");
        } else {
            $jadwal = $this->jadwalQuery()->findOrFail($this->editId);
            $jadwal->update($payload);
            $this->catat('update', "Mengupdate jadwal semprot tanaman '{$jadwal->tanaman->nama_tanaman}'");
        }

        $this->forgetOwnerCache(['jadwal_semprot', 'activity_log']);
        $this->showModal = false;
        $this->dispatch('alert-success', message: 'Jadwal semprot disimpan');
    }

    public function delete($id)
    {
        $jadwal = $this->jadwalQuery()->findOrFail($id);
        $nama = $jadwal->tanaman->nama_tanaman;
        $jadwal->delete();
        $this->catat('hapus', "Menghapus jadwal semprot tanaman '{$nama}'");
        $this->forgetOwnerCache(['jadwal_semprot', 'activity_log']);
        $this->dispatch('alert-success', message: 'Jadwal semprot dihapus');
    }

    public function render()
    {
        $cacheKey = 'jadwal:list:page'.$this->getPage().':per'.$this->perPage.':s'.md5($this->search ?? '');
        $list = $this->rememberOwnerCache(['jadwal_semprot'], $cacheKey, 300, fn () =>
            $this->jadwalQuery()
                ->with('tanaman')
                ->when($this->search, function ($q) {
                    $q->whereHas('tanaman', fn ($qq) => $qq->where('nama_tanaman', 'like', '%'.$this->search.'%'));
                })
                ->latest('id')
                ->paginate($this->perPage)
        );

        $tanamanAktif = $this->rememberOwnerCache(['tanaman'], 'tanaman:aktif-dropdown', 300, fn () =>
            Tanaman::where('id_owners', $this->owner->id)
                ->whereNull('siklus_selesai_at')
                ->orderBy('nama_tanaman')
                ->get()
        );

        $logs = $this->rememberOwnerCache(['activity_log'], 'activity_log:recent', 120, fn () =>
            ActivityLog::where('id_owners', $this->owner->id)->latest('id')->limit(15)->get()
        );

        return view('livewire.owner.jadwal-semprot', [
            'list' => $list,
            'tanamanAktif' => $tanamanAktif,
            'logs' => $logs,
        ]);
    }
}
