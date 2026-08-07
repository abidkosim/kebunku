<x-dynamic-component :component="$actorType === 'owner' ? 'owner.shell' : 'staff.shell'" :owner="$owner" active="galeri" :logs="$logs" :actor-type="$actorType" :actor-nama="$actorNama" :actor-foto-url="$actorFotoUrl">

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="font-extrabold text-lg flex items-center gap-2 dark:text-white">
                <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 8h16M4 4h16v16H4V4z"/></svg>
                Galeri
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Foto & video kebun dari owner, teknisi, dan keuangan</p>
        </div>
        <button wire:click="openUpload" class="btn-primary px-4 py-2.5 rounded-full text-xs font-bold shadow-md transition-all flex items-center gap-1.5 w-fit">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            Unggah
        </button>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 mb-6">
        @forelse($list as $item)
        <div wire:key="galeri-item-{{ $item->id }}" class="glass-card rounded-2xl overflow-hidden shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20 group">
            <div wire:click="openView({{ $item->id }})" class="relative aspect-square bg-slate-100 dark:bg-slate-800 cursor-pointer overflow-hidden">
                @if($item->jenis === 'foto')
                    <img src="{{ $item->thumbnail_url ?? $item->file_url }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" alt="{{ $item->keterangan }}">
                @else
                    <video src="{{ $item->file_url }}#t=0.1" class="w-full h-full object-cover" preload="metadata" muted playsinline></video>
                    <div class="absolute inset-0 flex items-center justify-center bg-slate-900/20">
                        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-lg">
                            <svg class="w-4 h-4 text-slate-900 ml-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        </div>
                    </div>
                @endif
                @if($item->status === 'processing')
                    <div class="absolute top-2 right-2 text-[9px] font-bold px-2 py-1 rounded-full bg-slate-900/70 text-white flex items-center gap-1">
                        <svg class="w-3 h-3 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Memproses
                    </div>
                @endif
            </div>
            <div class="p-3">
                <p class="text-xs font-bold dark:text-white truncate">{{ $item->actor_nama }}</p>
                <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">{{ $item->created_at->diffForHumans() }}</p>
            </div>
        </div>
        @empty
        <div class="col-span-full glass-card rounded-2xl p-10 text-center text-sm text-slate-400 dark:text-slate-500">
            Belum ada foto/video. Klik "Unggah" untuk menambahkan.
        </div>
        @endforelse
    </div>

    @if($list->total() > 0)
    <div class="flex items-center justify-center gap-2 mb-6">
        <button wire:click="previousPage" @disabled($list->onFirstPage()) class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <span class="text-xs font-semibold text-slate-600 dark:text-slate-300 px-2">Hal {{ $list->currentPage() }} / {{ $list->lastPage() }}</span>
        <button wire:click="nextPage" @disabled(!$list->hasMorePages()) class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>
    </div>
    @endif

    {{-- MODAL UPLOAD --}}
    @if($showModalUpload)
    <div x-data="{ uploading: false, progress: 0 }"
         x-on:livewire-upload-start.window="uploading = true; progress = 0"
         x-on:livewire-upload-progress.window="progress = $event.detail.progress"
         x-on:livewire-upload-finish.window="uploading = false; progress = 100"
         x-on:livewire-upload-error.window="uploading = false"
         x-on:livewire-upload-cancel.window="uploading = false"
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div wire:click="$set('showModalUpload', false)" class="modal-backdrop absolute inset-0"></div>
        <div class="modal-content relative w-full sm:max-w-sm bg-white dark:bg-slate-800 rounded-t-2xl sm:rounded-2xl p-6 sm:p-7 shadow-2xl border border-white/50 dark:border-slate-700/50">
            <div class="flex justify-between items-start mb-4">
                <h3 class="font-extrabold text-lg dark:text-white">Unggah Foto/Video</h3>
                <button type="button" wire:click="$set('showModalUpload', false)" class="w-8 h-8 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center transition text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">✕</button>
            </div>
            <form wire:submit="simpanUpload" class="space-y-4">
                <div>
                    <label class="cursor-pointer flex flex-col items-center justify-center gap-2 border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-xl p-6 text-center hover:border-slate-400 dark:hover:border-slate-500 transition">
                        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400">
                            @if($file_upload) {{ $file_upload->getClientOriginalName() }} @else Pilih foto atau video @endif
                        </span>
                        <input type="file" wire:model="file_upload" accept="image/*,video/*" class="hidden">
                    </label>
                    <div x-show="uploading" class="mt-2" style="display:none;">
                        <div class="w-full h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-full bg-slate-900 dark:bg-emerald-500 transition-all duration-150" :style="`width: ${progress}%`"></div>
                        </div>
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1" x-text="`Mengunggah... ${progress}%`"></p>
                    </div>
                    @error('file_upload') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1.5">JPG/PNG/GIF/WEBP atau MP4/MOV/AVI/WEBM, maks 50MB</p>
                </div>
                @if($file_upload)
                    <div class="rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-700 aspect-video flex items-center justify-center">
                        @if($file_upload->isPreviewable())
                            @if(str_starts_with($file_upload->getMimeType() ?? '', 'video/'))
                                <video src="{{ $file_upload->temporaryUrl() }}" class="max-h-full max-w-full" controls></video>
                            @else
                                <img src="{{ $file_upload->temporaryUrl() }}" class="max-h-full max-w-full object-contain">
                            @endif
                        @else
                            <p class="text-xs text-slate-400 dark:text-slate-500 p-4 text-center">Preview belum tersedia untuk format ini, tapi tetap bisa diunggah.</p>
                        @endif
                    </div>
                @endif
                <div>
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Keterangan (opsional)</label>
                    <textarea wire:model="keterangan_form" rows="2" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none"></textarea>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" wire:click="$set('showModalUpload', false)" class="flex-1 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 py-3.5 rounded-xl text-sm font-bold transition dark:text-white">Batal</button>
                    <button type="submit" :disabled="uploading" :class="{ 'opacity-50 cursor-not-allowed': uploading }" class="btn-primary flex-1 py-3.5 rounded-xl text-sm font-bold transition-all">
                        <span x-show="!uploading">Unggah</span>
                        <span x-show="uploading" style="display:none;">Menunggu file selesai...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- MODAL VIEW (lightbox) --}}
    @if($showModalView && $selected)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div wire:click="$set('showModalView', false)" class="modal-backdrop absolute inset-0"></div>
        <div class="modal-content relative w-full max-w-lg bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-white/50 dark:border-slate-700/50 overflow-hidden">
            <div class="bg-black flex items-center justify-center max-h-[70vh]">
                @if($selected->jenis === 'foto')
                    <img src="{{ $selected->file_url }}" class="max-h-[70vh] w-full object-contain">
                @else
                    <video src="{{ $selected->file_url }}" class="max-h-[70vh] w-full" controls autoplay muted playsinline></video>
                @endif
            </div>
            <div class="p-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm font-bold dark:text-white">{{ $selected->actor_nama }}</p>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">{{ $selected->created_at->diffForHumans() }} • {{ ucfirst($selected->actor_type) }}</p>
                    </div>
                    <button type="button" wire:click="$set('showModalView', false)" class="w-8 h-8 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center transition text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white shrink-0">✕</button>
                </div>
                @if($selected->status === 'processing')
                    <p class="text-xs text-amber-600 dark:text-amber-400 mt-2">Sedang memproses thumbnail di background...</p>
                @endif
                @if($selected->keterangan)
                    <p class="text-sm text-slate-600 dark:text-slate-300 mt-3">{{ $selected->keterangan }}</p>
                @endif
                @if($this->bolehKelola($selected))
                <div class="flex gap-2 mt-4 pt-4 border-t border-slate-200/50 dark:border-slate-700/50">
                    <button wire:click="openEdit({{ $selected->id }})" class="flex-1 text-xs font-bold px-3 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition">Edit Keterangan</button>
                    <button wire:click="delete({{ $selected->id }})" wire:confirm="Hapus item ini dari galeri?" class="flex-1 text-xs font-bold px-3 py-2.5 rounded-xl bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/50 transition">Hapus</button>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- MODAL EDIT --}}
    @if($showModalEdit)
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div wire:click="$set('showModalEdit', false)" class="modal-backdrop absolute inset-0"></div>
        <div class="modal-content relative w-full sm:max-w-sm bg-white dark:bg-slate-800 rounded-t-2xl sm:rounded-2xl p-6 sm:p-7 shadow-2xl border border-white/50 dark:border-slate-700/50">
            <div class="flex justify-between items-start mb-4">
                <h3 class="font-extrabold text-lg dark:text-white">Edit Keterangan</h3>
                <button type="button" wire:click="$set('showModalEdit', false)" class="w-8 h-8 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center transition text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">✕</button>
            </div>
            <form wire:submit="update" class="space-y-4">
                <div>
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Keterangan</label>
                    <textarea wire:model="keteranganEdit_form" rows="3" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none"></textarea>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" wire:click="$set('showModalEdit', false)" class="flex-1 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 py-3.5 rounded-xl text-sm font-bold transition dark:text-white">Batal</button>
                    <button type="submit" class="btn-primary flex-1 py-3.5 rounded-xl text-sm font-bold transition-all">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</x-dynamic-component>
