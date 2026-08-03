<?php

namespace App\Livewire\Owner;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Livewire\Owner\Concerns\RequiresOwnerAuth;
use App\Models\Galeri;
use App\Models\ActivityLog;
use App\Jobs\BuatThumbnailGaleri;
use App\Events\GaleriUpdated;
use Illuminate\Support\Facades\Storage;

class KelolaGaleri extends Component
{
    use RequiresOwnerAuth, WithFileUploads, WithPagination;

    public $perPage = 12;

    public $showModalUpload = false;
    public $file_upload;
    public $keterangan_form;

    public $showModalView = false;
    public $viewId = null;

    public $showModalEdit = false;
    public $editId = null;
    public $keteranganEdit_form;

    public function mount()
    {
        if ($redirect = $this->loadAuthenticatedOwner()) {
            return $redirect;
        }
        if ($redirect = $this->requireRole(['owner', 'teknisi', 'keuangan'])) {
            return $redirect;
        }
    }

    /**
     * Dipanggil otomatis lewat WebSocket (Laravel Reverb) tiap ada broadcast GaleriUpdated
     * untuk owner ini - dari sesi/browser manapun (upload, edit, hapus, atau thumbnail
     * selesai diproses job). Body kosong: Livewire otomatis re-render setelah listener
     * jalan, jadi cukup "dibangunkan" saja - data terbaru diambil ulang oleh render().
     */
    #[On('echo:galeri.{owner.id},.GaleriUpdated')]
    public function segarkanOtomatis(): void
    {
        //
    }

    private function siarkanPerubahan(): void
    {
        GaleriUpdated::dispatch($this->owner->id);
    }

    private function catat(string $aksi, string $keterangan): void
    {
        ActivityLog::catat($this->actorType, $this->actorId, $this->actorNama, $aksi, 'Galeri', $keterangan, $this->owner->id);
    }

    private function galeriQuery()
    {
        return Galeri::where('id_owners', $this->owner->id);
    }

    public function bolehKelola(Galeri $item): bool
    {
        if ($this->actorType === 'owner') {
            return true;
        }
        return $item->actor_type === $this->actorType && $item->actor_id === $this->actorId;
    }

    public function openUpload()
    {
        $this->reset(['file_upload', 'keterangan_form']);
        $this->showModalUpload = true;
    }

    public function simpanUpload()
    {
        $this->validate([
            'file_upload' => 'required|file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,webm|max:51200',
            'keterangan_form' => 'nullable',
        ]);

        $mime = $this->file_upload->getMimeType();
        $jenis = str_starts_with($mime, 'video/') ? 'video' : 'foto';
        // Cuma pindah file (cepat) di request ini - resize thumbnail dikerjakan job di background biar page tidak nunggu.
        $path = $this->file_upload->store('galeri', 'public');

        $item = Galeri::create([
            'id_owners' => $this->owner->id,
            'jenis' => $jenis,
            'file' => $path,
            'status' => $jenis === 'foto' ? 'processing' : 'ready',
            'keterangan' => $this->keterangan_form,
            'actor_type' => $this->actorType,
            'actor_id' => $this->actorId,
            'actor_nama' => $this->actorNama,
        ]);

        if ($jenis === 'foto') {
            BuatThumbnailGaleri::dispatch($item->id);
        }

        $this->catat('tambah', "Mengunggah {$jenis} baru ke Galeri");
        $this->siarkanPerubahan();

        $this->showModalUpload = false;
        $this->dispatch('alert-success', message: 'Berhasil diunggah ke Galeri');
    }

    public function openView($id)
    {
        $this->galeriQuery()->findOrFail($id);
        $this->viewId = $id;
        $this->showModalView = true;
    }

    public function openEdit($id)
    {
        $item = $this->galeriQuery()->findOrFail($id);
        if (!$this->bolehKelola($item)) {
            $this->dispatch('alert-error', message: 'Kamu tidak punya izin mengedit item ini.');
            return;
        }
        $this->editId = $item->id;
        $this->keteranganEdit_form = $item->keterangan;
        $this->showModalEdit = true;
    }

    public function update()
    {
        $item = $this->galeriQuery()->findOrFail($this->editId);
        if (!$this->bolehKelola($item)) {
            $this->dispatch('alert-error', message: 'Kamu tidak punya izin mengedit item ini.');
            return;
        }

        $this->validate(['keteranganEdit_form' => 'nullable']);

        $item->update(['keterangan' => $this->keteranganEdit_form]);
        $this->catat('update', "Mengubah keterangan Galeri milik '{$item->actor_nama}'");
        $this->siarkanPerubahan();

        $this->showModalEdit = false;
        $this->dispatch('alert-success', message: 'Keterangan berhasil diperbarui');
    }

    public function delete($id)
    {
        $item = $this->galeriQuery()->findOrFail($id);
        if (!$this->bolehKelola($item)) {
            $this->dispatch('alert-error', message: 'Kamu tidak punya izin menghapus item ini.');
            return;
        }

        $namaPengunggah = $item->actor_nama;
        $jenis = $item->jenis;
        Storage::disk('public')->delete(array_filter([$item->file, $item->thumbnail]));
        $item->delete();

        $this->catat('hapus', "Menghapus {$jenis} di Galeri milik '{$namaPengunggah}'");
        $this->siarkanPerubahan();
        $this->dispatch('alert-success', message: 'Berhasil dihapus dari Galeri');
    }

    public function render()
    {
        $list = $this->galeriQuery()
            ->latest('id')
            ->paginate($this->perPage);

        $selected = $this->viewId ? $this->galeriQuery()->find($this->viewId) : null;

        $logs = ActivityLog::where('id_owners', $this->owner->id)
            ->latest('id')
            ->limit(15)
            ->get();

        return view('livewire.owner.kelola-galeri', [
            'list' => $list,
            'selected' => $selected,
            'logs' => $logs,
        ]);
    }
}
