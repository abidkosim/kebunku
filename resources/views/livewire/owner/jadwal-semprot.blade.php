<x-dynamic-component :component="$actorType === 'owner' ? 'owner.shell' : 'staff.shell'" :owner="$owner" active="tanaman-semprot" :logs="$logs" :actor-type="$actorType" :actor-nama="$actorNama" :actor-foto-url="$actorFotoUrl">
    <div class="glass-card rounded-2xl shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20 border-slate-200/60 dark:border-slate-700/50 overflow-hidden">
        <div class="p-5 lg:p-6 flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-200/50 dark:border-slate-700/50">
            <div>
                <h3 class="font-extrabold text-lg flex items-center gap-2 dark:text-white">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v3m6.36.64l-2.12 2.12M21 12h-3m.36 6.36l-2.12-2.12M12 21v-3m-6.36-.64l2.12-2.12M3 12h3m-.36-6.36l2.12 2.12M12 12a3 3 0 100 6 3 3 0 000-6z"/></svg>
                    Jadwal Semprot
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 hidden sm:block">Aktivitas semprot lintas semua tanaman</p>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full lg:w-auto">
                <div class="relative flex-1 lg:w-64">
                    <input wire:model.live.debounce.300ms="search" placeholder="Cari nama tanaman..." class="input-fancy w-full lg:w-[260px] pl-10 pr-4 py-2.5 rounded-full text-[13px] outline-none">
                    <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <button wire:click="openCreate" class="btn-primary px-5 py-2.5 rounded-full text-sm font-bold shadow-md transition-all flex items-center gap-1.5 justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Semprot
                </button>
            </div>
        </div>

        <div class="hidden md:block table-scroll">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr class="text-[10px] mono tracking-[0.1em] text-slate-500 dark:text-slate-400">
                            <th scope="col" class="px-6 py-4 text-left">TANAMAN</th>
                            <th scope="col" class="px-6 py-4 text-left">TANGGAL RENCANA</th>
                            <th scope="col" class="px-6 py-4 text-left">PROGRESS</th>
                            <th scope="col" class="px-6 py-4 text-left">STATUS</th>
                            <th scope="col" class="px-6 py-4 text-left">CATATAN</th>
                            <th scope="col" class="px-6 py-4 text-right">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($list as $item)
                        <tr class="group transition-all duration-200 dark:hover:bg-slate-800/30">
                            <td class="px-6 py-4 align-middle text-sm font-bold dark:text-white">{{ $item->tanaman->nama_tanaman }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 align-middle">{{ $item->tanggal_rencana->format('d M Y') }}</td>
                            <td class="px-6 py-4 align-middle" style="min-width:150px">
                                <x-owner.progress-bar :tahap="$item" fallback="Selesai" />
                            </td>
                            <td class="px-6 py-4 align-middle">
                                <span class="text-[10px] font-bold px-2.5 py-1 rounded-full {{ $item->status === 'selesai' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' }}">{{ $item->status === 'selesai' ? 'Selesai' : 'Belum' }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400 align-middle">{{ $item->catatan ?: '-' }}</td>
                            <td class="px-6 py-4 align-middle">
                                <div class="flex justify-end gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                    <button wire:click="openEdit({{ $item->id }})" aria-label="Edit" class="action-btn w-8 h-8 rounded-full bg-white/70 dark:bg-slate-700/70 border border-slate-200 dark:border-slate-600 hover:bg-slate-900 dark:hover:bg-slate-600 hover:text-white dark:hover:text-white hover:border-slate-900 dark:hover:border-slate-500 shadow-sm flex items-center justify-center transition-all dark:text-slate-300">✎</button>
                                    <button wire:click="delete({{ $item->id }})" wire:confirm="Hapus jadwal semprot ini?" aria-label="Hapus" class="action-btn delete w-8 h-8 rounded-full bg-white/70 dark:bg-slate-700/70 border border-slate-200 dark:border-slate-600 hover:bg-red-500 hover:text-white hover:border-red-500 shadow-sm flex items-center justify-center transition-all dark:text-slate-300">✕</button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-6 py-10 text-center text-sm text-slate-400 dark:text-slate-500">Belum ada jadwal semprot.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="md:hidden divide-y divide-slate-100/70 dark:divide-slate-700/50">
            @forelse($list as $item)
            <div class="p-5 flex items-center justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold dark:text-white">{{ $item->tanaman->nama_tanaman }}</p>
                    <p class="text-[10px] mono text-slate-500 dark:text-slate-400">{{ $item->tanggal_rencana->format('d M Y') }} • {{ $item->status === 'selesai' ? 'Selesai' : 'Belum' }}</p>
                    @if($item->status !== 'selesai')
                        <div class="mt-2 max-w-[180px]"><x-owner.progress-bar :tahap="$item" /></div>
                    @endif
                </div>
                <div class="flex gap-2">
                    <button wire:click="openEdit({{ $item->id }})" aria-label="Edit" class="w-9 h-9 rounded-full bg-slate-100/70 dark:bg-slate-700/50 flex items-center justify-center hover:bg-slate-200 dark:hover:bg-slate-600 transition dark:text-slate-300">✎</button>
                    <button wire:click="delete({{ $item->id }})" wire:confirm="Hapus?" aria-label="Hapus" class="w-9 h-9 rounded-full bg-slate-100/70 dark:bg-slate-700/50 flex items-center justify-center hover:bg-red-100 dark:hover:bg-red-900/30 hover:text-red-500 dark:hover:text-red-400 transition dark:text-slate-300">✕</button>
                </div>
            </div>
            @empty
            <div class="p-10 text-center text-sm text-slate-400 dark:text-slate-500">Belum ada jadwal semprot</div>
            @endforelse
        </div>

        @if($list->total() > 0)
        <div class="p-4 lg:px-6 flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-slate-200/50 dark:border-slate-700/50">
            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                <span>Tampilkan</span>
                <select wire:model.live="perPage" class="input-fancy px-3 py-1.5 rounded-lg text-xs outline-none w-auto">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span>dari {{ $list->total() }} jadwal</span>
            </div>
            <div class="flex items-center gap-2">
                <button wire:click="previousPage" @disabled($list->onFirstPage()) class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <span class="text-xs font-semibold text-slate-600 dark:text-slate-300 px-2">Hal {{ $list->currentPage() }} / {{ $list->lastPage() }}</span>
                <button wire:click="nextPage" @disabled(!$list->hasMorePages()) class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>
        @endif
    </div>

    {{-- MODAL SEMPROT --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div wire:click="$set('showModal', false)" class="modal-backdrop absolute inset-0"></div>
        <div class="modal-content relative w-full sm:max-w-md bg-white/90 dark:bg-slate-800/95 backdrop-blur-xl rounded-t-2xl sm:rounded-2xl p-6 sm:p-7 shadow-2xl border border-white/50 dark:border-slate-700/50">
            <div class="flex justify-between items-start mb-4">
                <h3 class="font-extrabold text-lg dark:text-white">{{ $isNew ? 'Tambah Jadwal Semprot' : 'Edit Jadwal Semprot' }}</h3>
                <button type="button" wire:click="$set('showModal', false)" class="w-8 h-8 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center transition text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">✕</button>
            </div>
            <form wire:submit="save" class="space-y-4">
                @if($isNew)
                <div>
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Tanaman</label>
                    <select wire:model="tanamanId_form" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                        <option value="">Pilih tanaman</option>
                        @foreach($tanamanAktif as $t)
                            <option value="{{ $t->id }}">{{ $t->nama_tanaman }} ({{ $t->meja->kebun->nama_kebun }} - Meja {{ $t->meja->nomor }})</option>
                        @endforeach
                    </select>
                    @error('tanamanId_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                @endif
                <div>
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Tanggal Rencana</label>
                    <input type="date" wire:model="tanggalRencana_form" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                    @error('tanggalRencana_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Status</label>
                    <select wire:model="status_form" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                        <option value="belum">Belum</option>
                        <option value="selesai">Selesai</option>
                    </select>
                </div>
                @if($status_form === 'selesai')
                <div>
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Tanggal Selesai</label>
                    <input type="date" wire:model="tanggalSelesai_form" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                </div>
                @endif
                <div>
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Catatan</label>
                    <textarea wire:model="catatan_form" rows="2" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none"></textarea>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" wire:click="$set('showModal', false)" class="flex-1 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 py-3.5 rounded-xl text-sm font-bold transition dark:text-white">Batal</button>
                    <button type="submit" class="btn-primary flex-1 py-3.5 rounded-xl text-sm font-bold transition-all">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <x-owner.modal-peringatan-tenggat
        :show="$showPeringatanTenggat"
        :items="$itemPeringatanTenggat"
        close-method="tutupPeringatanTenggat"
        title="Jadwal Semprot Hampir Habis Waktu"
    />
</x-dynamic-component>
