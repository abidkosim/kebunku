<x-dynamic-component :component="$actorType === 'owner' ? 'owner.shell' : 'staff.shell'" :owner="$owner" active="tanaman-kelola" :logs="$logs" :actor-type="$actorType" :actor-nama="$actorNama" :actor-foto-url="$actorFotoUrl">

    @php $routePrefix = $actorType === 'owner' ? 'owner' : 'portal'; @endphp

    @if(!$selected)
    {{-- ===================== LIST VIEW ===================== --}}
    <div class="glass-card rounded-2xl shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20 border-slate-200/60 dark:border-slate-700/50 overflow-hidden">
        <div class="p-5 lg:p-6 flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-200/50 dark:border-slate-700/50">
            <div>
                <h3 class="font-extrabold text-lg flex items-center gap-2 dark:text-white">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3c-2.5 2.5-4 5-4 8a4 4 0 008 0c0-3-1.5-5.5-4-8z"/></svg>
                    Kelola Tanaman
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 hidden sm:block">Siklus pertumbuhan: semai, peremajaan, pendewasaan, sampai siap panen</p>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full lg:w-auto">
                <div class="relative flex-1 lg:w-64">
                    <input wire:model.live.debounce.300ms="search" placeholder="Cari nama tanaman..." class="input-fancy w-full lg:w-[260px] pl-10 pr-4 py-2.5 rounded-full text-[13px] outline-none">
                    <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <button wire:click="openCreateTanaman" class="btn-primary px-5 py-2.5 rounded-full text-sm font-bold shadow-md transition-all flex items-center gap-1.5 justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Tanaman
                </button>
            </div>
        </div>

        <div class="hidden md:block table-scroll">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr class="text-[10px] mono tracking-[0.1em] text-slate-500 dark:text-slate-400">
                            <th scope="col" class="px-6 py-4 text-left">NAMA TANAMAN</th>
                            <th scope="col" class="px-6 py-4 text-left">KEBUN / MEJA</th>
                            <th scope="col" class="px-6 py-4 text-left">STATUS</th>
                            <th scope="col" class="px-6 py-4 text-left">PROGRESS</th>
                            <th scope="col" class="px-6 py-4 text-left">DIBUAT</th>
                            <th scope="col" class="px-6 py-4 text-right">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($list as $item)
                        <tr class="group transition-all duration-200 dark:hover:bg-slate-800/30 cursor-pointer" wire:click="viewDetail({{ $item->id }})">
                            <td class="px-6 py-4 align-middle text-sm font-bold dark:text-white">{{ $item->nama_tanaman }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 align-middle">{{ $item->meja->kebun->nama_kebun }} • Meja {{ $item->meja->nomor }}</td>
                            <td class="px-6 py-4 align-middle">
                                <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">{{ $item->status }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm mono text-slate-500 dark:text-slate-400 align-middle">{{ $item->progress }}</td>
                            <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400 align-middle">{{ $item->created_at->format('d M Y') }}</td>
                            <td class="px-6 py-4 align-middle" onclick="event.stopPropagation()">
                                <div class="flex justify-end gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                    <button wire:click="openEditTanaman({{ $item->id }})" aria-label="Edit tanaman" class="action-btn w-8 h-8 rounded-full bg-white/70 dark:bg-slate-700/70 border border-slate-200 dark:border-slate-600 hover:bg-slate-900 dark:hover:bg-slate-600 hover:text-white dark:hover:text-white hover:border-slate-900 dark:hover:border-slate-500 shadow-sm flex items-center justify-center transition-all dark:text-slate-300">✎</button>
                                    <button wire:click="deleteTanaman({{ $item->id }})" wire:confirm="Yakin hapus tanaman {{ $item->nama_tanaman }}? Semua tahap & jadwalnya juga akan terhapus." aria-label="Hapus tanaman" class="action-btn delete w-8 h-8 rounded-full bg-white/70 dark:bg-slate-700/70 border border-slate-200 dark:border-slate-600 hover:bg-red-500 hover:text-white hover:border-red-500 shadow-sm flex items-center justify-center transition-all dark:text-slate-300">✕</button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-6 py-10 text-center text-sm text-slate-400 dark:text-slate-500">Belum ada tanaman, klik "Tambah Tanaman" untuk mulai.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="md:hidden divide-y divide-slate-100/70 dark:divide-slate-700/50">
            @forelse($list as $item)
            <div class="p-5 flex items-center justify-between gap-4 hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition cursor-pointer" wire:click="viewDetail({{ $item->id }})">
                <div>
                    <p class="text-sm font-bold dark:text-white">{{ $item->nama_tanaman }}</p>
                    <p class="text-[10px] mono text-slate-500 dark:text-slate-400">{{ $item->meja->kebun->nama_kebun }} • Meja {{ $item->meja->nomor }} • {{ $item->status }}</p>
                </div>
                <div class="flex gap-2" onclick="event.stopPropagation()">
                    <button wire:click="openEditTanaman({{ $item->id }})" aria-label="Edit" class="w-9 h-9 rounded-full bg-slate-100/70 dark:bg-slate-700/50 flex items-center justify-center hover:bg-slate-200 dark:hover:bg-slate-600 transition dark:text-slate-300">✎</button>
                    <button wire:click="deleteTanaman({{ $item->id }})" wire:confirm="Hapus?" aria-label="Hapus" class="w-9 h-9 rounded-full bg-slate-100/70 dark:bg-slate-700/50 flex items-center justify-center hover:bg-red-100 dark:hover:bg-red-900/30 hover:text-red-500 dark:hover:text-red-400 transition dark:text-slate-300">✕</button>
                </div>
            </div>
            @empty
            <div class="p-10 text-center text-sm text-slate-400 dark:text-slate-500">Belum ada tanaman</div>
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
                <span>dari {{ $list->total() }} tanaman</span>
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

    @else
    {{-- ===================== DETAIL VIEW ===================== --}}
    @php
        // Halaman ini cuma mengelola siklus pertumbuhan sampai "Siap Panen" - tahap panen itu sendiri dikelola di halaman Panen.
        $tahapanUrut = $selected->tahapans->whereNotIn('jenis', ['panen'])->sortBy('id')->values();
        $latestTahap = $tahapanUrut->last();
        $sudahMulaiPanen = $selected->tahapans->contains('jenis', 'panen');
        $labelJenis = ['semai' => 'Semai (Persemaian)', 'peremajaan' => 'Peremajaan', 'pendewasaan' => 'Pendewasaan'];
    @endphp

    <button wire:click="backToList" class="mb-4 flex items-center gap-1.5 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke Daftar
    </button>

    <div class="glass-card rounded-2xl shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20 p-6 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-extrabold dark:text-white">{{ $selected->nama_tanaman }}</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $selected->meja->kebun->nama_kebun }} • Meja {{ $selected->meja->nomor }} • <span class="font-semibold">{{ $selected->status }}</span></p>
            @if($selected->catatan)
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-2">{{ $selected->catatan }}</p>
            @endif
        </div>
        <div class="flex gap-2">
            <button wire:click="openEditTanaman({{ $selected->id }})" class="w-9 h-9 rounded-full bg-slate-100/70 dark:bg-slate-700/50 flex items-center justify-center hover:bg-slate-200 dark:hover:bg-slate-600 transition dark:text-slate-300">✎</button>
            <button wire:click="deleteTanaman({{ $selected->id }})" wire:confirm="Yakin hapus tanaman ini? Semua tahap & jadwalnya juga akan terhapus." class="w-9 h-9 rounded-full bg-slate-100/70 dark:bg-slate-700/50 flex items-center justify-center hover:bg-red-100 dark:hover:bg-red-900/30 hover:text-red-500 dark:hover:text-red-400 transition dark:text-slate-300">✕</button>
        </div>
    </div>

    {{-- Riwayat tahap pertumbuhan --}}
    <div class="space-y-4 mb-6">
        @if($tahapanUrut->isEmpty())
            <div class="glass-card rounded-2xl p-8 text-center">
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Tanaman ini belum memulai siklus pertumbuhan.</p>
                <button wire:click="openMulaiTahap('semai')" class="btn-primary px-6 py-3 rounded-xl text-sm font-bold transition-all">Mulai Semai</button>
            </div>
        @else
            @foreach($tahapanUrut as $tahap)
            <div class="glass-card rounded-2xl p-5 shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-extrabold dark:text-white">{{ $labelJenis[$tahap->jenis] }}</p>
                    <span class="text-[9px] font-bold px-2 py-0.5 rounded-full {{ $tahap->status === 'selesai' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' }}">{{ $tahap->status === 'selesai' ? 'Selesai' : 'Berjalan' }}</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <div>
                        <p class="text-slate-400 dark:text-slate-500">Jumlah Awal</p>
                        <p class="font-bold text-slate-700 dark:text-slate-200 mt-0.5">{{ $tahap->jumlah_awal }} tanaman</p>
                    </div>
                    <div>
                        <p class="text-slate-400 dark:text-slate-500">Mulai</p>
                        <p class="font-bold text-slate-700 dark:text-slate-200 mt-0.5">{{ $tahap->tanggal_mulai->format('d M Y') }}</p>
                    </div>
                    <div>
                        <p class="text-slate-400 dark:text-slate-500">Durasi Rencana</p>
                        <p class="font-bold text-slate-700 dark:text-slate-200 mt-0.5">{{ $tahap->durasi_rencana ? $tahap->durasi_rencana.' hari' : 'Open-ended' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-400 dark:text-slate-500">Target Selesai</p>
                        <p class="font-bold text-slate-700 dark:text-slate-200 mt-0.5">{{ $tahap->tanggal_selesai_rencana?->format('d M Y') ?? '-' }}</p>
                    </div>
                </div>
                @if($tahap->status === 'selesai')
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs mt-3 pt-3 border-t border-slate-200/50 dark:border-slate-700/50">
                    <div>
                        <p class="text-slate-400 dark:text-slate-500">Selesai Aktual</p>
                        <p class="font-bold text-slate-700 dark:text-slate-200 mt-0.5">{{ $tahap->tanggal_selesai_aktual?->format('d M Y') ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-400 dark:text-slate-500">Lolos / Hidup</p>
                        <p class="font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">{{ $tahap->jumlah_lolos }} tanaman</p>
                    </div>
                    <div>
                        <p class="text-slate-400 dark:text-slate-500">Mati</p>
                        <p class="font-bold {{ $tahap->jumlah_mati > 0 ? 'text-red-600 dark:text-red-400' : 'text-slate-700 dark:text-slate-200' }} mt-0.5">{{ $tahap->jumlah_mati }} tanaman</p>
                    </div>
                    <div>
                        <p class="text-slate-400 dark:text-slate-500">Kondisi</p>
                        <p class="mt-0.5">
                            @if($tahap->lengkap)
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Lengkap</span>
                            @else
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Berkurang {{ $tahap->jumlah_mati }}</span>
                            @endif
                        </p>
                    </div>
                </div>
                @endif
                @if($tahap->catatan)
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-3">{{ $tahap->catatan }}</p>
                @endif
                <div class="flex gap-2 mt-4">
                    @if($tahap->status === 'berjalan')
                        <button wire:click="openTandaiSelesai({{ $tahap->id }})" class="btn-primary text-xs font-bold px-4 py-2 rounded-lg transition-all">{{ $tahap->jenis === 'panen' ? 'Tutup Siklus Panen' : 'Tandai Selesai' }}</button>
                    @elseif($tahap->id === $latestTahap->id)
                        <button wire:click="batalkanSelesaiTahap({{ $tahap->id }})" class="text-xs font-bold px-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition">Batalkan Selesai</button>
                    @endif
                    <button wire:click="openEditTahap({{ $tahap->id }})" class="text-xs font-bold px-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition">Edit</button>
                </div>
            </div>
            @endforeach

            {{-- Aksi lanjutan setelah tahap terakhir selesai --}}
            @if($latestTahap->status === 'selesai')
                @if($latestTahap->jenis === 'semai')
                    <div class="glass-card rounded-2xl p-5 flex flex-col sm:flex-row gap-3">
                        <button wire:click="openMulaiTahap('peremajaan')" class="flex-1 text-sm font-bold px-4 py-3 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600 transition">Lanjut ke Peremajaan</button>
                        <button wire:click="openMulaiTahap('pendewasaan')" class="flex-1 btn-primary text-sm font-bold px-4 py-3 rounded-xl transition-all">Langsung ke Pendewasaan</button>
                    </div>
                @elseif($latestTahap->jenis === 'peremajaan')
                    <div class="glass-card rounded-2xl p-5">
                        <button wire:click="openMulaiTahap('pendewasaan')" class="w-full btn-primary text-sm font-bold px-4 py-3 rounded-xl transition-all">Mulai Pendewasaan</button>
                    </div>
                @elseif($latestTahap->jenis === 'pendewasaan')
                    <div class="glass-card rounded-2xl p-5 text-center">
                        <p class="text-sm font-bold text-emerald-600 dark:text-emerald-400 mb-3">🌾 {{ $sudahMulaiPanen ? 'Sedang/Sudah Dipanen' : 'Siap Panen' }}</p>
                        <a href="{{ route($routePrefix.'.tanaman.panen') }}?tanaman={{ $selected->id }}" class="btn-primary inline-block px-6 py-3 rounded-xl text-sm font-bold transition-all">{{ $sudahMulaiPanen ? 'Lihat Panen' : 'Kelola Panen' }} &rarr;</a>
                    </div>
                @endif
            @endif
        @endif
    </div>

    @endif

    {{-- MODAL TANAMAN --}}
    @if($showModalTanaman)
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div wire:click="$set('showModalTanaman', false)" class="modal-backdrop absolute inset-0"></div>
        <div class="modal-content relative w-full sm:max-w-md bg-white/90 dark:bg-slate-800/95 backdrop-blur-xl rounded-t-2xl sm:rounded-2xl p-6 sm:p-7 shadow-2xl border border-white/50 dark:border-slate-700/50">
            <div class="flex justify-between items-start mb-4">
                <h3 class="font-extrabold text-lg dark:text-white">{{ $isEditModeTanaman ? 'Edit Tanaman' : 'Tambah Tanaman' }}</h3>
                <button type="button" wire:click="$set('showModalTanaman', false)" class="w-8 h-8 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center transition text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">✕</button>
            </div>
            <form wire:submit="saveTanaman" class="space-y-4">
                <div>
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Nama Tanaman</label>
                    <input wire:model="namaTanaman_form" placeholder="Contoh: Cabai Rawit" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                    @error('namaTanaman_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Kebun</label>
                        <select wire:model.live="kebunId_form" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                            <option value="">Pilih kebun</option>
                            @foreach($kebunList as $k)
                                <option value="{{ $k->id }}">{{ $k->nama_kebun }}</option>
                            @endforeach
                        </select>
                        @error('kebunId_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Meja</label>
                        <select wire:model="mejaId_form" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                            <option value="">Pilih meja</option>
                            @foreach($this->mejaTersedia as $m)
                                <option value="{{ $m->id }}">Meja {{ $m->nomor }}</option>
                            @endforeach
                        </select>
                        @error('mejaId_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
                @if($kebunId_form && $this->mejaTersedia->isEmpty())
                    <p class="text-xs text-amber-600 dark:text-amber-400">Semua meja di kebun ini sedang terpakai. <a href="{{ route($routePrefix.'.tanaman.kebun') }}" class="underline font-bold">Tambah meja baru?</a></p>
                @endif
                <div>
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Catatan</label>
                    <textarea wire:model="catatanTanaman_form" rows="2" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none"></textarea>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" wire:click="$set('showModalTanaman', false)" class="flex-1 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 py-3.5 rounded-xl text-sm font-bold transition dark:text-white">Batal</button>
                    <button type="submit" class="btn-primary flex-1 py-3.5 rounded-xl text-sm font-bold transition-all">{{ $isEditModeTanaman ? 'Update' : 'Simpan' }}</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- MODAL TAHAP --}}
    @if($showModalTahap)
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div wire:click="$set('showModalTahap', false)" class="modal-backdrop absolute inset-0"></div>
        <div class="modal-content relative w-full sm:max-w-md bg-white/90 dark:bg-slate-800/95 backdrop-blur-xl rounded-t-2xl sm:rounded-2xl p-6 sm:p-7 shadow-2xl border border-white/50 dark:border-slate-700/50">
            <div class="flex justify-between items-start mb-4">
                <h3 class="font-extrabold text-lg dark:text-white">{{ $tahapMode === 'mulai' ? 'Mulai '.ucfirst($jenisTahap_form) : 'Edit Tahap '.ucfirst($jenisTahap_form) }}</h3>
                <button type="button" wire:click="$set('showModalTahap', false)" class="w-8 h-8 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center transition text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">✕</button>
            </div>
            <form wire:submit="saveTahap" class="space-y-4">
                <div>
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Jumlah Tanaman</label>
                    @if($jumlahAwalTerkunci)
                        <div class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm opacity-70">{{ $jumlahAwal_form }} tanaman <span class="text-slate-400">(diwariskan dari tahap sebelumnya)</span></div>
                    @else
                        <input type="number" min="1" wire:model="jumlahAwal_form" placeholder="Contoh: 100" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                    @endif
                    @error('jumlahAwal_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Tanggal Mulai</label>
                    <input type="date" wire:model="tanggalMulai_form" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                    @error('tanggalMulai_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                @if($jenisTahap_form === 'panen')
                    <p class="text-xs text-slate-400 dark:text-slate-500">Panen bersifat berkelanjutan (bisa berkali-kali) dan ditutup manual lewat tombol "Tutup Siklus Panen" saat produksi selesai — tidak perlu durasi rencana.</p>
                @else
                <div>
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Durasi Rencana (hari)</label>
                    <input type="number" min="1" wire:model="durasiRencana_form" placeholder="Contoh: 14" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                    @error('durasiRencana_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                @endif
                <div>
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Catatan</label>
                    <textarea wire:model="catatanTahap_form" rows="2" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none"></textarea>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" wire:click="$set('showModalTahap', false)" class="flex-1 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 py-3.5 rounded-xl text-sm font-bold transition dark:text-white">Batal</button>
                    <button type="submit" class="btn-primary flex-1 py-3.5 rounded-xl text-sm font-bold transition-all">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- MODAL TANDAI SELESAI --}}
    @if($showModalSelesai)
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div wire:click="$set('showModalSelesai', false)" class="modal-backdrop absolute inset-0"></div>
        <div class="modal-content relative w-full sm:max-w-md bg-white/90 dark:bg-slate-800/95 backdrop-blur-xl rounded-t-2xl sm:rounded-2xl p-6 sm:p-7 shadow-2xl border border-white/50 dark:border-slate-700/50">
            <div class="flex justify-between items-start mb-4">
                <h3 class="font-extrabold text-lg dark:text-white">Tandai Tahap Selesai</h3>
                <button type="button" wire:click="$set('showModalSelesai', false)" class="w-8 h-8 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center transition text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">✕</button>
            </div>
            <form wire:submit="simpanSelesai" class="space-y-4">
                <div>
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Berapa yang Lolos / Masih Hidup?</label>
                    <input type="number" min="0" wire:model="jumlahLolos_form" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                    @error('jumlahLolos_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Sisanya otomatis dihitung sebagai mati.</p>
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Tanggal Selesai</label>
                    <input type="date" wire:model="tanggalSelesaiAktual_form" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                    @error('tanggalSelesaiAktual_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Catatan</label>
                    <textarea wire:model="catatanSelesai_form" rows="2" placeholder="Opsional, misal penyebab kematian" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none"></textarea>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" wire:click="$set('showModalSelesai', false)" class="flex-1 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 py-3.5 rounded-xl text-sm font-bold transition dark:text-white">Batal</button>
                    <button type="submit" class="btn-primary flex-1 py-3.5 rounded-xl text-sm font-bold transition-all">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    @endif

</x-dynamic-component>
