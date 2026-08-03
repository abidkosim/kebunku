<x-staff.shell :owner="$owner" active="dashboard" :logs="$logs" :actor-type="$actorType" :actor-nama="$actorNama" :actor-foto-url="$actorFotoUrl">
    <div class="mb-5">
        <h1 class="text-lg font-extrabold dark:text-white">Halo, {{ $actorNama }} 👋</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $owner->nama_usaha }}</p>
    </div>

    @if($actorType === 'teknisi')
        <div class="grid grid-cols-2 gap-3 mb-5">
            <div class="glass-card rounded-2xl p-4">
                <p class="text-[10px] mono font-bold tracking-wide text-slate-400 dark:text-slate-500 uppercase">Tanaman Aktif</p>
                <p class="text-2xl font-extrabold mt-1 dark:text-white">{{ $totalTanamanAktif }}</p>
            </div>
            <div class="glass-card rounded-2xl p-4">
                <p class="text-[10px] mono font-bold tracking-wide text-slate-400 dark:text-slate-500 uppercase">Siap Panen</p>
                <p class="text-2xl font-extrabold mt-1 text-emerald-600 dark:text-emerald-400">{{ $siapPanen }}</p>
            </div>
        </div>

        <div class="space-y-3">
            <a href="{{ route('portal.tanaman') }}" class="glass-card rounded-2xl p-4 flex items-center gap-3 active:scale-95 transition-transform">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3c-2.5 2.5-4 5-4 8a4 4 0 008 0c0-3-1.5-5.5-4-8z"/></svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold dark:text-white">Kelola Tanaman</p>
                    <p class="text-xs text-slate-400 dark:text-slate-500">Siklus semai sampai siap panen</p>
                </div>
            </a>
            <a href="{{ route('portal.tanaman.semprot') }}" class="glass-card rounded-2xl p-4 flex items-center gap-3 active:scale-95 transition-transform">
                <div class="w-10 h-10 rounded-xl bg-sky-100 dark:bg-sky-900/30 flex items-center justify-center text-sky-600 dark:text-sky-400 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v3m6.36.64l-2.12 2.12M21 12h-3m.36 6.36l-2.12-2.12M12 21v-3m-6.36-.64l2.12-2.12M3 12h3m-.36-6.36l2.12 2.12M12 12a3 3 0 100 6 3 3 0 000-6z"/></svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold dark:text-white">Jadwal Semprot</p>
                    <p class="text-xs text-slate-400 dark:text-slate-500">Semua jadwal semprot</p>
                </div>
            </a>
            <a href="{{ route('portal.tanaman.panen') }}" class="glass-card rounded-2xl p-4 flex items-center gap-3 active:scale-95 transition-transform">
                <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 dark:text-amber-400 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v1m0 8v1m0-1v-1m0 4v1m0-1v-1"/></svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold dark:text-white">Panen</p>
                    <p class="text-xs text-slate-400 dark:text-slate-500">Catat panen &amp; pembayaran</p>
                </div>
            </a>
            <a href="{{ route('portal.tanaman.kebun') }}" class="glass-card rounded-2xl p-4 flex items-center gap-3 active:scale-95 transition-transform">
                <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4h7v7H4V4zm9 0h7v7h-7V4zM4 13h7v7H4v-7zm9 0h7v7h-7v-7z"/></svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold dark:text-white">Kebun &amp; Meja</p>
                    <p class="text-xs text-slate-400 dark:text-slate-500">Lihat &amp; kelola meja</p>
                </div>
            </a>
        </div>
    @else
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-extrabold text-xs dark:text-white">Ringkasan {{ now()->translatedFormat('F Y') }}</h3>
            <a href="{{ route('portal.laporan') }}" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 hover:underline">Laporan Lengkap &rarr;</a>
        </div>
        <div class="grid grid-cols-2 gap-3 mb-5">
            <div class="glass-card rounded-2xl p-4 col-span-2">
                <p class="text-[10px] mono font-bold tracking-wide text-slate-400 dark:text-slate-500 uppercase">Laba/Rugi Bulan Ini</p>
                <p class="text-xl font-extrabold mt-1 {{ $labaRugiBulanIni >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">Rp {{ number_format($labaRugiBulanIni, 0, ',', '.') }}</p>
                <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">Panen + pemasukan - pengeluaran</p>
            </div>
            <div class="glass-card rounded-2xl p-4">
                <p class="text-[10px] mono font-bold tracking-wide text-slate-400 dark:text-slate-500 uppercase">Pendapatan Panen</p>
                <p class="text-lg font-extrabold mt-1 text-emerald-600 dark:text-emerald-400">Rp {{ number_format($pendapatanPanenBulanIni, 0, ',', '.') }}</p>
            </div>
            <div class="glass-card rounded-2xl p-4">
                <p class="text-[10px] mono font-bold tracking-wide text-slate-400 dark:text-slate-500 uppercase">Belum Dibayar</p>
                <p class="text-lg font-extrabold mt-1 {{ $totalBelumDibayar > 0 ? 'text-red-600 dark:text-red-400' : 'dark:text-white' }}">Rp {{ number_format($totalBelumDibayar, 0, ',', '.') }}</p>
            </div>
            <div class="glass-card rounded-2xl p-4">
                <p class="text-[10px] mono font-bold tracking-wide text-slate-400 dark:text-slate-500 uppercase">Pemasukan Umum</p>
                <p class="text-lg font-extrabold mt-1 text-emerald-600 dark:text-emerald-400">Rp {{ number_format($pemasukanUmumBulanIni, 0, ',', '.') }}</p>
            </div>
            <div class="glass-card rounded-2xl p-4">
                <p class="text-[10px] mono font-bold tracking-wide text-slate-400 dark:text-slate-500 uppercase">Pengeluaran Umum</p>
                <p class="text-lg font-extrabold mt-1 text-red-600 dark:text-red-400">Rp {{ number_format($pengeluaranUmumBulanIni, 0, ',', '.') }}</p>
            </div>
            @if($menungguHarga > 0)
            <div class="glass-card rounded-2xl p-4 col-span-2">
                <p class="text-xs font-semibold text-amber-600 dark:text-amber-400">{{ $menungguHarga }} transaksi panen menunggu harga ditentukan (dicatat oleh Teknisi)</p>
            </div>
            @endif
        </div>

        @if($rekapKategoriBulanIni->isNotEmpty())
        <div class="glass-card rounded-2xl overflow-hidden mb-5">
            <div class="p-3.5 border-b border-slate-200/50 dark:border-slate-700/50">
                <h4 class="font-extrabold text-xs dark:text-white">Kategori Terbesar Bulan Ini</h4>
            </div>
            <div class="divide-y divide-slate-100/70 dark:divide-slate-700/50">
                @foreach($rekapKategoriBulanIni as $row)
                <div class="p-3 flex items-center justify-between gap-2">
                    <p class="text-xs font-bold dark:text-white">{{ $row['kategori'] }}</p>
                    <span class="text-xs font-bold {{ $row['jenis'] === 'pemasukan' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">Rp {{ number_format($row['total'], 0, ',', '.') }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="space-y-3">
            <a href="{{ route('portal.keuangan') }}" class="glass-card rounded-2xl p-4 flex items-center gap-3 active:scale-95 transition-transform">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v1m0 8v1m0-1v-1m0 4v1m0-1v-1"/></svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold dark:text-white">Keuangan</p>
                    <p class="text-xs text-slate-400 dark:text-slate-500">Pengeluaran &amp; pemasukan</p>
                </div>
            </a>
            <a href="{{ route('portal.laporan') }}" class="glass-card rounded-2xl p-4 flex items-center gap-3 active:scale-95 transition-transform">
                <div class="w-10 h-10 rounded-xl bg-sky-100 dark:bg-sky-900/30 flex items-center justify-center text-sky-600 dark:text-sky-400 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold dark:text-white">Laporan</p>
                    <p class="text-xs text-slate-400 dark:text-slate-500">Rekap hasil &amp; keuangan</p>
                </div>
            </a>
        </div>
    @endif
</x-staff.shell>
