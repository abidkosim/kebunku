<?php

namespace App\Livewire\Owner;

use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use App\Livewire\Owner\Concerns\RequiresOwnerAuth;
use App\Livewire\Owner\Concerns\CachesOwnerData;
use App\Models\ActivityLog;
use App\Models\Kebun;
use App\Models\Tandon;
use App\Models\TandonBacaan;
use App\Jobs\SimulasikanTandon;
use App\Services\TandonIngestService;
use Illuminate\Support\Str;

class MonitorTandon extends Component
{
    use RequiresOwnerAuth, WithPagination, CachesOwnerData;

    public $search = '';
    public $perPage = 10;

    public $selectedTandonId = null;
    public $rentangGrafik = '24jam'; // '24jam' | '7hari'

    public $showModal = false;
    public $isEditMode = false;
    public $editId = null;
    public $id_kebun_form, $nama_form, $target_ppm_form, $target_ph_form;
    public $durasi_dosing_detik_form, $jeda_cek_detik_form, $maks_percobaan_dosing_form;

    public function mount()
    {
        if ($redirect = $this->loadAuthenticatedOwner()) {
            return $redirect;
        }
        if ($redirect = $this->requireRole(['owner'])) {
            return $redirect;
        }
        if ($redirect = $this->requireAksesPenuh()) {
            return $redirect;
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    #[On('echo:tandon.{owner.id},.TandonUpdated')]
    public function segarkanOtomatis()
    {
        // body kosong - kehadiran event ini cukup membuat Livewire render ulang & ambil data terbaru
    }

    private function catat(string $aksi, string $keterangan): void
    {
        ActivityLog::catat($this->actorType, $this->actorId, $this->actorNama, $aksi, 'Monitor Tandon', $keterangan, $this->owner->id);
    }

    private function tandonQuery()
    {
        return Tandon::whereHas('kebun', function ($q) {
            $q->where('id_owners', $this->owner->id);
        });
    }

    public function viewDetail($id)
    {
        $tandon = $this->tandonQuery()->findOrFail($id);
        $this->selectedTandonId = $id;

        // siapkan form target inline di halaman detail (pakai method save() yang sama)
        $this->editId = $tandon->id;
        $this->id_kebun_form = $tandon->id_kebun;
        $this->nama_form = $tandon->nama;
        $this->target_ppm_form = $tandon->target_ppm;
        $this->target_ph_form = $tandon->target_ph;
        $this->durasi_dosing_detik_form = $tandon->durasi_dosing_detik;
        $this->jeda_cek_detik_form = $tandon->jeda_cek_detik;
        $this->maks_percobaan_dosing_form = $tandon->maks_percobaan_dosing;
        $this->isEditMode = true;
    }

    public function backToList()
    {
        $this->selectedTandonId = null;
    }

    public function openCreate()
    {
        $this->reset(['nama_form', 'id_kebun_form', 'editId']);
        $this->target_ppm_form = 750;
        $this->target_ph_form = 6.0;
        $this->durasi_dosing_detik_form = 5;
        $this->jeda_cek_detik_form = 60;
        $this->maks_percobaan_dosing_form = 5;
        $this->isEditMode = false;
        $this->showModal = true;
    }

    public function openEdit($id)
    {
        $tandon = $this->tandonQuery()->findOrFail($id);
        $this->editId = $tandon->id;
        $this->id_kebun_form = $tandon->id_kebun;
        $this->nama_form = $tandon->nama;
        $this->target_ppm_form = $tandon->target_ppm;
        $this->target_ph_form = $tandon->target_ph;
        $this->durasi_dosing_detik_form = $tandon->durasi_dosing_detik;
        $this->jeda_cek_detik_form = $tandon->jeda_cek_detik;
        $this->maks_percobaan_dosing_form = $tandon->maks_percobaan_dosing;
        $this->isEditMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'id_kebun_form' => 'required|exists:kebun,id',
            'nama_form' => 'required|string|max:100',
            'target_ppm_form' => 'required|integer|min:0|max:2000',
            'target_ph_form' => 'required|numeric|min:0|max:14',
            'durasi_dosing_detik_form' => 'required|integer|min:1|max:120',
            'jeda_cek_detik_form' => 'required|integer|min:5|max:1800',
            'maks_percobaan_dosing_form' => 'required|integer|min:1|max:20',
        ]);

        // pastikan kebun yang dipilih benar-benar milik owner ini
        $this->kebunQuery()->findOrFail($this->id_kebun_form);

        if ($this->isEditMode) {
            $tandon = $this->tandonQuery()->findOrFail($this->editId);
            $tandon->update([
                'id_kebun' => $this->id_kebun_form,
                'nama' => $this->nama_form,
                'target_ppm' => $this->target_ppm_form,
                'target_ph' => $this->target_ph_form,
                'durasi_dosing_detik' => $this->durasi_dosing_detik_form,
                'jeda_cek_detik' => $this->jeda_cek_detik_form,
                'maks_percobaan_dosing' => $this->maks_percobaan_dosing_form,
            ]);
            $this->catat('update', "Mengubah target tandon '{$tandon->nama}': PPM {$this->target_ppm_form}, pH {$this->target_ph_form}");
            $this->forgetOwnerCache(['activity_log']);
        } else {
            $tandon = Tandon::create([
                'id_kebun' => $this->id_kebun_form,
                'nama' => $this->nama_form,
                'target_ppm' => $this->target_ppm_form,
                'target_ph' => $this->target_ph_form,
                'durasi_dosing_detik' => $this->durasi_dosing_detik_form,
                'jeda_cek_detik' => $this->jeda_cek_detik_form,
                'maks_percobaan_dosing' => $this->maks_percobaan_dosing_form,
                'ppm_terkini' => max(0, $this->target_ppm_form - 150),
                'ph_terkini' => max(0, $this->target_ph_form - 0.5),
                'suhu_terkini' => 27.0,
                'status_simulasi' => 'aktif',
                'terakhir_baca_at' => now(),
            ]);
            SimulasikanTandon::dispatch($tandon->id)->delay(now()->addSeconds(5));
            $this->catat('tambah', "Menambahkan tandon baru '{$tandon->nama}'");
            $this->forgetOwnerCache(['activity_log']);
        }

        $this->showModal = false;
        $this->dispatch('alert-success', message: 'Data tandon disimpan');
    }

    public function toggleSimulasi($id)
    {
        $tandon = $this->tandonQuery()->findOrFail($id);

        if ($tandon->status_simulasi === 'aktif') {
            $tandon->update(['status_simulasi' => 'berhenti', 'status_pompa' => null]);
            $this->catat('update', "Menghentikan simulasi sensor tandon '{$tandon->nama}'");
        } else {
            $tandon->update(['status_simulasi' => 'aktif']);
            SimulasikanTandon::dispatch($tandon->id)->delay(now()->addSeconds(3));
            $this->catat('update', "Mengaktifkan kembali simulasi sensor tandon '{$tandon->nama}'");
        }
        $this->forgetOwnerCache(['activity_log']);
    }

    public function ubahSumberData($id)
    {
        $tandon = $this->tandonQuery()->findOrFail($id);

        if ($tandon->sumber_data === 'iot') {
            $tandon->update(['sumber_data' => 'simulasi']);
            $this->catat('update', "Tandon '{$tandon->nama}' dikembalikan ke mode simulasi");
        } else {
            $tandon->update(['sumber_data' => 'iot', 'status_simulasi' => 'berhenti', 'status_pompa' => null]);
            $this->catat('update', "Tandon '{$tandon->nama}' dipindah ke mode IoT (sensor asli lewat ESP32)");
        }
        $this->forgetOwnerCache(['activity_log']);
    }

    public function generateUlangToken($id)
    {
        $tandon = $this->tandonQuery()->findOrFail($id);
        $tandon->update(['device_token' => Str::random(40)]);
        $this->catat('update', "Token perangkat IoT tandon '{$tandon->nama}' di-generate ulang");
        $this->forgetOwnerCache(['activity_log']);
        $this->dispatch('alert-success', message: 'Token perangkat baru sudah dibuat');
    }

    public function generateKunciMonitor()
    {
        $this->owner->update(['kunci_monitor' => Str::random(32)]);
        $this->owner->refresh();
        $this->catat('update', 'Link Monitor Publik dibuat/di-generate ulang');
        $this->forgetOwnerCache(['activity_log']);
        $this->dispatch('alert-success', message: 'Link monitor publik sudah dibuat');
    }

    public function delete($id)
    {
        $tandon = $this->tandonQuery()->findOrFail($id);
        $tandon->update(['status_simulasi' => 'berhenti']);
        $nama = $tandon->nama;
        $tandon->delete();
        $this->catat('hapus', "Menghapus tandon '{$nama}'");
        $this->forgetOwnerCache(['activity_log']);
        $this->dispatch('alert-success', message: 'Tandon dihapus');
    }

    private function kebunQuery()
    {
        return Kebun::where('id_owners', $this->owner->id);
    }

    public function render()
    {
        // Menandai bahwa layar monitor milik owner ini sedang benar-benar dibuka, supaya
        // SimulasikanTandon memakai jeda cepat (8 detik) dan siaran realtime-nya dikirim.
        // Saat tidak ada yang membuka, simulasi otomatis melambat sendiri - lihat
        // App\Jobs\SimulasikanTandon.
        TandonIngestService::tandaiSedangDitonton($this->owner->id);

        $list = $this->tandonQuery()
            ->with('kebun')
            ->when($this->search, function ($q) {
                $q->where(function ($q2) {
                    $q2->where('nama', 'like', '%'.$this->search.'%')
                        ->orWhereHas('kebun', function ($q3) {
                            $q3->where('nama_kebun', 'like', '%'.$this->search.'%');
                        });
                });
            })
            ->latest('id')
            ->paginate($this->perPage);

        // Sengaja TIDAK di-cache: $list/$selected berisi ppm/ph/suhu terkini yang ditulis
        // ulang tiap 8 detik oleh SimulasikanTandon dan didorong realtime lewat broadcast
        // TandonUpdated - cache di sini cuma akan basi dalam hitungan detik atau (kalau
        // di-flush tiap tick juga) menambah beban Redis tanpa manfaat cache sama sekali.
        $selected = $this->selectedTandonId
            ? $this->tandonQuery()->with('kebun')->find($this->selectedTandonId)
            : null;

        $kebunList = $this->rememberOwnerCache(['kebun'], 'kebun:dropdown', 300, fn () =>
            $this->kebunQuery()->orderBy('nama_kebun')->get()
        );

        $logs = $this->rememberOwnerCache(['activity_log'], 'activity_log:recent', 120, fn () =>
            ActivityLog::where('id_owners', $this->owner->id)->latest('id')->limit(15)->get()
        );

        if ($selected) {
            $mulai = $this->rentangGrafik === '7hari' ? now()->subDays(7) : now()->subDay();
            $formatJam = $this->rentangGrafik === '7hari' ? 'd/m H:i' : 'H:i';

            // Ini AMAN di-cache (beda dengan $list/$selected di atas): baris riwayat cuma
            // masuk ~tiap 5 menit (throttle di SimulasikanTandon), dan TandonBacaan::booted()
            // sudah flush tag ini begitu ada baris baru - jadi query berat ini kena cache
            // hampir selalu, cuma miss tepat setelah ada data baru masuk.
            $grafik = $this->rememberOwnerCache(
                ["tandon_bacaan:{$selected->id}"],
                "tandon:grafik:{$selected->id}:{$this->rentangGrafik}",
                600,
                function () use ($selected, $mulai, $formatJam) {
                    $bacaan = TandonBacaan::where('id_tandon', $selected->id)
                        ->where('created_at', '>=', $mulai)
                        ->orderBy('created_at')
                        ->get();

                    return [
                        'labels' => $bacaan->map(fn ($b) => $b->created_at->format($formatJam))->values(),
                        'ppm' => $bacaan->pluck('ppm')->values(),
                        'ph' => $bacaan->pluck('ph')->values(),
                        'suhu' => $bacaan->pluck('suhu')->values(),
                    ];
                }
            );

            $this->dispatch('tandon-grafik-data', tandonId: $selected->id, grafik: $grafik);
        }

        return view('livewire.owner.monitor-tandon', [
            'list' => $list,
            'selected' => $selected,
            'kebunList' => $kebunList,
            'logs' => $logs,
        ]);
    }
}
