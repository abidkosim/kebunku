<x-dynamic-component :component="$actorType === 'owner' ? 'owner.shell' : 'staff.shell'" :owner="$owner" active="keuangan" :logs="$logs" :actor-type="$actorType" :actor-nama="$actorNama" :actor-foto-url="$actorFotoUrl">

    <div class="mb-6 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h3 class="font-extrabold text-lg flex items-center gap-2 dark:text-white">
                <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v1m0 8v1m0-1v-1m0 4v1m0-1v-1M4.5 19.5h15A1.5 1.5 0 0021 18V6a1.5 1.5 0 00-1.5-1.5h-15A1.5 1.5 0 003 6v12a1.5 1.5 0 001.5 1.5z"/></svg>
                Keuangan
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Catatan pemasukan &amp; pengeluaran umum (di luar pendapatan panen)</p>
        </div>
        <button wire:click="openCreate" class="btn-primary px-4 py-2.5 rounded-full text-xs font-bold shadow-md transition-all flex items-center gap-1.5 w-fit">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Catatan
        </button>
    </div>

    {{-- Filter periode --}}
    <div class="glass-card rounded-2xl p-4 mb-6 flex flex-col sm:flex-row sm:items-center gap-3">
        <div class="flex items-center gap-2 flex-1">
            <input type="date" wire:model.live="dariTanggal" class="input-fancy px-3 py-2 rounded-xl text-xs outline-none w-full">
            <span class="text-xs text-slate-400">s/d</span>
            <input type="date" wire:model.live="sampaiTanggal" class="input-fancy px-3 py-2 rounded-xl text-xs outline-none w-full">
        </div>
        <div class="flex items-center gap-2">
            <button wire:click="setPeriode('bulan-ini')" class="text-xs font-bold px-3 py-2 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition whitespace-nowrap">Bulan Ini</button>
            <button wire:click="setPeriode('tahun-ini')" class="text-xs font-bold px-3 py-2 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition whitespace-nowrap">Tahun Ini</button>
            <button wire:click="setPeriode('semua')" class="text-xs font-bold px-3 py-2 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition whitespace-nowrap">Semua</button>
        </div>
    </div>

    {{-- Ringkasan periode --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="glass-card rounded-2xl p-5 shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20">
            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Pemasukan</p>
            <p class="text-lg font-extrabold text-emerald-600 dark:text-emerald-400 mt-1.5">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</p>
        </div>
        <div class="glass-card rounded-2xl p-5 shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20">
            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Pengeluaran</p>
            <p class="text-lg font-extrabold text-red-600 dark:text-red-400 mt-1.5">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</p>
        </div>
        <div class="glass-card rounded-2xl p-5 shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20">
            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Saldo</p>
            <p class="text-lg font-extrabold {{ $saldo >= 0 ? 'text-slate-800 dark:text-white' : 'text-red-600 dark:text-red-400' }} mt-1.5">Rp {{ number_format($saldo, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="glass-card rounded-2xl shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20 overflow-hidden">
        <div class="hidden md:block table-scroll">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr class="text-[10px] mono tracking-[0.1em] text-slate-500 dark:text-slate-400">
                            <th scope="col" class="px-6 py-4 text-left">TANGGAL</th>
                            <th scope="col" class="px-6 py-4 text-left">JENIS</th>
                            <th scope="col" class="px-6 py-4 text-left">KATEGORI</th>
                            <th scope="col" class="px-6 py-4 text-left">JUMLAH</th>
                            <th scope="col" class="px-6 py-4 text-left">DICATAT OLEH</th>
                            <th scope="col" class="px-6 py-4 text-right">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($list as $item)
                        <tr>
                            <td class="px-6 py-4 align-middle text-sm text-slate-600 dark:text-slate-300">{{ $item->tanggal->format('d M Y') }}</td>
                            <td class="px-6 py-4 align-middle">
                                <span class="text-[10px] font-bold px-2.5 py-1 rounded-full {{ $item->jenis === 'pemasukan' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">{{ ucfirst($item->jenis) }}</span>
                            </td>
                            <td class="px-6 py-4 align-middle text-sm font-bold dark:text-white">{{ $item->kategori }}</td>
                            <td class="px-6 py-4 align-middle text-sm mono {{ $item->jenis === 'pemasukan' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">Rp {{ number_format($item->jumlah, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 align-middle text-xs text-slate-500 dark:text-slate-400">{{ $item->dicatat_oleh }}</td>
                            <td class="px-6 py-4 align-middle">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="openEdit({{ $item->id }})" class="text-xs font-bold px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition">Edit</button>
                                    <button wire:click="delete({{ $item->id }})" wire:confirm="Hapus catatan ini?" class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-red-100 dark:hover:bg-red-900/30 hover:text-red-500 dark:hover:text-red-400 transition">✕</button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-6 py-10 text-center text-sm text-slate-400 dark:text-slate-500">Belum ada catatan keuangan di periode ini</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="md:hidden divide-y divide-slate-100/70 dark:divide-slate-700/50">
            @forelse($list as $item)
            <div class="p-4 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-sm font-bold dark:text-white flex items-center gap-2 flex-wrap">
                        {{ $item->kategori }}
                        <span class="text-[9px] font-bold px-2 py-0.5 rounded-full {{ $item->jenis === 'pemasukan' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">{{ ucfirst($item->jenis) }}</span>
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $item->tanggal->format('d M Y') }} • {{ $item->dicatat_oleh }}</p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <span class="text-sm font-bold {{ $item->jenis === 'pemasukan' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">Rp {{ number_format($item->jumlah, 0, ',', '.') }}</span>
                    <button wire:click="openEdit({{ $item->id }})" class="text-xs font-bold px-2 py-1 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">Edit</button>
                </div>
            </div>
            @empty
            <div class="p-10 text-center text-sm text-slate-400 dark:text-slate-500">Belum ada catatan keuangan di periode ini</div>
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
                <span>dari {{ $list->total() }} catatan</span>
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

    {{-- MODAL TAMBAH/EDIT --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div wire:click="$set('showModal', false)" class="modal-backdrop absolute inset-0"></div>
        <div class="modal-content relative w-full sm:max-w-sm bg-white/90 dark:bg-slate-800/95 backdrop-blur-xl rounded-t-2xl sm:rounded-2xl p-6 sm:p-7 shadow-2xl border border-white/50 dark:border-slate-700/50">
            <div class="flex justify-between items-start mb-4">
                <h3 class="font-extrabold text-lg dark:text-white">{{ $isEditMode ? 'Edit Catatan' : 'Tambah Catatan' }}</h3>
                <button type="button" wire:click="$set('showModal', false)" class="w-8 h-8 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center transition text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">✕</button>
            </div>
            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Jenis</label>
                    <div class="grid grid-cols-2 gap-2 mt-1.5">
                        @foreach(['pengeluaran' => 'Pengeluaran', 'pemasukan' => 'Pemasukan'] as $val => $label)
                        <label class="flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl border text-xs font-bold cursor-pointer transition {{ $jenis_form === $val ? 'bg-slate-900 dark:bg-slate-600 text-white border-slate-900 dark:border-slate-600' : 'bg-white dark:bg-slate-700/50 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-600' }}">
                            <input type="radio" wire:model.live="jenis_form" value="{{ $val }}" class="hidden">
                            {{ $label }}
                        </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Kategori</label>
                    <select wire:model="kategori_form" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                        <option value="">Pilih kategori</option>
                        @foreach($this->kategoriOptions() as $opt)
                            <option value="{{ $opt }}">{{ $opt }}</option>
                        @endforeach
                    </select>
                    @error('kategori_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Jumlah (Rp)</label>
                        <input type="number" step="0.01" min="0.01" wire:model="jumlah_form" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                        @error('jumlah_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Tanggal</label>
                        <input type="date" wire:model="tanggal_form" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                        @error('tanggal_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
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
</x-dynamic-component>
