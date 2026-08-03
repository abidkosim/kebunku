<x-dynamic-component :component="$actorType === 'owner' ? 'owner.shell' : 'staff.shell'" :owner="$owner" active="laporan" :logs="$logs" :actor-type="$actorType" :actor-nama="$actorNama" :actor-foto-url="$actorFotoUrl">

    <div class="mb-6">
        <h3 class="font-extrabold text-lg flex items-center gap-2 dark:text-white">
            <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Laporan
        </h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Rekap hasil panen, keuangan, dan tingkat keberhasilan tanam lintas semua kebun</p>
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

    {{-- Ringkasan panen --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
        <div class="glass-card rounded-2xl p-5 shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20">
            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Hasil Panen</p>
            <p class="text-xl font-extrabold text-slate-800 dark:text-white mt-1.5">{{ number_format($totalBerat, 2) }} <span class="text-sm font-semibold text-slate-400">kg</span></p>
        </div>
        <div class="glass-card rounded-2xl p-5 shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20">
            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Pendapatan Panen</p>
            <p class="text-xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1.5">Rp {{ number_format($totalPendapatanPanen, 0, ',', '.') }}</p>
        </div>
        <div class="glass-card rounded-2xl p-5 shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20">
            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Belum Dibayar</p>
            <p class="text-xl font-extrabold {{ $totalBelumDibayar > 0 ? 'text-red-600 dark:text-red-400' : 'text-slate-800 dark:text-white' }} mt-1.5">Rp {{ number_format($totalBelumDibayar, 0, ',', '.') }}</p>
        </div>
        <div class="glass-card rounded-2xl p-5 shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20">
            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Siklus Selesai Dipanen</p>
            <p class="text-xl font-extrabold text-slate-800 dark:text-white mt-1.5">{{ $totalSelesaiDipanen }} <span class="text-sm font-semibold text-slate-400">tanaman</span></p>
        </div>
    </div>

    {{-- Ringkasan keuangan umum --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="glass-card rounded-2xl p-5 shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20">
            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Pemasukan Umum</p>
            <p class="text-lg font-extrabold text-emerald-600 dark:text-emerald-400 mt-1.5">Rp {{ number_format($totalPemasukanUmum, 0, ',', '.') }}</p>
        </div>
        <div class="glass-card rounded-2xl p-5 shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20">
            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Pengeluaran Umum</p>
            <p class="text-lg font-extrabold text-red-600 dark:text-red-400 mt-1.5">Rp {{ number_format($totalPengeluaranUmum, 0, ',', '.') }}</p>
        </div>
        <div class="glass-card rounded-2xl p-5 shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20">
            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Laba/Rugi Bersih</p>
            <p class="text-lg font-extrabold {{ $labaRugiBersih >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }} mt-1.5">Rp {{ number_format($labaRugiBersih, 0, ',', '.') }}</p>
            <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">Pendapatan panen + pemasukan umum - pengeluaran umum</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Tingkat kematian per tahap --}}
        <div class="glass-card rounded-2xl shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20 overflow-hidden">
            <div class="p-5 border-b border-slate-200/50 dark:border-slate-700/50">
                <h3 class="font-extrabold text-sm dark:text-white">Tingkat Keberhasilan per Tahap</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Tahap yang selesai di periode terpilih</p>
            </div>
            <div class="divide-y divide-slate-100/70 dark:divide-slate-700/50">
                @forelse($kematianPerTahap as $row)
                <div class="p-4 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-bold dark:text-white">{{ $row['label'] }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $row['lolos'] }} hidup / {{ $row['awal'] }} awal @if($row['mati'] > 0)• {{ $row['mati'] }} mati @endif</p>
                    </div>
                    @if($row['persen_selamat'] !== null)
                    <span class="text-[10px] font-bold px-2.5 py-1 rounded-full {{ $row['persen_selamat'] >= 90 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : ($row['persen_selamat'] >= 70 ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400') }}">{{ $row['persen_selamat'] }}% selamat</span>
                    @endif
                </div>
                @empty
                <div class="p-8 text-center text-sm text-slate-400 dark:text-slate-500">Tidak ada tahap yang selesai di periode ini</div>
                @endforelse
            </div>
        </div>

        {{-- Pembeli hutang terbesar --}}
        <div class="glass-card rounded-2xl shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20 overflow-hidden">
            <div class="p-5 border-b border-slate-200/50 dark:border-slate-700/50">
                <h3 class="font-extrabold text-sm dark:text-white">Pembeli dengan Hutang Terbesar</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Top 5 sisa hutang saat ini (bukan per periode)</p>
            </div>
            <div class="divide-y divide-slate-100/70 dark:divide-slate-700/50">
                @forelse($pembeliHutangTerbesar as $p)
                <div class="p-4 flex items-center justify-between gap-3">
                    <p class="text-sm font-bold dark:text-white">{{ $p['nama'] }}</p>
                    <span class="text-sm font-bold text-red-600 dark:text-red-400">Rp {{ number_format($p['total_hutang'], 0, ',', '.') }}</span>
                </div>
                @empty
                <div class="p-8 text-center text-sm text-slate-400 dark:text-slate-500">Tidak ada hutang tersisa</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Rekap per kebun --}}
        <div class="glass-card rounded-2xl shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20 overflow-hidden">
            <div class="p-5 border-b border-slate-200/50 dark:border-slate-700/50">
                <h3 class="font-extrabold text-sm dark:text-white">Rekap per Kebun</h3>
            </div>
            <div class="divide-y divide-slate-100/70 dark:divide-slate-700/50">
                @forelse($rekapKebun as $row)
                <div class="p-4 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-bold dark:text-white">{{ $row['nama_kebun'] }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $row['jumlah_tanaman'] }} tanaman • {{ number_format($row['total_berat'], 2) }} kg</p>
                    </div>
                    <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($row['total_pendapatan'], 0, ',', '.') }}</span>
                </div>
                @empty
                <div class="p-8 text-center text-sm text-slate-400 dark:text-slate-500">Belum ada kebun</div>
                @endforelse
            </div>
        </div>

        {{-- Rekap keuangan per kategori --}}
        <div class="glass-card rounded-2xl shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20 overflow-hidden">
            <div class="p-5 border-b border-slate-200/50 dark:border-slate-700/50">
                <h3 class="font-extrabold text-sm dark:text-white">Rekap Keuangan per Kategori</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Di luar pendapatan panen</p>
            </div>
            <div class="divide-y divide-slate-100/70 dark:divide-slate-700/50">
                @forelse($rekapKeuangan as $row)
                <div class="p-4 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="text-[9px] font-bold px-2 py-0.5 rounded-full {{ $row['jenis'] === 'pemasukan' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">{{ ucfirst($row['jenis']) }}</span>
                        <p class="text-sm font-bold dark:text-white">{{ $row['kategori'] }}</p>
                    </div>
                    <span class="text-sm font-bold {{ $row['jenis'] === 'pemasukan' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">Rp {{ number_format($row['total'], 0, ',', '.') }}</span>
                </div>
                @empty
                <div class="p-8 text-center text-sm text-slate-400 dark:text-slate-500">Belum ada catatan keuangan di periode ini</div>
                @endforelse
            </div>
        </div>
    </div>
</x-dynamic-component>
