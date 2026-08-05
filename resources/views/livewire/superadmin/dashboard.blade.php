<div x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }"
     x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))"
     :class="{ 'dark': darkMode }"
     class="panel-ui min-h-screen bg-gradient-to-br from-slate-100 via-slate-50 to-white dark:bg-slate-900 dark:from-slate-900 dark:via-slate-900 dark:to-slate-900 text-slate-900 dark:text-slate-100 font-sans antialiased transition-colors duration-300">

    {{-- Navbar --}}
    <nav class="glass-card sticky top-0 z-30 border-b border-slate-200/60 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 lg:px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-gradient-to-br from-slate-900 to-slate-700 rounded-xl flex items-center justify-center text-white font-extrabold text-lg shadow-lg shadow-slate-900/20">S</div>
                <div class="hidden sm:block">
                    <p class="font-extrabold text-sm leading-none tracking-tight bg-gradient-to-r from-slate-900 to-slate-600 dark:from-slate-100 dark:to-slate-400 bg-clip-text text-transparent">Superadmin Panel</p>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5 mono font-medium tracking-[0.15em]">SECURE • v2.1 POWER</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                {{-- Notifikasi Toggle --}}
                <div class="relative" x-data="{ notifOpen: false }">
                    <button @click="notifOpen = !notifOpen" aria-label="Notifikasi" class="theme-toggle w-9 h-9 rounded-full bg-white/80 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center shadow-sm hover:shadow-md transition-all relative">
                        <svg class="w-4 h-4 text-slate-700 dark:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        @if($logs->count() > 0)
                            <span class="absolute top-0.5 right-0.5 w-2 h-2 bg-red-500 rounded-full border border-white dark:border-slate-800"></span>
                        @endif
                    </button>
                    <div x-show="notifOpen" @click.outside="notifOpen = false" x-transition
                         class="absolute right-0 mt-2 w-80 sm:w-96 glass-card rounded-2xl shadow-2xl border border-white/50 dark:border-slate-700/50 overflow-hidden z-40"
                         style="display:none;">
                        <div class="p-4 border-b border-slate-200/50 dark:border-slate-700/50 flex items-center justify-between bg-white/50 dark:bg-slate-800/50">
                            <h4 class="font-extrabold text-sm dark:text-white">Aktivitas Terbaru</h4>
                            <span class="text-[10px] mono text-slate-400 dark:text-slate-500">{{ $logs->count() }} log</span>
                        </div>
                        <div class="max-h-96 overflow-y-auto divide-y divide-slate-100/70 dark:divide-slate-700/50">
                            @forelse($logs as $log)
                            <div class="p-3.5 hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                                <p class="text-xs text-slate-700 dark:text-slate-300">
                                    <span class="font-bold">{{ $log->actor_nama }}</span>
                                    {{ $log->keterangan }}
                                </p>
                                <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">{{ $log->modul }} • {{ $log->created_at->diffForHumans() }}</p>
                            </div>
                            @empty
                            <div class="p-6 text-center text-xs text-slate-400 dark:text-slate-500">Belum ada aktivitas</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Dark/Light Toggle --}}
                <button @click="darkMode = !darkMode" aria-label="Toggle theme" class="theme-toggle w-9 h-9 rounded-full bg-white/80 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center shadow-sm hover:shadow-md transition-all">
                    <svg x-show="!darkMode" class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    <svg x-show="darkMode" class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </button>

                <div class="hidden md:flex items-center gap-3 pl-4 pr-1.5 py-1.5 bg-white/60 dark:bg-slate-800/60 rounded-full border border-slate-200/70 dark:border-slate-700/50 shadow-sm">
                    <div class="text-right">
                        <p class="text-sm font-bold leading-none text-slate-800 dark:text-slate-200 flex items-center gap-1.5 justify-end">
                            {{ session('superadmin_nama') }}
                            @if(($superadmin->akses ?? 'full') === 'read_only')
                                <span class="text-[9px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 uppercase tracking-wide">Read Only</span>
                            @endif
                        </p>
                    </div>
                    <x-avatar :name="session('superadmin_nama')" size="w-8 h-8" />
                </div>
                <button wire:click="logout" aria-label="Logout" class="w-9 h-9 rounded-full bg-white/80 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-red-50 dark:hover:bg-red-900/30 hover:text-red-600 dark:hover:text-red-400 hover:border-red-300 dark:hover:border-red-700 flex items-center justify-center transition-all duration-300 shadow-sm hover:shadow-md group">
                    <svg class="w-4 h-4 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </button>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 lg:px-6 py-6 lg:py-8">
        {{-- STATISTIK 4 KOLOM --}}
        <div class="stats-grid mb-6">
            {{-- Total Admin --}}
            <div class="stat-card glass-card rounded-2xl p-5 shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20">
                <div class="glow"></div>
                <div>
                    <p class="text-[10px] mono font-bold tracking-[0.12em] text-slate-400 dark:text-slate-500 uppercase flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Total Admin
                    </p>
                    <p class="text-3xl font-extrabold mt-1 dark:text-white">{{ $list->count() }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Akun terdaftar</p>
                </div>
            </div>

            {{-- Total Owner --}}
            <div class="stat-card glass-card rounded-2xl p-5 shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20">
                <div class="glow"></div>
                <div>
                    <p class="text-[10px] mono font-bold tracking-[0.12em] text-slate-400 dark:text-slate-500 uppercase flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        Total Owner
                    </p>
                    <p class="text-3xl font-extrabold mt-1 dark:text-white">{{ $listOwner->count() }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Pemilik kebun</p>
                </div>
            </div>

            {{-- Aktif Hari Ini --}}
            <div class="stat-card glass-card rounded-2xl p-5 shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20">
                <div class="glow"></div>
                <div>
                    <p class="text-[10px] mono font-bold tracking-[0.12em] text-slate-400 dark:text-slate-500 uppercase flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Aktif Hari Ini
                    </p>
                    <p class="text-3xl font-extrabold mt-1 dark:text-white">24</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-pulse"></span>
                        User online
                    </p>
                </div>
            </div>

            {{-- Pendapatan --}}
            <div class="stat-card glass-card rounded-2xl p-5 shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20 relative overflow-hidden">
                <div class="glow"></div>
                <div class="absolute -right-8 -top-8 w-32 h-32 bg-emerald-400/10 blur-2xl rounded-full pointer-events-none"></div>
                <div class="relative z-10">
                    <p class="text-[10px] mono font-bold tracking-[0.12em] text-slate-400 dark:text-slate-500 uppercase flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v1m0 8v1m0-1v-1m0 4v1m0-1v-1"/></svg>
                        Pendapatan
                    </p>
                    <p class="text-3xl font-extrabold mt-1 bg-gradient-to-r from-emerald-600 to-emerald-500 dark:from-emerald-400 dark:to-emerald-300 bg-clip-text text-transparent">Rp 0</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Bulan ini</p>
                </div>
            </div>
        </div>

        {{-- Grid: Akun Saya + Tabel Superadmin --}}
        <div class="grid grid-cols-12 gap-6 items-start">
            {{-- Left: Akun Saya --}}
            <div class="col-span-12 lg:col-span-4">
                <div class="glass-card rounded-2xl shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20 border-slate-200/60 dark:border-slate-700/50 overflow-hidden">
                    <div class="p-6 pb-3 border-b border-slate-200/50 dark:border-slate-700/50">
                        <h3 class="font-extrabold text-lg flex items-center gap-2 dark:text-white">
                            <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Akun Saya
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Kelola kredensial pribadimu</p>
                    </div>
                    <form wire:submit="updateAkunPribadi" class="p-6 space-y-4">
                        <div>
                            <label for="nama" class="text-[10px] font-bold tracking-wide text-slate-500 dark:text-slate-400 uppercase">Nama Lengkap</label>
                            <input id="nama" wire:model="nama" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                        </div>
                        <div>
                            <label for="username" class="text-[10px] font-bold tracking-wide text-slate-500 dark:text-slate-400 uppercase">Username</label>
                            <input id="username" wire:model="username" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm mono outline-none">
                        </div>
                        <div>
                            <label for="password_baru" class="text-[10px] font-bold tracking-wide text-slate-500 dark:text-slate-400 uppercase">Password Baru</label>
                            <input id="password_baru" wire:model="password_baru" type="password" placeholder="••••••••" autocomplete="off" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                        </div>
                        <button type="submit" class="btn-primary w-full py-3.5 rounded-xl text-sm font-bold transition-all">
                            Simpan Perubahan
                        </button>
                    </form>
                </div>
            </div>

            {{-- Right: Tabel Superadmin --}}
            <div class="col-span-12 lg:col-span-8">
                <div class="glass-card rounded-2xl shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20 border-slate-200/60 dark:border-slate-700/50 overflow-hidden">
                    <div class="p-5 lg:p-6 flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-200/50 dark:border-slate-700/50">
                        <div>
                            <h3 class="font-extrabold text-lg flex items-center gap-2 dark:text-white">
                                <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                Manajemen Superadmin
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 hidden sm:block">Kelola semua akses superadmin dalam satu tempat</p>
                        </div>
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full lg:w-auto">
                            <div class="relative flex-1 lg:w-64">
                                <input id="searchSuperadmin" wire:model.live.debounce.300ms="search" placeholder="Cari nama / username..." class="input-fancy w-full lg:w-[260px] pl-10 pr-4 py-2.5 rounded-full text-[13px] outline-none">
                                <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            @if(($superadmin->akses ?? 'full') === 'full')
                            <button wire:click="openCreate" aria-label="Tambah Superadmin" class="btn-primary px-5 py-2.5 rounded-full text-sm font-bold shadow-md transition-all flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Tambah
                            </button>
                            @endif
                        </div>
                    </div>

                    {{-- DESKTOP TABLE --}}
                    <div class="hidden md:block table-scroll">
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr class="text-[10px] mono tracking-[0.1em] text-slate-500 dark:text-slate-400">
                                        <th scope="col" class="px-6 py-4 text-left">USER</th>
                                        <th scope="col" class="px-6 py-4 text-left">USERNAME</th>
                                        <th scope="col" class="px-6 py-4 text-left">AKSES</th>
                                        <th scope="col" class="px-6 py-4 text-left">DIBUAT</th>
                                        <th scope="col" class="px-6 py-4 text-right">AKSI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($list as $item)
                                    <tr class="group transition-all duration-200 dark:hover:bg-slate-800/30">
                                        <td class="px-6 py-4 align-middle">
                                            <div class="flex items-center gap-3">
                                                <x-avatar :name="$item->nama" />
                                                <div>
                                                    <p class="text-sm font-bold flex items-center gap-2 dark:text-white">
                                                        {{ $item->nama }}
                                                        @if($item->id == session('superadmin_id'))
                                                            <span class="badge-you">You</span>
                                                        @endif
                                                    </p>
                                                    <p class="text-[10px] mono text-slate-400 dark:text-slate-500">#{{ str_pad($item->id, 3, '0', STR_PAD_LEFT) }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm mono text-slate-600 dark:text-slate-300 align-middle font-medium">{{ $item->username }}</td>
                                        <td class="px-6 py-4 align-middle">
                                            @if($item->akses === 'full')
                                                <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Full Akses</span>
                                            @else
                                                <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Read Only</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400 align-middle">{{ $item->created_at->format('d M Y') }}</td>
                                        <td class="px-6 py-4 align-middle">
                                            @if(($superadmin->akses ?? 'full') === 'full')
                                            <div class="flex justify-end gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                                <button wire:click="openEdit({{ $item->id }})" aria-label="Edit superadmin" class="action-btn w-8 h-8 rounded-full bg-white/70 dark:bg-slate-700/70 border border-slate-200 dark:border-slate-600 hover:bg-slate-900 dark:hover:bg-slate-600 hover:text-white dark:hover:text-white hover:border-slate-900 dark:hover:border-slate-500 shadow-sm flex items-center justify-center transition-all dark:text-slate-300">✎</button>
                                                <button wire:click="delete({{ $item->id }})" wire:confirm="Yakin hapus {{ $item->nama }}?" aria-label="Hapus superadmin" class="action-btn delete w-8 h-8 rounded-full bg-white/70 dark:bg-slate-700/70 border border-slate-200 dark:border-slate-600 hover:bg-red-500 hover:text-white hover:border-red-500 shadow-sm flex items-center justify-center transition-all dark:text-slate-300">✕</button>
                                            </div>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- MOBILE CARD LIST --}}
                    <div class="md:hidden divide-y divide-slate-100/70 dark:divide-slate-700/50">
                        @foreach($list as $item)
                        <div class="p-5 flex items-center justify-between gap-4 hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                            <div class="flex items-center gap-3">
                                <x-avatar :name="$item->nama" />
                                <div>
                                    <p class="text-sm font-bold dark:text-white flex items-center gap-1.5">
                                        {{ $item->nama }}
                                        @if($item->akses === 'read_only')
                                            <span class="text-[9px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Read Only</span>
                                        @endif
                                    </p>
                                    <p class="text-[10px] mono text-slate-500 dark:text-slate-400">{{ $item->username }} • {{ $item->created_at->format('d M') }}</p>
                                </div>
                            </div>
                            @if(($superadmin->akses ?? 'full') === 'full')
                            <div class="flex gap-2">
                                <button wire:click="openEdit({{ $item->id }})" aria-label="Edit" class="w-9 h-9 rounded-full bg-slate-100/70 dark:bg-slate-700/50 flex items-center justify-center hover:bg-slate-200 dark:hover:bg-slate-600 transition dark:text-slate-300">✎</button>
                                <button wire:click="delete({{ $item->id }})" wire:confirm="Hapus?" aria-label="Hapus" class="w-9 h-9 rounded-full bg-slate-100/70 dark:bg-slate-700/50 flex items-center justify-center hover:bg-red-100 dark:hover:bg-red-900/30 hover:text-red-500 dark:hover:text-red-400 transition dark:text-slate-300">✕</button>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Manajemen Owner --}}
    <div class="max-w-7xl mx-auto px-4 lg:px-6 pb-8">
        <div class="glass-card rounded-2xl shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20 border-slate-200/60 dark:border-slate-700/50 overflow-hidden">
            <div class="p-5 lg:p-6 flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-200/50 dark:border-slate-700/50">
                <div>
                    <h3 class="font-extrabold text-lg flex items-center gap-2 dark:text-white">
                        <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        Manajemen Owner
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 hidden sm:block">Kelola data pemilik kebun</p>
                </div>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full lg:w-auto">
                    <div class="relative flex-1 lg:w-64">
                        <input id="searchOwner" wire:model.live.debounce.300ms="searchOwner" placeholder="Cari nama / usaha..." class="input-fancy w-full lg:w-[260px] pl-10 pr-4 py-2.5 rounded-full text-[13px] outline-none">
                        <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    @if(($superadmin->akses ?? 'full') === 'full')
                    <button wire:click="openCreateOwner" aria-label="Tambah Owner" class="btn-primary px-5 py-2.5 rounded-full text-sm font-bold shadow-md transition-all flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Tambah Owner
                    </button>
                    @endif
                </div>
            </div>

            {{-- DESKTOP TABLE --}}
            <div class="hidden md:block table-scroll">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr class="text-[10px] mono tracking-[0.1em] text-slate-500 dark:text-slate-400">
                                <th scope="col" class="px-6 py-4 text-left">NAMA</th>
                                <th scope="col" class="px-6 py-4 text-left">USAHA</th>
                                <th scope="col" class="px-6 py-4 text-left">USERNAME</th>
                                <th scope="col" class="px-6 py-4 text-left">MODE</th>
                                <th scope="col" class="px-6 py-4 text-left">DIBUAT</th>
                                <th scope="col" class="px-6 py-4 text-right">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($listOwner as $item)
                            <tr class="group transition-all duration-200 dark:hover:bg-slate-800/30">
                                <td class="px-6 py-4 align-middle">
                                    <div class="flex items-center gap-3">
                                        <x-avatar :name="$item->nama" />
                                        <p class="text-sm font-bold dark:text-white">{{ $item->nama }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 align-middle font-medium">{{ $item->nama_usaha }}</td>
                                <td class="px-6 py-4 text-sm mono text-slate-600 dark:text-slate-300 align-middle font-medium">{{ $item->username }}</td>
                                <td class="px-6 py-4 align-middle">
                                    @if($item->mode_langganan === 'trial')
                                        <span class="text-[10px] font-bold px-2.5 py-1 rounded-full {{ $item->punyaAksesPenuh() ? 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">
                                            Trial {{ $item->trial_berakhir_at?->isPast() ? 'berakhir' : 'sd '.$item->trial_berakhir_at?->format('d M Y') }}
                                        </span>
                                    @else
                                        <span class="text-[10px] font-bold px-2.5 py-1 rounded-full {{ $item->punyaAksesPenuh() ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">
                                            Pro {{ $item->pro_berakhir_at ? 'sd '.$item->pro_berakhir_at->format('d M Y') : '(tanpa batas)' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400 align-middle">{{ $item->created_at->format('d M Y') }}</td>
                                <td class="px-6 py-4 align-middle">
                                    @if(($superadmin->akses ?? 'full') === 'full')
                                    <div class="flex justify-end gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                        <button wire:click="openEditOwner({{ $item->id }})" aria-label="Edit owner" class="action-btn w-8 h-8 rounded-full bg-white/70 dark:bg-slate-700/70 border border-slate-200 dark:border-slate-600 hover:bg-slate-900 dark:hover:bg-slate-600 hover:text-white dark:hover:text-white hover:border-slate-900 dark:hover:border-slate-500 shadow-sm flex items-center justify-center transition-all dark:text-slate-300">✎</button>
                                        <button wire:click="deleteOwner({{ $item->id }})" wire:confirm="Yakin hapus owner {{ $item->nama }}?" aria-label="Hapus owner" class="action-btn delete w-8 h-8 rounded-full bg-white/70 dark:bg-slate-700/70 border border-slate-200 dark:border-slate-600 hover:bg-red-500 hover:text-white hover:border-red-500 shadow-sm flex items-center justify-center transition-all dark:text-slate-300">✕</button>
                                    </div>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- MOBILE CARD LIST --}}
            <div class="md:hidden divide-y divide-slate-100/70 dark:divide-slate-700/50">
                @foreach($listOwner as $item)
                <div class="p-5 flex items-center justify-between gap-4 hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                    <div class="flex items-center gap-3">
                        <x-avatar :name="$item->nama" />
                        <div>
                            <p class="text-sm font-bold dark:text-white">{{ $item->nama }}</p>
                            <p class="text-[10px] mono text-slate-500 dark:text-slate-400">{{ $item->nama_usaha }} • {{ $item->username }}</p>
                            @if($item->mode_langganan === 'trial')
                                <span class="inline-block mt-1 text-[9px] font-bold px-2 py-0.5 rounded-full {{ $item->punyaAksesPenuh() ? 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">
                                    Trial {{ $item->trial_berakhir_at?->isPast() ? 'berakhir' : 'sd '.$item->trial_berakhir_at?->format('d M') }}
                                </span>
                            @else
                                <span class="inline-block mt-1 text-[9px] font-bold px-2 py-0.5 rounded-full {{ $item->punyaAksesPenuh() ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">
                                    Pro {{ $item->pro_berakhir_at ? 'sd '.$item->pro_berakhir_at->format('d M') : '(tanpa batas)' }}
                                </span>
                            @endif
                        </div>
                    </div>
                    @if(($superadmin->akses ?? 'full') === 'full')
                    <div class="flex gap-2">
                        <button wire:click="openEditOwner({{ $item->id }})" aria-label="Edit" class="w-9 h-9 rounded-full bg-slate-100/70 dark:bg-slate-700/50 flex items-center justify-center hover:bg-slate-200 dark:hover:bg-slate-600 transition dark:text-slate-300">✎</button>
                        <button wire:click="deleteOwner({{ $item->id }})" wire:confirm="Hapus?" aria-label="Hapus" class="w-9 h-9 rounded-full bg-slate-100/70 dark:bg-slate-700/50 flex items-center justify-center hover:bg-red-100 dark:hover:bg-red-900/30 hover:text-red-500 dark:hover:text-red-400 transition dark:text-slate-300">✕</button>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Saran & Masukan --}}
    <div class="max-w-7xl mx-auto px-4 lg:px-6 pb-8">
        <div class="glass-card rounded-2xl shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20 border-slate-200/60 dark:border-slate-700/50 overflow-hidden">
            <div class="p-5 lg:p-6 border-b border-slate-200/50 dark:border-slate-700/50">
                <h3 class="font-extrabold text-lg flex items-center gap-2 dark:text-white">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    Saran &amp; Masukan
                    @if($sarans->where('dibaca', false)->count() > 0)
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">{{ $sarans->where('dibaca', false)->count() }} belum dibaca</span>
                    @endif
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Masukan dari owner/staff lewat panel masing-masing</p>
            </div>
            <div class="max-h-[28rem] overflow-y-auto divide-y divide-slate-100/70 dark:divide-slate-700/50">
                @forelse($sarans as $s)
                <div class="p-4 lg:p-5 flex items-start justify-between gap-4 {{ $s->dibaca ? '' : 'bg-amber-50/40 dark:bg-amber-900/10' }}">
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-slate-700 dark:text-slate-200">
                            {{ $s->actor_nama }}
                            <span class="font-normal text-slate-400 dark:text-slate-500">({{ $s->owner->nama_usaha ?? '-' }})</span>
                        </p>
                        <p class="text-sm text-slate-600 dark:text-slate-300 mt-1 whitespace-pre-line">{{ $s->pesan }}</p>
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1.5">{{ $s->created_at->diffForHumans() }}</p>
                    </div>
                    @if(!$s->dibaca)
                    <button wire:click="tandaiDibacaSaran({{ $s->id }})" class="shrink-0 text-[11px] font-bold px-3 py-1.5 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition">Tandai Dibaca</button>
                    @endif
                </div>
                @empty
                <div class="p-8 text-center text-xs text-slate-400 dark:text-slate-500">Belum ada saran & masukan</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- MODAL SUPERADMIN --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div wire:click="$set('showModal', false)" class="modal-backdrop absolute inset-0"></div>
        <div class="modal-content relative w-full sm:max-w-md bg-white/90 dark:bg-slate-800/95 backdrop-blur-xl rounded-t-2xl sm:rounded-2xl p-6 sm:p-7 shadow-2xl border border-white/50 dark:border-slate-700/50">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="font-extrabold text-lg dark:text-white">{{ $isEditMode ? 'Edit Superadmin' : 'Tambah Superadmin' }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Password otomatis di-hash bcrypt</p>
                </div>
                <button type="button" wire:click="$set('showModal', false)" aria-label="Tutup modal" class="w-8 h-8 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center transition text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">✕</button>
            </div>
            <form wire:submit="save" class="space-y-4">
                <div>
                    <label for="nama_form" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Nama</label>
                    <input id="nama_form" wire:model="nama_form" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                </div>
                <div>
                    <label for="username_form" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Username</label>
                    <input id="username_form" wire:model="username_form" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm mono outline-none">
                </div>
                <div>
                    <label for="password_form" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Password</label>
                    <input id="password_form" wire:model="password_form" type="password" placeholder="{{ $isEditMode ? 'Kosongkan jika tidak ganti' : 'Min. 6 karakter' }}" autocomplete="off" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                </div>
                <div>
                    <label for="akses_form" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Level Akses</label>
                    <select id="akses_form" wire:model="akses_form" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                        <option value="full">Full Akses</option>
                        <option value="read_only">Read Only</option>
                    </select>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" wire:click="$set('showModal', false)" class="flex-1 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 py-3.5 rounded-xl text-sm font-bold transition dark:text-white">Batal</button>
                    <button type="submit" class="btn-primary flex-1 py-3.5 rounded-xl text-sm font-bold transition-all">{{ $isEditMode ? 'Update' : 'Simpan' }}</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- MODAL OWNER --}}
    @if($showModalOwner)
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div wire:click="$set('showModalOwner', false)" class="modal-backdrop absolute inset-0"></div>
        <div class="modal-content relative w-full sm:max-w-xl lg:max-w-2xl bg-white/90 dark:bg-slate-800/95 backdrop-blur-xl rounded-t-2xl sm:rounded-2xl p-6 sm:p-7 lg:p-8 shadow-2xl border border-white/50 dark:border-slate-700/50">
            <div class="flex justify-between items-start mb-5">
                <div>
                    <h3 class="font-extrabold text-lg dark:text-white">{{ $isEditModeOwner ? 'Edit Owner' : 'Tambah Owner' }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Password otomatis di-hash bcrypt</p>
                </div>
                <button type="button" wire:click="$set('showModalOwner', false)" aria-label="Tutup modal" class="w-8 h-8 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center transition text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">✕</button>
            </div>
            <form wire:submit="saveOwner" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="namaOwner_form" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Nama</label>
                        <input id="namaOwner_form" wire:model="namaOwner_form" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                    </div>
                    <div>
                        <label for="namaUsahaOwner_form" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Nama Usaha</label>
                        <input id="namaUsahaOwner_form" wire:model="namaUsahaOwner_form" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="usernameOwner_form" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Username</label>
                        <input id="usernameOwner_form" wire:model="usernameOwner_form" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm mono outline-none">
                    </div>
                    <div>
                        <label for="passwordOwner_form" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Password</label>
                        <input id="passwordOwner_form" wire:model="passwordOwner_form" type="password" placeholder="{{ $isEditModeOwner ? 'Kosongkan jika tidak ganti' : 'Min. 6 karakter' }}" autocomplete="off" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                    </div>
                </div>
                <div>
                    <label for="alamatOwner_form" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Alamat</label>
                    <textarea id="alamatOwner_form" wire:model="alamatOwner_form" rows="3" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none"></textarea>
                </div>

                <div class="pt-2 border-t border-slate-200/50 dark:border-slate-700/50">
                    <label for="modeLangganan_form" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Mode Langganan</label>
                    <select id="modeLangganan_form" wire:model.live="modeLangganan_form" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                        <option value="trial">Trial</option>
                        <option value="pro">Pro</option>
                    </select>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">Trial habis / Pro lewat tanggal -> menu Tandon &amp; Galeri otomatis terkunci</p>
                </div>

                @if($modeLangganan_form === 'trial')
                <div>
                    <label for="trialBerakhirAt_form" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Trial Berakhir</label>
                    <input id="trialBerakhirAt_form" wire:model="trialBerakhirAt_form" type="date" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                    @error('trialBerakhirAt_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                @else
                <div>
                    <label for="proBerakhirAt_form" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Pro Berakhir (kosongkan = tanpa batas)</label>
                    <input id="proBerakhirAt_form" wire:model="proBerakhirAt_form" type="date" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                    @error('proBerakhirAt_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                @endif

                <div class="flex gap-3 pt-2 sm:max-w-xs sm:ml-auto">
                    <button type="button" wire:click="$set('showModalOwner', false)" class="flex-1 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 py-3.5 rounded-xl text-sm font-bold transition dark:text-white">Batal</button>
                    <button type="submit" class="btn-primary flex-1 py-3.5 rounded-xl text-sm font-bold transition-all">{{ $isEditModeOwner ? 'Update' : 'Simpan' }}</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>