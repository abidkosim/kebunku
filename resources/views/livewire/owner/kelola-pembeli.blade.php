<x-dynamic-component :component="$actorType === 'owner' ? 'owner.shell' : 'staff.shell'" :owner="$owner" active="pembeli" :logs="$logs" :actor-type="$actorType" :actor-nama="$actorNama" :actor-foto-url="$actorFotoUrl">

    @php
        $statusLabelPembeli = ['lunas' => 'Lunas', 'sebagian' => 'Sebagian', 'hutang' => 'Hutang', 'menunggu_harga' => 'Menunggu Harga'];
        $statusColorPembeli = [
            'lunas' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
            'sebagian' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
            'hutang' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
            'menunggu_harga' => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
        ];
    @endphp

    @if(!$selected)
    {{-- ===================== LIST VIEW ===================== --}}

    {{-- Kartu ringkasan (item 60) - gambaran menyeluruh untuk periode terpilih di
         bawah, TIDAK ikut search/status supaya selalu jadi angka pembanding yang stabil
         (lihat catatan di KelolaPembeli::render()). --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-4">
        <div class="glass-card rounded-2xl p-4">
            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Total Pembeli</p>
            <p class="text-xl font-extrabold text-slate-800 dark:text-white mt-1">{{ $ringkasan['total_pembeli'] }}</p>
        </div>
        <div class="glass-card rounded-2xl p-4">
            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Total KG Dibeli</p>
            <p class="text-xl font-extrabold text-slate-800 dark:text-white mt-1">{{ number_format($ringkasan['total_kg'], 1) }}</p>
        </div>
        <div class="glass-card rounded-2xl p-4">
            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide">KG Menunggu Harga</p>
            <p class="text-xl font-extrabold {{ $ringkasan['kg_menunggu_harga'] > 0 ? 'text-slate-600 dark:text-slate-300' : 'text-slate-800 dark:text-white' }} mt-1">{{ number_format($ringkasan['kg_menunggu_harga'], 1) }}</p>
        </div>
        <div class="glass-card rounded-2xl p-4">
            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide">KG Belum Lunas</p>
            <p class="text-xl font-extrabold {{ $ringkasan['kg_belum_lunas'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-800 dark:text-white' }} mt-1">{{ number_format($ringkasan['kg_belum_lunas'], 1) }}</p>
        </div>
        <div class="glass-card rounded-2xl p-4 col-span-2 lg:col-span-1">
            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Total Hutang</p>
            <p class="text-xl font-extrabold {{ $ringkasan['total_hutang'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-slate-800 dark:text-white' }} mt-1">Rp {{ number_format($ringkasan['total_hutang'], 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Filter periode transaksi (tanggal panen) + status hutang --}}
    <div class="glass-card rounded-2xl p-4 mb-4 flex flex-col lg:flex-row lg:items-center gap-3">
        <div class="flex items-center gap-2 flex-1">
            <input type="date" wire:model.live="dariTanggal" class="input-fancy px-3 py-2 rounded-xl text-xs outline-none w-full">
            <span class="text-xs text-slate-400">s/d</span>
            <input type="date" wire:model.live="sampaiTanggal" class="input-fancy px-3 py-2 rounded-xl text-xs outline-none w-full">
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <button wire:click="setPeriode('bulan-ini')" class="text-xs font-bold px-3 py-2 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition whitespace-nowrap">Bulan Ini</button>
            <button wire:click="setPeriode('tahun-ini')" class="text-xs font-bold px-3 py-2 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition whitespace-nowrap">Tahun Ini</button>
            <button wire:click="setPeriode('semua')" class="text-xs font-bold px-3 py-2 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition whitespace-nowrap">Semua</button>
        </div>
        <select wire:model.live="filterStatus" class="input-fancy px-4 py-2.5 rounded-full text-xs outline-none">
            <option value="">Semua Status</option>
            <option value="lunas">Lunas</option>
            <option value="sebagian">Sebagian</option>
            <option value="hutang">Hutang</option>
            <option value="menunggu_harga">Menunggu Harga</option>
        </select>
        @if($search || $filterStatus || $dariTanggal || $sampaiTanggal)
            <button wire:click="resetFilter" class="shrink-0 text-xs font-bold px-4 py-2.5 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition whitespace-nowrap">Reset Filter</button>
        @endif
    </div>

    <div class="glass-card rounded-2xl shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20 border-slate-200/60 dark:border-slate-700/50 overflow-hidden">
        <div class="p-5 lg:p-6 flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-200/50 dark:border-slate-700/50">
            <div>
                <h3 class="font-extrabold text-lg flex items-center gap-2 dark:text-white">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 8.25h19.5M2.25 8.25v10.5a1.5 1.5 0 001.5 1.5h16.5a1.5 1.5 0 001.5-1.5V8.25M2.25 8.25l1.5-4.5h16.5l1.5 4.5m-6 4.5a2.25 2.25 0 11-4.5 0"/></svg>
                    Pembeli
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 hidden sm:block">Daftar pembeli hasil panen beserta riwayat transaksi & hutangnya</p>
            </div>
            <div class="flex items-center gap-3 flex-1 lg:flex-none justify-end">
                <div class="relative flex-1 lg:w-64 lg:flex-none">
                    <input wire:model.live.debounce.300ms="search" placeholder="Cari nama pembeli..." class="input-fancy w-full lg:w-[260px] pl-10 pr-4 py-2.5 rounded-full text-[13px] outline-none">
                    <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <button wire:click="openCreate" class="btn-primary px-4 py-2.5 rounded-full text-xs font-bold shadow-md transition-all flex items-center gap-1.5 whitespace-nowrap">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Pembeli
                </button>
            </div>
        </div>

        <div class="hidden md:block table-scroll">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr class="text-[10px] mono tracking-[0.1em] text-slate-500 dark:text-slate-400">
                            <th scope="col" class="px-6 py-4 text-left">NAMA</th>
                            <th scope="col" class="px-6 py-4 text-left">KONTAK</th>
                            <th scope="col" class="px-6 py-4 text-left">TRANSAKSI</th>
                            <th scope="col" class="px-6 py-4 text-left">KG DIBELI</th>
                            <th scope="col" class="px-6 py-4 text-left">STATUS</th>
                            <th scope="col" class="px-6 py-4 text-right">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($list as $item)
                        <tr class="group transition-all duration-200 dark:hover:bg-slate-800/30 cursor-pointer" wire:click="viewDetail({{ $item->id }})">
                            <td class="px-6 py-4 align-middle text-sm font-bold dark:text-white">{{ $item->nama }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 align-middle">{{ $item->kontak ?: '-' }}</td>
                            <td class="px-6 py-4 text-sm mono text-slate-500 dark:text-slate-400 align-middle">{{ $item->panens_count }}</td>
                            <td class="px-6 py-4 text-sm mono text-slate-500 dark:text-slate-400 align-middle">{{ number_format($item->total_kg, 2) }} kg</td>
                            <td class="px-6 py-4 align-middle">
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-bold px-2.5 py-1 rounded-full {{ $statusColorPembeli[$item->status_hutang] }}">{{ $statusLabelPembeli[$item->status_hutang] }}</span>
                                    @if($item->total_hutang > 0)
                                        <span class="text-[11px] font-bold text-red-600 dark:text-red-400 whitespace-nowrap">Rp {{ number_format($item->total_hutang, 0, ',', '.') }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 align-middle" onclick="event.stopPropagation()">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="openEdit({{ $item->id }})" class="text-xs font-bold px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition">Edit</button>
                                    @if($item->panens_count === 0)
                                    <button wire:click="delete({{ $item->id }})" wire:confirm="Hapus pembeli ini?" class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-red-100 dark:hover:bg-red-900/30 hover:text-red-500 dark:hover:text-red-400 transition">✕</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-6 py-10 text-center text-sm text-slate-400 dark:text-slate-500">{{ $search || $filterStatus || $dariTanggal || $sampaiTanggal ? 'Tidak ada pembeli yang cocok dengan filter ini.' : 'Belum ada pembeli. Pembeli juga otomatis dibuat lewat form Catat Panen.' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="md:hidden divide-y divide-slate-100/70 dark:divide-slate-700/50">
            @forelse($list as $item)
            <div class="p-5 flex items-center justify-between gap-4 cursor-pointer" wire:click="viewDetail({{ $item->id }})">
                <div>
                    <p class="text-sm font-bold dark:text-white">{{ $item->nama }}</p>
                    <p class="text-[10px] mono text-slate-500 dark:text-slate-400">{{ $item->panens_count }} transaksi • {{ number_format($item->total_kg, 2) }} kg</p>
                    <p class="text-[10px] font-bold mt-1 flex items-center gap-1.5">
                        <span class="px-2 py-0.5 rounded-full {{ $statusColorPembeli[$item->status_hutang] }}">{{ $statusLabelPembeli[$item->status_hutang] }}</span>
                        @if($item->total_hutang > 0)
                            <span class="text-red-600 dark:text-red-400">Rp {{ number_format($item->total_hutang, 0, ',', '.') }}</span>
                        @endif
                    </p>
                </div>
                <span class="text-xs font-bold text-slate-400">&rarr;</span>
            </div>
            @empty
            <div class="p-10 text-center text-sm text-slate-400 dark:text-slate-500">{{ $search || $filterStatus || $dariTanggal || $sampaiTanggal ? 'Tidak ada pembeli yang cocok dengan filter ini.' : 'Belum ada pembeli' }}</div>
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
                <span>dari {{ $list->total() }} pembeli</span>
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
        $panens = $selected->panens->sortByDesc('tanggal');
        $totalBerat = $panens->sum(fn($p) => (float) $p->berat_kg);
        $totalPendapatan = $panens->filter(fn($p) => $p->harga_per_kg !== null)->sum(fn($p) => $p->total_harga);
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

    <div class="glass-card rounded-2xl shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20 p-6 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-extrabold dark:text-white">{{ $selected->nama }}</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $selected->kontak ?: 'Tidak ada kontak' }}</p>
        </div>
        <button wire:click="openEdit({{ $selected->id }})" class="px-4 py-2 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition w-fit">Edit Data</button>
    </div>

    <div class="grid grid-cols-3 gap-3 mb-6 text-xs">
        <div class="glass-card rounded-2xl p-4">
            <p class="text-slate-400 dark:text-slate-500">Total Berat Dibeli</p>
            <p class="font-bold text-slate-700 dark:text-slate-200 mt-0.5">{{ number_format($totalBerat, 2) }} kg</p>
        </div>
        <div class="glass-card rounded-2xl p-4">
            <p class="text-slate-400 dark:text-slate-500">Total Transaksi</p>
            <p class="font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</p>
        </div>
        <div class="glass-card rounded-2xl p-4">
            <p class="text-slate-400 dark:text-slate-500">Sisa Hutang</p>
            <p class="font-bold {{ $selected->total_hutang > 0 ? 'text-red-600 dark:text-red-400' : 'text-slate-700 dark:text-slate-200' }} mt-0.5">Rp {{ number_format($selected->total_hutang, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="glass-card rounded-2xl shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20 overflow-hidden">
        <div class="p-5 border-b border-slate-200/50 dark:border-slate-700/50">
            <h3 class="font-extrabold text-sm dark:text-white">Riwayat Transaksi</h3>
        </div>
        <div class="divide-y divide-slate-100/70 dark:divide-slate-700/50">
            @forelse($panens as $p)
            <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-sm font-bold dark:text-white flex items-center gap-2 flex-wrap">
                        {{ $p->tanggal->format('d M Y') }} • {{ number_format($p->berat_kg, 2) }} kg • {{ $p->tanaman->nama_tanaman }}
                        <span class="text-[9px] font-bold px-2 py-0.5 rounded-full {{ $statusColor[$p->status_pembayaran] }}">{{ $statusLabel[$p->status_pembayaran] }}</span>
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        @if($p->harga_per_kg !== null)
                            Rp {{ number_format($p->harga_per_kg, 0, ',', '.') }}/kg • Total Rp {{ number_format($p->total_harga, 0, ',', '.') }}
                            @if($p->sisa_hutang > 0)
                                • Sisa Rp {{ number_format($p->sisa_hutang, 0, ',', '.') }}
                            @endif
                        @else
                            Harga belum ditentukan
                        @endif
                    </p>
                </div>
                @if($p->status_pembayaran !== 'lunas')
                <a href="{{ route('owner.tanaman.panen') }}?tanaman={{ $p->tanaman_id }}" wire:navigate class="text-xs font-bold px-3 py-1.5 rounded-lg btn-primary shrink-0 w-fit">Catat Bayar &rarr;</a>
                @endif
            </div>
            @empty
            <div class="p-8 text-center text-sm text-slate-400 dark:text-slate-500">Belum ada transaksi</div>
            @endforelse
        </div>
    </div>
    @endif

    {{-- MODAL TAMBAH/EDIT PEMBELI --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div wire:click="$set('showModal', false)" class="modal-backdrop absolute inset-0"></div>
        <div class="modal-content relative w-full sm:max-w-sm bg-white dark:bg-slate-800 rounded-t-2xl sm:rounded-2xl p-6 sm:p-7 shadow-2xl border border-white/50 dark:border-slate-700/50">
            <div class="flex justify-between items-start mb-4">
                <h3 class="font-extrabold text-lg dark:text-white">{{ $isEditMode ? 'Edit Pembeli' : 'Tambah Pembeli' }}</h3>
                <button type="button" wire:click="$set('showModal', false)" class="w-8 h-8 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center transition text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">✕</button>
            </div>
            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Nama Pembeli</label>
                    <input wire:model="nama_form" placeholder="Nama pembeli" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                    @error('nama_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Kontak (opsional)</label>
                    <input wire:model="kontak_form" placeholder="No. HP / alamat singkat" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                    @error('kontak_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
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
