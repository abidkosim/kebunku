<x-owner.shell :owner="$owner" active="panel" :logs="$logs">
    <div class="mb-6">
        <h1 class="text-xl font-extrabold dark:text-white">Halo, {{ $owner->nama }} 👋</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Ringkasan kebun "{{ $owner->nama_usaha }}" kamu hari ini.</p>
    </div>

    <div class="stats-grid mb-6">
        <div class="stat-card glass-card rounded-2xl p-5 shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20">
            <div class="glow"></div>
            <div>
                <p class="text-[10px] mono font-bold tracking-[0.12em] text-slate-400 dark:text-slate-500 uppercase flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Total User
                </p>
                <p class="text-3xl font-extrabold mt-1 dark:text-white">{{ $totalUser }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Anggota tim</p>
            </div>
        </div>

        <div class="stat-card glass-card rounded-2xl p-5 shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20">
            <div class="glow"></div>
            <div>
                <p class="text-[10px] mono font-bold tracking-[0.12em] text-slate-400 dark:text-slate-500 uppercase flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3c-2.5 2.5-4 5-4 8a4 4 0 008 0c0-3-1.5-5.5-4-8z"/></svg>
                    Tanaman Aktif
                </p>
                <p class="text-3xl font-extrabold mt-1 dark:text-white">{{ $totalTanamanAktif }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Belum dipanen/tutup siklus</p>
            </div>
        </div>

        <div class="stat-card glass-card rounded-2xl p-5 shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20">
            <div class="glow"></div>
            <div>
                <p class="text-[10px] mono font-bold tracking-[0.12em] text-slate-400 dark:text-slate-500 uppercase flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 8.25h19.5M2.25 8.25v10.5a1.5 1.5 0 001.5 1.5h16.5a1.5 1.5 0 001.5-1.5V8.25"/></svg>
                    Pembeli
                </p>
                <p class="text-3xl font-extrabold mt-1 dark:text-white">{{ $totalPembeli }}</p>
                <p class="text-xs {{ $totalBelumDibayar > 0 ? 'text-red-500 dark:text-red-400' : 'text-slate-500 dark:text-slate-400' }} mt-0.5">
                    @if($totalBelumDibayar > 0)
                        Rp {{ number_format($totalBelumDibayar, 0, ',', '.') }} belum dibayar
                    @else
                        Tidak ada hutang
                    @endif
                </p>
            </div>
        </div>

        <div class="stat-card glass-card rounded-2xl p-5 shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20 relative overflow-hidden">
            <div class="glow"></div>
            <div class="absolute -right-8 -top-8 w-32 h-32 bg-emerald-400/10 blur-2xl rounded-full pointer-events-none"></div>
            <div class="relative z-10">
                <p class="text-[10px] mono font-bold tracking-[0.12em] text-slate-400 dark:text-slate-500 uppercase flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v1m0 8v1m0-1v-1m0 4v1m0-1v-1"/></svg>
                    Laba/Rugi Bulan Ini
                </p>
                <p class="text-3xl font-extrabold mt-1 {{ $labaRugiBulanIni >= 0 ? 'bg-gradient-to-r from-emerald-600 to-emerald-500 dark:from-emerald-400 dark:to-emerald-300 bg-clip-text text-transparent' : 'text-red-600 dark:text-red-400' }}">Rp {{ number_format($labaRugiBulanIni, 0, ',', '.') }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Panen + pemasukan - pengeluaran</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <a href="{{ route('owner.tanaman') }}" wire:navigate class="glass-card rounded-2xl p-5 flex items-center gap-3 hover:shadow-lg transition-shadow">
            <div class="w-11 h-11 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3c-2.5 2.5-4 5-4 8a4 4 0 008 0c0-3-1.5-5.5-4-8z"/></svg>
            </div>
            <div>
                <p class="text-sm font-bold dark:text-white">Kelola Tanaman</p>
                <p class="text-xs text-slate-400 dark:text-slate-500">Siklus semai - panen</p>
            </div>
        </a>
        <a href="{{ route('owner.pembeli') }}" wire:navigate class="glass-card rounded-2xl p-5 flex items-center gap-3 hover:shadow-lg transition-shadow">
            <div class="w-11 h-11 rounded-xl bg-sky-100 dark:bg-sky-900/30 flex items-center justify-center text-sky-600 dark:text-sky-400 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 8.25h19.5M2.25 8.25v10.5a1.5 1.5 0 001.5 1.5h16.5a1.5 1.5 0 001.5-1.5V8.25"/></svg>
            </div>
            <div>
                <p class="text-sm font-bold dark:text-white">Pembeli</p>
                <p class="text-xs text-slate-400 dark:text-slate-500">Riwayat & hutang</p>
            </div>
        </a>
        <a href="{{ route('owner.keuangan') }}" wire:navigate class="glass-card rounded-2xl p-5 flex items-center gap-3 hover:shadow-lg transition-shadow">
            <div class="w-11 h-11 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 dark:text-amber-400 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v1m0 8v1m0-1v-1m0 4v1m0-1v-1M4.5 19.5h15A1.5 1.5 0 0021 18V6a1.5 1.5 0 00-1.5-1.5h-15A1.5 1.5 0 003 6v12a1.5 1.5 0 001.5 1.5z"/></svg>
            </div>
            <div>
                <p class="text-sm font-bold dark:text-white">Keuangan</p>
                <p class="text-xs text-slate-400 dark:text-slate-500">Pemasukan & pengeluaran</p>
            </div>
        </a>
        <a href="{{ route('owner.laporan') }}" wire:navigate class="glass-card rounded-2xl p-5 flex items-center gap-3 hover:shadow-lg transition-shadow">
            <div class="w-11 h-11 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <p class="text-sm font-bold dark:text-white">Laporan</p>
                <p class="text-xs text-slate-400 dark:text-slate-500">Rekap lintas kebun</p>
            </div>
        </a>
    </div>

    {{-- Detail Laporan Bulan Ini --}}
    <div class="flex items-center justify-between mb-3">
        <h3 class="font-extrabold text-sm dark:text-white">Detail Laporan Bulan Ini ({{ now()->translatedFormat('F Y') }})</h3>
        <a href="{{ route('owner.laporan') }}" wire:navigate class="text-xs font-bold text-slate-500 dark:text-slate-400 hover:underline">Laporan Lengkap &rarr;</a>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="glass-card rounded-2xl shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20 p-5">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Hasil Panen</p>
                    <p class="text-lg font-extrabold dark:text-white mt-1">{{ number_format($totalBeratPanenBulanIni, 2) }} <span class="text-xs font-semibold text-slate-400">kg</span></p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Pendapatan Panen</p>
                    <p class="text-lg font-extrabold text-emerald-600 dark:text-emerald-400 mt-1">Rp {{ number_format($pendapatanPanenBulanIni, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Pemasukan Umum</p>
                    <p class="text-lg font-extrabold text-emerald-600 dark:text-emerald-400 mt-1">Rp {{ number_format($pemasukanUmumBulanIni, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Pengeluaran Umum</p>
                    <p class="text-lg font-extrabold text-red-600 dark:text-red-400 mt-1">Rp {{ number_format($pengeluaranUmumBulanIni, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="glass-card rounded-2xl shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20 overflow-hidden">
            <div class="p-4 border-b border-slate-200/50 dark:border-slate-700/50">
                <h4 class="font-extrabold text-xs dark:text-white">Tingkat Keberhasilan per Tahap</h4>
                <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">Tahap yang selesai bulan ini</p>
            </div>
            <div class="divide-y divide-slate-100/70 dark:divide-slate-700/50">
                @forelse($kematianPerTahapBulanIni as $row)
                <div class="p-3.5 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-bold dark:text-white">{{ $row['label'] }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $row['lolos'] }} hidup / {{ $row['awal'] }} awal</p>
                    </div>
                    @if($row['persen_selamat'] !== null)
                    <span class="text-[10px] font-bold px-2.5 py-1 rounded-full {{ $row['persen_selamat'] >= 90 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : ($row['persen_selamat'] >= 70 ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400') }}">{{ $row['persen_selamat'] }}%</span>
                    @endif
                </div>
                @empty
                <div class="p-6 text-center text-xs text-slate-400 dark:text-slate-500">Belum ada tahap yang selesai bulan ini</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="glass-card rounded-2xl shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20 p-6">
        <h3 class="font-extrabold text-sm dark:text-white mb-2">Mulai dari sini</h3>
        <p class="text-sm text-slate-500 dark:text-slate-400">Kelola anggota tim kamu lewat menu <a href="{{ route('owner.user') }}" wire:navigate class="font-bold text-slate-900 dark:text-white hover:underline">Manajemen User</a> di dropdown nama kamu (kanan atas). Modul Tandon & Galeri masih dalam pengembangan.</p>
    </div>
</x-owner.shell>
