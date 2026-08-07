<x-dynamic-component :component="$actorType === 'owner' ? 'owner.shell' : 'staff.shell'" :owner="$owner" active="tanaman-panen" :logs="$logs" :actor-type="$actorType" :actor-nama="$actorNama" :actor-foto-url="$actorFotoUrl">

    @if(!$selected)
    {{-- ===================== LIST VIEW ===================== --}}
    <div class="glass-card rounded-2xl shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20 border-slate-200/60 dark:border-slate-700/50 overflow-hidden">
        <div class="p-5 lg:p-6 flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-200/50 dark:border-slate-700/50">
            <div>
                <h3 class="font-extrabold text-lg flex items-center gap-2 dark:text-white">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v1m0 8v1m0-1v-1m0 4v1m0-1v-1"/></svg>
                    Panen
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 hidden sm:block">Tanaman yang sudah siap panen, sedang dipanen, atau selesai dipanen</p>
            </div>
            <div class="relative flex-1 lg:w-64 lg:flex-none">
                <input wire:model.live.debounce.300ms="search" placeholder="Cari nama tanaman..." class="input-fancy w-full lg:w-[260px] pl-10 pr-4 py-2.5 rounded-full text-[13px] outline-none">
                <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
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
                            <th scope="col" class="px-6 py-4 text-left">TOTAL PANEN</th>
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
                            <td class="px-6 py-4 text-sm mono text-slate-500 dark:text-slate-400 align-middle">{{ number_format($item->total_berat_panen, 2) }} kg</td>
                            <td class="px-6 py-4 align-middle" onclick="event.stopPropagation()">
                                <button wire:click="viewDetail({{ $item->id }})" class="text-xs font-bold px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition">Kelola &rarr;</button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-6 py-10 text-center text-sm text-slate-400 dark:text-slate-500">Belum ada tanaman yang siap panen. Selesaikan tahap Pendewasaan dulu di halaman Kelola Tanaman.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="md:hidden divide-y divide-slate-100/70 dark:divide-slate-700/50">
            @forelse($list as $item)
            <div class="p-5 flex items-center justify-between gap-4 cursor-pointer" wire:click="viewDetail({{ $item->id }})">
                <div>
                    <p class="text-sm font-bold dark:text-white">{{ $item->nama_tanaman }}</p>
                    <p class="text-[10px] mono text-slate-500 dark:text-slate-400">{{ $item->meja->kebun->nama_kebun }} • Meja {{ $item->meja->nomor }} • {{ $item->status }}</p>
                </div>
                <span class="text-xs font-bold text-slate-400">&rarr;</span>
            </div>
            @empty
            <div class="p-10 text-center text-sm text-slate-400 dark:text-slate-500">Belum ada tanaman siap panen</div>
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
        $tahapPanen = $selected->tahapans->firstWhere('jenis', 'panen');
        $panens = $selected->panens->sortByDesc('tanggal');
        $totalBerat = $panens->sum(fn($p) => (float) $p->berat_kg);
        $totalPendapatan = $panens->filter(fn($p) => $p->harga_per_kg !== null)->sum(fn($p) => $p->total_harga);
        $totalHutang = $panens->filter(fn($p) => $p->harga_per_kg !== null)->sum(fn($p) => $p->sisa_hutang);
        $menungguHarga = $panens->filter(fn($p) => $p->harga_per_kg === null)->count();
        $statusLabel = ['lunas' => 'Lunas', 'sebagian' => 'Sebagian', 'hutang' => 'Hutang', 'menunggu_harga' => 'Menunggu Harga'];
        $statusColor = [
            'lunas' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
            'sebagian' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
            'hutang' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
            'menunggu_harga' => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
        ];
    @endphp

    <button wire:click="backToList" class="mb-4 flex items-center gap-1.5 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke Daftar
    </button>

    <div class="glass-card rounded-2xl shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20 p-6 mb-6">
        <h2 class="text-lg font-extrabold dark:text-white">{{ $selected->nama_tanaman }}</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $selected->meja->kebun->nama_kebun }} • Meja {{ $selected->meja->nomor }} • <span class="font-semibold">{{ $selected->status }}</span></p>
    </div>

    @if(!$tahapPanen)
        {{-- Belum mulai panen --}}
        <div class="glass-card rounded-2xl p-8 text-center">
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Tanaman ini sudah siap panen tapi belum dimulai.</p>
            <button wire:click="openMulaiPanen" class="btn-primary px-6 py-3 rounded-xl text-sm font-bold transition-all">Mulai Panen</button>
        </div>
    @else
        {{-- Ringkasan + daftar transaksi panen --}}
        <div class="glass-card rounded-2xl shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20 overflow-hidden">
            <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200/50 dark:border-slate-700/50">
                <div>
                    <h3 class="font-extrabold text-sm dark:text-white">Riwayat Panen</h3>
                    @if($tahapPanen->status === 'berjalan')
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Mulai {{ $tahapPanen->tanggal_mulai->format('d M Y') }} • {{ $tahapPanen->jumlah_awal }} tanaman siap panen</p>
                    @else
                        <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-0.5">Siklus ditutup {{ $tahapPanen->tanggal_selesai_aktual->format('d M Y') }} • {{ $tahapPanen->jumlah_lolos }}/{{ $tahapPanen->jumlah_awal }} berhasil dipanen</p>
                    @endif
                </div>
                @if($tahapPanen->status === 'berjalan')
                <div class="flex gap-2">
                    <button wire:click="openCreatePanen" class="btn-primary px-4 py-2 rounded-full text-xs font-bold shadow-md transition-all flex items-center gap-1.5 w-fit">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Catat Panen
                    </button>
                    <button wire:click="openTutupSiklus({{ $tahapPanen->id }})" class="px-4 py-2 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition w-fit">
                        Tutup Siklus Panen
                    </button>
                </div>
                @endif
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-5 border-b border-slate-200/50 dark:border-slate-700/50 text-xs">
                <div>
                    <p class="text-slate-400 dark:text-slate-500">Total Berat</p>
                    <p class="font-bold text-slate-700 dark:text-slate-200 mt-0.5">{{ number_format($totalBerat, 2) }} kg</p>
                </div>
                <div>
                    <p class="text-slate-400 dark:text-slate-500">Total Pendapatan</p>
                    <p class="font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-slate-400 dark:text-slate-500">Belum Dibayar</p>
                    <p class="font-bold {{ $totalHutang > 0 ? 'text-red-600 dark:text-red-400' : 'text-slate-700 dark:text-slate-200' }} mt-0.5">Rp {{ number_format($totalHutang, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-slate-400 dark:text-slate-500">Menunggu Harga</p>
                    <p class="font-bold text-slate-700 dark:text-slate-200 mt-0.5">{{ $menungguHarga }} transaksi</p>
                </div>
            </div>

            <div class="divide-y divide-slate-100/70 dark:divide-slate-700/50">
                @forelse($panens as $p)
                <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm font-bold dark:text-white flex items-center gap-2 flex-wrap">
                            {{ $p->tanggal->format('d M Y') }} • {{ number_format($p->berat_kg, 2) }} kg • {{ $p->pembeli->nama }}
                            <span class="text-[9px] font-bold px-2 py-0.5 rounded-full {{ $statusColor[$p->status_pembayaran] }}">{{ $statusLabel[$p->status_pembayaran] }}</span>
                        </p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Dipanen oleh: {{ $p->pemanen }}
                            @if($p->harga_per_kg !== null)
                                • Rp {{ number_format($p->harga_per_kg, 0, ',', '.') }}/kg • Total Rp {{ number_format($p->total_harga, 0, ',', '.') }}
                                @if($p->sisa_hutang > 0)
                                    • Sisa Rp {{ number_format($p->sisa_hutang, 0, ',', '.') }}
                                @endif
                            @endif
                        </p>
                        @if($p->catatan)
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">{{ $p->catatan }}</p>
                        @endif
                    </div>
                    <div class="flex gap-2 shrink-0">
                        @if($p->status_pembayaran !== 'lunas')
                            <button wire:click="openCatatPembayaran({{ $p->id }})" class="text-xs font-bold px-3 py-1.5 rounded-lg btn-primary">Catat Bayar</button>
                        @endif
                        @if($tahapPanen->status === 'berjalan')
                            <button wire:click="deletePanen({{ $p->id }})" wire:confirm="Hapus catatan panen ini?" class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-red-100 dark:hover:bg-red-900/30 hover:text-red-500 dark:hover:text-red-400 transition">✕</button>
                        @endif
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-sm text-slate-400 dark:text-slate-500">Belum ada catatan panen</div>
                @endforelse
            </div>
        </div>
    @endif
    @endif

    {{-- MODAL MULAI PANEN --}}
    @if($showModalMulai)
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div wire:click="$set('showModalMulai', false)" class="modal-backdrop absolute inset-0"></div>
        <div class="modal-content relative w-full sm:max-w-sm bg-white dark:bg-slate-800 rounded-t-2xl sm:rounded-2xl p-6 sm:p-7 shadow-2xl border border-white/50 dark:border-slate-700/50">
            <div class="flex justify-between items-start mb-4">
                <h3 class="font-extrabold text-lg dark:text-white">Mulai Panen</h3>
                <button type="button" wire:click="$set('showModalMulai', false)" class="w-8 h-8 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center transition text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">✕</button>
            </div>
            <form wire:submit="simpanMulaiPanen" class="space-y-4">
                <div>
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Jumlah Tanaman Siap Panen</label>
                    <div class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm opacity-70">{{ $jumlahAwalPanen_form }} tanaman <span class="text-slate-400">(dari tahap Pendewasaan)</span></div>
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Tanggal Mulai</label>
                    <input type="date" wire:model="tanggalMulaiPanen_form" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                    @error('tanggalMulaiPanen_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <p class="text-xs text-slate-400 dark:text-slate-500">Panen bisa dicatat berkali-kali. Tutup siklus manual saat produksi sudah selesai.</p>
                <div class="flex gap-3 pt-2">
                    <button type="button" wire:click="$set('showModalMulai', false)" class="flex-1 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 py-3.5 rounded-xl text-sm font-bold transition dark:text-white">Batal</button>
                    <button type="submit" class="btn-primary flex-1 py-3.5 rounded-xl text-sm font-bold transition-all">Mulai</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- MODAL CATAT PANEN --}}
    @if($showModalPanen)
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div wire:click="$set('showModalPanen', false)" class="modal-backdrop absolute inset-0"></div>
        <div class="modal-content relative w-full sm:max-w-lg bg-white dark:bg-slate-800 rounded-t-2xl sm:rounded-2xl p-6 sm:p-7 shadow-2xl border border-white/50 dark:border-slate-700/50">
            <div class="flex justify-between items-start mb-4">
                <h3 class="font-extrabold text-lg dark:text-white">Catat Panen</h3>
                <button type="button" wire:click="$set('showModalPanen', false)" class="w-8 h-8 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center transition text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">✕</button>
            </div>
            <p class="text-xs text-slate-400 dark:text-slate-500 -mt-2 mb-4">Dipanen oleh: <span class="font-bold text-slate-600 dark:text-slate-300">{{ $this->actorNama }}</span> (sesuai akun yang login)</p>
            <form wire:submit="savePanen" class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Tanggal Panen</label>
                        <input type="date" wire:model="tanggalPanen_form" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                        @error('tanggalPanen_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Berat (kg)</label>
                        <input type="number" step="0.01" min="0.01" wire:model="beratKg_form" placeholder="Contoh: 12.5" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                        @error('beratKg_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Pembeli</label>
                    <select wire:model.live="pembeliId_form" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                        <option value="">Pilih pembeli</option>
                        @foreach($pembeliList as $pb)
                            <option value="{{ $pb->id }}">{{ $pb->nama }}</option>
                        @endforeach
                        <option value="baru">+ Pembeli Baru...</option>
                    </select>
                    @error('pembeliId_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                @if($pembeliId_form === 'baru')
                <div>
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Nama Pembeli Baru</label>
                    <input wire:model="pembeliBaru_form" placeholder="Nama pembeli" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                    @error('pembeliBaru_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                @endif

                <div>
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Metode Pembayaran</label>
                    <div class="grid grid-cols-3 gap-2 mt-1.5">
                        @foreach(['cash' => 'Cash', 'sebagian' => 'Sebagian', 'hutang' => 'Hutang'] as $val => $label)
                        <label class="flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl border text-xs font-bold cursor-pointer transition {{ $metodeBayar_form === $val ? 'bg-slate-900 dark:bg-slate-600 text-white border-slate-900 dark:border-slate-600' : 'bg-white dark:bg-slate-700 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-600' }}">
                            <input type="radio" wire:model.live="metodeBayar_form" value="{{ $val }}" class="hidden">
                            {{ $label }}
                        </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
                        Harga per Kg {{ $metodeBayar_form === 'hutang' ? '(opsional, boleh dikosongkan dulu)' : '' }}
                    </label>
                    <input type="number" step="0.01" min="0.01" wire:model="hargaPerKg_form" placeholder="Rp per kg" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                    @error('hargaPerKg_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                @if($metodeBayar_form === 'sebagian')
                <div>
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Jumlah Dibayar Sekarang</label>
                    <input type="number" step="0.01" min="0.01" wire:model="jumlahDibayar_form" placeholder="Rp" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                    @error('jumlahDibayar_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                @elseif($metodeBayar_form === 'hutang')
                    <p class="text-xs text-slate-400 dark:text-slate-500">Belum dibayar sama sekali. Nanti saat pembeli bayar, gunakan tombol "Catat Bayar" pada transaksi ini.</p>
                @endif

                <div>
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Catatan</label>
                    <textarea wire:model="catatanPanen_form" rows="2" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none"></textarea>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" wire:click="$set('showModalPanen', false)" class="flex-1 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 py-3.5 rounded-xl text-sm font-bold transition dark:text-white">Batal</button>
                    <button type="submit" class="btn-primary flex-1 py-3.5 rounded-xl text-sm font-bold transition-all">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- MODAL CATAT PEMBAYARAN --}}
    @if($showModalPembayaran)
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div wire:click="$set('showModalPembayaran', false)" class="modal-backdrop absolute inset-0"></div>
        <div class="modal-content relative w-full sm:max-w-md bg-white dark:bg-slate-800 rounded-t-2xl sm:rounded-2xl p-6 sm:p-7 shadow-2xl border border-white/50 dark:border-slate-700/50">
            <div class="flex justify-between items-start mb-4">
                <h3 class="font-extrabold text-lg dark:text-white">Catat Pembayaran</h3>
                <button type="button" wire:click="$set('showModalPembayaran', false)" class="w-8 h-8 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center transition text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">✕</button>
            </div>
            <form wire:submit="simpanPembayaran" class="space-y-4">
                @if($panenUntukBayar && $panenUntukBayar->harga_per_kg !== null)
                <div class="rounded-xl bg-slate-50 dark:bg-slate-700 p-3 text-xs flex items-center justify-between">
                    <span class="text-slate-500 dark:text-slate-400">Kekurangan yang harus dibayar</span>
                    <span class="font-extrabold text-red-600 dark:text-red-400">Rp {{ number_format($panenUntukBayar->sisa_hutang, 0, ',', '.') }}</span>
                </div>
                @endif
                <div>
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Harga per Kg</label>
                    <input type="number" step="0.01" min="0.01" wire:model.live="hargaPerKgBayar_form" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                    @error('hargaPerKgBayar_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Kalau tadinya hutang tanpa harga, tentukan harganya sekarang - kekurangan akan otomatis terhitung.</p>
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Jumlah Dibayar Sekarang</label>
                    <input type="number" step="0.01" min="0.01" wire:model="tambahanBayar_form" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                    @error('tambahanBayar_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Sudah otomatis diisi sebesar kekurangannya - turunkan angkanya kalau cuma mau bayar sebagian.</p>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" wire:click="$set('showModalPembayaran', false)" class="flex-1 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 py-3.5 rounded-xl text-sm font-bold transition dark:text-white">Batal</button>
                    <button type="submit" class="btn-primary flex-1 py-3.5 rounded-xl text-sm font-bold transition-all">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- MODAL TUTUP SIKLUS --}}
    @if($showModalTutup)
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div wire:click="$set('showModalTutup', false)" class="modal-backdrop absolute inset-0"></div>
        <div class="modal-content relative w-full sm:max-w-md bg-white dark:bg-slate-800 rounded-t-2xl sm:rounded-2xl p-6 sm:p-7 shadow-2xl border border-white/50 dark:border-slate-700/50">
            <div class="flex justify-between items-start mb-4">
                <h3 class="font-extrabold text-lg dark:text-white">Tutup Siklus Panen</h3>
                <button type="button" wire:click="$set('showModalTutup', false)" class="w-8 h-8 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center transition text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">✕</button>
            </div>
            <form wire:submit="simpanTutupSiklus" class="space-y-4">
                <div>
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Berapa yang Berhasil Dipanen?</label>
                    <input type="number" min="0" wire:model="jumlahLolosTutup_form" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                    @error('jumlahLolosTutup_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Sisanya otomatis dihitung sebagai gagal/rusak.</p>
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Tanggal Tutup</label>
                    <input type="date" wire:model="tanggalTutup_form" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                    @error('tanggalTutup_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Catatan</label>
                    <textarea wire:model="catatanTutup_form" rows="2" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none"></textarea>
                </div>
                <p class="text-xs text-amber-600 dark:text-amber-400">Meja akan otomatis bebas lagi setelah ini. Data & riwayat panen tetap tersimpan.</p>
                <div class="flex gap-3 pt-2">
                    <button type="button" wire:click="$set('showModalTutup', false)" class="flex-1 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 py-3.5 rounded-xl text-sm font-bold transition dark:text-white">Batal</button>
                    <button type="submit" class="btn-primary flex-1 py-3.5 rounded-xl text-sm font-bold transition-all">Tutup Siklus</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</x-dynamic-component>
