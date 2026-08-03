<?php

namespace App\Livewire\Owner;

use Livewire\Component;
use Livewire\WithPagination;
use App\Livewire\Owner\Concerns\RequiresOwnerAuth;
use App\Models\Tanaman;
use App\Models\Tahapan;
use App\Models\Pembeli;
use App\Models\Panen;
use App\Models\ActivityLog;

class KelolaPanen extends Component
{
    use RequiresOwnerAuth, WithPagination;

    public $search = '';
    public $perPage = 10;

    public $selectedTanamanId = null;

    // Modal Mulai Panen
    public $showModalMulai = false;
    public $jumlahAwalPanen_form;
    public $tanggalMulaiPanen_form;

    // Modal Catat Panen (transaksi)
    public $showModalPanen = false;
    public $tanggalPanen_form, $pembeliId_form, $pembeliBaru_form;
    public $beratKg_form, $metodeBayar_form = 'cash', $hargaPerKg_form, $jumlahDibayar_form, $catatanPanen_form;

    // Modal Catat Pembayaran
    public $showModalPembayaran = false;
    public $panenUntukBayarId = null;
    public $hargaPerKgBayar_form, $tambahanBayar_form;

    // Modal Tutup Siklus
    public $showModalTutup = false;
    public $tahapPanenIdUntukTutup = null;
    public $jumlahLolosTutup_form;
    public $tanggalTutup_form;
    public $catatanTutup_form;

    public function mount()
    {
        if ($redirect = $this->loadAuthenticatedOwner()) {
            return $redirect;
        }
        if ($redirect = $this->requireRole(['owner', 'teknisi'])) {
            return $redirect;
        }

        $tanamanId = request()->query('tanaman');
        if ($tanamanId && $this->tanamanQuery()->where('id', $tanamanId)->exists()) {
            $this->selectedTanamanId = (int) $tanamanId;
        }
    }

    private function catat(string $aksi, string $keterangan): void
    {
        ActivityLog::catat($this->actorType, $this->actorId, $this->actorNama, $aksi, 'Panen', $keterangan, $this->owner->id);
    }

    private function tanamanQuery()
    {
        return Tanaman::where('id_owners', $this->owner->id)
            ->whereHas('tahapans', fn ($q) => $q->where('jenis', 'pendewasaan')->where('status', 'selesai'));
    }

    private function tahapanQuery()
    {
        return Tahapan::whereHas('tanaman', fn ($q) => $q->where('id_owners', $this->owner->id));
    }

    private function panenQuery()
    {
        return Panen::whereHas('tanaman', fn ($q) => $q->where('id_owners', $this->owner->id));
    }

    public function viewDetail($id)
    {
        $this->tanamanQuery()->findOrFail($id);
        $this->selectedTanamanId = $id;
    }

    public function backToList()
    {
        $this->selectedTanamanId = null;
    }

    // --- Mulai Panen ---
    public function openMulaiPanen()
    {
        $tanaman = $this->tanamanQuery()->findOrFail($this->selectedTanamanId);
        $pendewasaan = $tanaman->tahapans()->where('jenis', 'pendewasaan')->where('status', 'selesai')->first();
        $this->jumlahAwalPanen_form = $pendewasaan?->jumlah_lolos;
        $this->tanggalMulaiPanen_form = now()->toDateString();
        $this->showModalMulai = true;
    }

    public function simpanMulaiPanen()
    {
        $this->validate([
            'tanggalMulaiPanen_form' => 'required|date',
        ]);

        $tanaman = $this->tanamanQuery()->findOrFail($this->selectedTanamanId);

        Tahapan::create([
            'tanaman_id' => $tanaman->id,
            'jenis' => 'panen',
            'jumlah_awal' => $this->jumlahAwalPanen_form,
            'durasi_rencana' => null,
            'tanggal_mulai' => $this->tanggalMulaiPanen_form,
            'tanggal_selesai_rencana' => null,
            'status' => 'berjalan',
        ]);

        $this->catat('tambah', "Memulai panen tanaman '{$tanaman->nama_tanaman}' dengan {$this->jumlahAwalPanen_form} tanaman");

        $this->showModalMulai = false;
        $this->dispatch('alert-success', message: 'Panen dimulai, silakan catat hasil panen');
    }

    // --- Catat Panen (transaksi berulang) ---
    public function openCreatePanen()
    {
        $this->reset(['pembeliId_form', 'pembeliBaru_form', 'beratKg_form', 'hargaPerKg_form', 'jumlahDibayar_form', 'catatanPanen_form']);
        $this->tanggalPanen_form = now()->toDateString();
        $this->metodeBayar_form = 'cash';
        $this->showModalPanen = true;
    }

    public function savePanen()
    {
        $rules = [
            'tanggalPanen_form' => 'required|date',
            'pembeliId_form' => 'required',
            'beratKg_form' => 'required|numeric|min:0.01',
            'metodeBayar_form' => 'required|in:cash,sebagian,hutang',
            'catatanPanen_form' => 'nullable',
        ];

        if ($this->pembeliId_form === 'baru') {
            $rules['pembeliBaru_form'] = 'required';
        }

        // cash & sebagian wajib tahu harga; hutang boleh dikosongkan dulu
        $rules['hargaPerKg_form'] = $this->metodeBayar_form === 'hutang'
            ? 'nullable|numeric|min:0.01'
            : 'required|numeric|min:0.01';

        if ($this->metodeBayar_form === 'sebagian') {
            $rules['jumlahDibayar_form'] = 'required|numeric|min:0.01';
        }

        $this->validate($rules);

        $tanaman = $this->tanamanQuery()->findOrFail($this->selectedTanamanId);

        if ($this->pembeliId_form === 'baru') {
            $pembeli = Pembeli::create(['id_owners' => $this->owner->id, 'nama' => $this->pembeliBaru_form]);
        } else {
            $pembeli = Pembeli::where('id_owners', $this->owner->id)->findOrFail($this->pembeliId_form);
        }

        $totalHarga = $this->hargaPerKg_form ? round((float) $this->beratKg_form * (float) $this->hargaPerKg_form, 2) : null;

        $jumlahDibayar = match ($this->metodeBayar_form) {
            'cash' => $totalHarga,
            'sebagian' => (float) $this->jumlahDibayar_form,
            default => 0,
        };

        if ($this->metodeBayar_form === 'sebagian' && $totalHarga !== null && $jumlahDibayar > $totalHarga) {
            $this->addError('jumlahDibayar_form', 'Jumlah bayar tidak boleh melebihi total harga.');
            return;
        }

        // Sesuai identitas sesi yang login (owner atau teknisi) - bukan input manual, supaya tidak bisa dipalsukan.
        Panen::create([
            'tanaman_id' => $tanaman->id,
            'pembeli_id' => $pembeli->id,
            'pemanen' => $this->actorNama,
            'tanggal' => $this->tanggalPanen_form,
            'berat_kg' => $this->beratKg_form,
            'harga_per_kg' => $this->hargaPerKg_form ?: null,
            'jumlah_dibayar' => $jumlahDibayar,
            'catatan' => $this->catatanPanen_form,
        ]);

        $labelMetode = ['cash' => 'lunas (cash)', 'sebagian' => 'dibayar sebagian', 'hutang' => 'hutang'];
        $keterangan = "Mencatat panen tanaman '{$tanaman->nama_tanaman}': {$this->beratKg_form} kg ke pembeli '{$pembeli->nama}'"
            .($totalHarga !== null ? ' (Rp'.number_format($totalHarga, 0, ',', '.').", {$labelMetode[$this->metodeBayar_form]})" : ' (harga belum ditentukan)');
        $this->catat('tambah', $keterangan);

        $this->showModalPanen = false;
        $this->dispatch('alert-success', message: 'Panen berhasil dicatat');
    }

    public function openCatatPembayaran($id)
    {
        $panen = $this->panenQuery()->findOrFail($id);
        $this->panenUntukBayarId = $panen->id;
        $this->hargaPerKgBayar_form = $panen->harga_per_kg;
        // Langsung diisi nominal kekurangannya (sisa hutang) - owner tinggal turunkan angkanya kalau cuma mau bayar sebagian.
        $this->tambahanBayar_form = $panen->harga_per_kg !== null ? $panen->sisa_hutang : null;
        $this->showModalPembayaran = true;
    }

    public function updatedHargaPerKgBayarForm($value)
    {
        // Kalau tadinya hutang tanpa harga, begitu harga diisi langsung hitung & isi otomatis kekurangannya.
        $panen = $this->panenQuery()->find($this->panenUntukBayarId);
        if (!$panen || !is_numeric($value)) {
            return;
        }
        $totalHarga = round((float) $panen->berat_kg * (float) $value, 2);
        $this->tambahanBayar_form = max(0, round($totalHarga - (float) $panen->jumlah_dibayar, 2));
    }

    public function simpanPembayaran()
    {
        $panen = $this->panenQuery()->findOrFail($this->panenUntukBayarId);

        $this->validate([
            'hargaPerKgBayar_form' => 'required|numeric|min:0.01',
            'tambahanBayar_form' => 'required|numeric|min:0.01',
        ]);

        if ($panen->harga_per_kg === null) {
            $panen->harga_per_kg = $this->hargaPerKgBayar_form;
        }

        $totalHarga = round((float) $panen->berat_kg * (float) $panen->harga_per_kg, 2);
        $sudahDibayar = (float) $panen->jumlah_dibayar;
        $tambahan = (float) $this->tambahanBayar_form;

        if ($sudahDibayar + $tambahan > $totalHarga) {
            $this->addError('tambahanBayar_form', 'Melebihi sisa hutang yang harus dibayar.');
            return;
        }

        $panen->jumlah_dibayar = $sudahDibayar + $tambahan;
        $panen->save();

        $sisa = $totalHarga - $panen->jumlah_dibayar;
        $keterangan = "Mencatat pembayaran panen tanaman '{$panen->tanaman->nama_tanaman}' dari '{$panen->pembeli->nama}': Rp".number_format($tambahan, 0, ',', '.')
            .($sisa > 0 ? ', sisa hutang Rp'.number_format($sisa, 0, ',', '.') : ', lunas');
        $this->catat('update', $keterangan);

        $this->showModalPembayaran = false;
        $this->dispatch('alert-success', message: 'Pembayaran berhasil dicatat');
    }

    public function deletePanen($id)
    {
        $panen = $this->panenQuery()->findOrFail($id);
        $nama = $panen->tanaman->nama_tanaman;
        $panen->delete();
        $this->catat('hapus', "Menghapus catatan panen tanaman '{$nama}'");
        $this->dispatch('alert-success', message: 'Catatan panen dihapus');
    }

    // --- Tutup Siklus ---
    public function openTutupSiklus($tahapPanenId)
    {
        $tahap = $this->tahapanQuery()->findOrFail($tahapPanenId);
        $this->tahapPanenIdUntukTutup = $tahap->id;
        $this->jumlahLolosTutup_form = $tahap->jumlah_awal;
        $this->tanggalTutup_form = now()->toDateString();
        $this->catatanTutup_form = null;
        $this->showModalTutup = true;
    }

    public function simpanTutupSiklus()
    {
        $tahap = $this->tahapanQuery()->findOrFail($this->tahapPanenIdUntukTutup);

        $this->validate([
            'jumlahLolosTutup_form' => "required|integer|min:0|max:{$tahap->jumlah_awal}",
            'tanggalTutup_form' => 'required|date',
            'catatanTutup_form' => 'nullable',
        ]);

        $tahap->update([
            'status' => 'selesai',
            'jumlah_lolos' => $this->jumlahLolosTutup_form,
            'tanggal_selesai_aktual' => $this->tanggalTutup_form,
            'catatan' => $this->catatanTutup_form,
        ]);

        $tahap->tanaman->update(['siklus_selesai_at' => now()]);

        $this->catat('update', "Menutup siklus panen tanaman '{$tahap->tanaman->nama_tanaman}': {$this->jumlahLolosTutup_form}/{$tahap->jumlah_awal} berhasil dipanen. Meja sudah bebas lagi.");

        $this->showModalTutup = false;
        $this->dispatch('alert-success', message: 'Siklus panen ditutup, meja sudah bebas lagi');
    }

    public function render()
    {
        $list = $this->tanamanQuery()
            ->with(['tahapans', 'meja.kebun'])
            ->when($this->search, function ($q) {
                $q->where('nama_tanaman', 'like', '%'.$this->search.'%');
            })
            ->latest('id')
            ->paginate($this->perPage);

        $selected = $this->selectedTanamanId
            ? $this->tanamanQuery()->with(['tahapans', 'panens.pembeli', 'meja.kebun'])->find($this->selectedTanamanId)
            : null;

        $pembeliList = Pembeli::where('id_owners', $this->owner->id)->orderBy('nama')->get();

        $panenUntukBayar = $this->panenUntukBayarId ? $this->panenQuery()->find($this->panenUntukBayarId) : null;

        $logs = ActivityLog::where('id_owners', $this->owner->id)
            ->latest('id')
            ->limit(15)
            ->get();

        return view('livewire.owner.kelola-panen', [
            'list' => $list,
            'selected' => $selected,
            'pembeliList' => $pembeliList,
            'panenUntukBayar' => $panenUntukBayar,
            'logs' => $logs,
        ]);
    }
}
