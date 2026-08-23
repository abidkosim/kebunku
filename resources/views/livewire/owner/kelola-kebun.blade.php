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
                    @if($kebun->punya_koordinat)
                        <a href="{{ $kebun->koordinat_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-600 dark:text-emerald-400 mt-1.5">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0zM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                            Koordinat tersimpan
                        </a>
                    @else
                        <p class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-600 dark:text-amber-400 mt-1.5">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                            Koordinat belum diisi - Absensi terkunci untuk kebun ini
                        </p>
                    @endif
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
    <div x-data="{
            mencari: false,
            ambilLokasi() {
                if (!navigator.geolocation) { return; }
                this.mencari = true;
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        $wire.set('lat_form', pos.coords.latitude);
                        $wire.set('lng_form', pos.coords.longitude);
                        this.mencari = false;
                    },
                    () => { this.mencari = false; },
                    { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
                );
            }
         }"
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div wire:click="$set('showModalKebun', false)" class="modal-backdrop absolute inset-0"></div>
        <div class="modal-content relative w-full sm:max-w-sm bg-white dark:bg-slate-800 rounded-t-2xl sm:rounded-2xl p-6 sm:p-7 shadow-2xl border border-white/50 dark:border-slate-700/50 max-h-[90vh] overflow-y-auto">
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

                <div class="rounded-xl border border-slate-200 dark:border-slate-600 p-4">
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Koordinat Kebun (opsional)</label>
                        <button type="button" x-on:click="ambilLokasi()" :disabled="mencari" class="shrink-0 text-[10px] font-bold px-2.5 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center gap-1 disabled:opacity-50">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0zM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                            <span x-text="mencari ? 'Mencari...' : 'Pakai Lokasi Saya'"></span>
                        </button>
                    </div>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mb-2">Berdiri di kebun ini lalu klik tombol di atas, atau isi manual. Dipakai untuk validasi radius 20m fitur Absensi Teknisi.</p>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <input type="number" step="any" wire:model="lat_form" placeholder="Latitude" class="input-fancy w-full px-3 py-2.5 rounded-xl text-xs outline-none">
                        </div>
                        <div>
                            <input type="number" step="any" wire:model="lng_form" placeholder="Longitude" class="input-fancy w-full px-3 py-2.5 rounded-xl text-xs outline-none">
                        </div>
                    </div>
                    @error('lat_form') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                    @error('lng_form') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" wire:click="$set('showModalKebun', false)" class="flex-1 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 py-3.5 rounded-xl text-sm font-bold transition dark:text-white">Batal</button>
                    <button type="submit" class="btn-primary flex-1 py-3.5 rounded-xl text-sm font-bold transition-all">{{ $isEditModeKebun ? 'Update' : 'Simpan' }}</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</x-dynamic-component>
