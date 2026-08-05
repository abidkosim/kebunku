<x-dynamic-component :component="$actorType === 'owner' ? 'owner.shell' : 'staff.shell'" :owner="$owner" active="tanaman-kebun" :logs="$logs" :actor-type="$actorType" :actor-nama="$actorNama" :actor-foto-url="$actorFotoUrl">
    @php $routePrefix = $actorType === 'owner' ? 'owner' : 'portal'; @endphp
    <a href="{{ route($routePrefix.'.tanaman') }}" wire:navigate class="mb-4 flex items-center gap-1.5 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition w-fit">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke Kelola Tanaman
    </a>

    <div class="flex items-center justify-between mb-6 mt-2">
        <div>
            <h1 class="text-xl font-extrabold dark:text-white">Kelola Kebun &amp; Meja</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Atur kebun dan jumlah meja tempat tanaman ditempatkan.</p>
        </div>
        <button wire:click="openCreateKebun" class="btn-primary px-5 py-2.5 rounded-full text-sm font-bold shadow-md transition-all flex items-center gap-1.5 shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Kebun
        </button>
    </div>

    <div class="space-y-6">
        @forelse($list as $kebun)
        <div class="glass-card rounded-2xl shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20 overflow-hidden">
            <div class="p-5 flex items-center justify-between border-b border-slate-200/50 dark:border-slate-700/50">
                <div>
                    <h3 class="font-extrabold text-sm dark:text-white">{{ $kebun->nama_kebun }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $kebun->meja->count() }} meja • {{ $kebun->meja->filter(fn($m) => $m->tanaman->contains(fn($t) => is_null($t->siklus_selesai_at)))->count() }} terpakai</p>
                </div>
                <div class="flex gap-2">
                    <button wire:click="tambahMeja({{ $kebun->id }})" class="text-xs font-bold px-3 py-2 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition">+ Meja</button>
                    <button wire:click="openEditKebun({{ $kebun->id }})" class="w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition">✎</button>
                    <button wire:click="deleteKebun({{ $kebun->id }})" wire:confirm="Yakin hapus kebun {{ $kebun->nama_kebun }}?" class="w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-red-100 dark:hover:bg-red-900/30 hover:text-red-500 dark:hover:text-red-400 transition">✕</button>
                </div>
            </div>
            <div class="p-5 flex flex-wrap gap-3">
                @forelse($kebun->meja->sortBy('nomor') as $meja)
                    @php $tanamanAktif = $meja->tanaman->firstWhere('siklus_selesai_at', null); $terpakai = (bool) $tanamanAktif; @endphp
                    <div class="w-24 rounded-xl p-3 text-center border {{ $terpakai ? 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800' : 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800' }}">
                        <p class="text-xs font-extrabold {{ $terpakai ? 'text-red-700 dark:text-red-400' : 'text-emerald-700 dark:text-emerald-400' }}">Meja {{ $meja->nomor }}</p>
                        <p class="text-[10px] mt-1 truncate {{ $terpakai ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-500' }}">{{ $terpakai ? $tanamanAktif->nama_tanaman : 'Kosong' }}</p>
                        @if(!$terpakai)
                            <button wire:click="hapusMeja({{ $meja->id }})" wire:confirm="Hapus meja {{ $meja->nomor }}?" class="text-[9px] text-slate-400 hover:text-red-500 mt-1">Hapus</button>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-slate-400 dark:text-slate-500">Belum ada meja</p>
                @endforelse
            </div>
        </div>
        @empty
        <div class="glass-card rounded-2xl p-10 text-center text-sm text-slate-400 dark:text-slate-500">Belum ada kebun, klik "Tambah Kebun" untuk mulai.</div>
        @endforelse
    </div>

    @if($showModalKebun)
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div wire:click="$set('showModalKebun', false)" class="modal-backdrop absolute inset-0"></div>
        <div class="modal-content relative w-full sm:max-w-sm bg-white/90 dark:bg-slate-800/95 backdrop-blur-xl rounded-t-2xl sm:rounded-2xl p-6 sm:p-7 shadow-2xl border border-white/50 dark:border-slate-700/50">
            <div class="flex justify-between items-start mb-4">
                <h3 class="font-extrabold text-lg dark:text-white">{{ $isEditModeKebun ? 'Edit Kebun' : 'Tambah Kebun' }}</h3>
                <button type="button" wire:click="$set('showModalKebun', false)" class="w-8 h-8 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center transition text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">✕</button>
            </div>
            <form wire:submit="saveKebun" class="space-y-4">
                <div>
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Nama Kebun</label>
                    <input wire:model="namaKebun_form" placeholder="Contoh: Kebun A" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                    @error('namaKebun_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                @if(!$isEditModeKebun)
                <div>
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Jumlah Meja</label>
                    <input type="number" min="1" wire:model="jumlahMeja_form" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                    @error('jumlahMeja_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Meja akan otomatis dibuat bernomor 1 sampai {{ $jumlahMeja_form ?: 'N' }}.</p>
                </div>
                @endif
                <div class="flex gap-3 pt-2">
                    <button type="button" wire:click="$set('showModalKebun', false)" class="flex-1 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 py-3.5 rounded-xl text-sm font-bold transition dark:text-white">Batal</button>
                    <button type="submit" class="btn-primary flex-1 py-3.5 rounded-xl text-sm font-bold transition-all">{{ $isEditModeKebun ? 'Update' : 'Simpan' }}</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</x-dynamic-component>
