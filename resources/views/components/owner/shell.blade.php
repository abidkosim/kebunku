@props(['owner', 'active' => 'panel', 'logs' => collect()])

@php
$menus = [
    ['slug' => 'panel', 'label' => 'Panel Dashboard', 'href' => route('owner.dashboard'), 'icon' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z'],
    ['slug' => 'tanaman', 'label' => 'Manajemen Tanaman', 'icon' => 'M12 3c-2.5 2.5-4 5-4 8a4 4 0 008 0c0-3-1.5-5.5-4-8zM12 13v8m-4 0h8', 'children' => [
        ['slug' => 'tanaman-kebun', 'label' => 'Kelola Kebun & Meja', 'href' => route('owner.tanaman.kebun')],
        ['slug' => 'tanaman-kelola', 'label' => 'Kelola Tanaman', 'href' => route('owner.tanaman')],
        ['slug' => 'tanaman-semprot', 'label' => 'Jadwal Semprot', 'href' => route('owner.tanaman.semprot')],
        ['slug' => 'tanaman-panen', 'label' => 'Panen', 'href' => route('owner.tanaman.panen')],
    ]],
    ['slug' => 'pembeli', 'label' => 'Pembeli', 'href' => route('owner.pembeli'), 'icon' => 'M2.25 8.25h19.5M2.25 8.25v10.5a1.5 1.5 0 001.5 1.5h16.5a1.5 1.5 0 001.5-1.5V8.25M2.25 8.25l1.5-4.5h16.5l1.5 4.5m-6 4.5a2.25 2.25 0 11-4.5 0'],
    ['slug' => 'tandon', 'label' => 'Monitor Tandon', 'href' => route('owner.tandon'), 'icon' => 'M12 21c-3.87 0-7-2.5-7-6.5C5 10 12 3 12 3s7 7 7 11.5c0 4-3.13 6.5-7 6.5z'],
    ['slug' => 'keuangan', 'label' => 'Keuangan', 'href' => route('owner.keuangan'), 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v1m0 8v1m0-1v-1m0 4v1m0-1v-1M4.5 19.5h15A1.5 1.5 0 0021 18V6a1.5 1.5 0 00-1.5-1.5h-15A1.5 1.5 0 003 6v12a1.5 1.5 0 001.5 1.5z'],
    ['slug' => 'laporan', 'label' => 'Laporan', 'href' => route('owner.laporan'), 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
    ['slug' => 'galeri', 'label' => 'Galeri', 'href' => route('owner.galeri'), 'icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 8h16M4 4h16v16H4V4z'],
];
@endphp

<div x-data="{ darkMode: localStorage.getItem('darkMode') === 'true', sidebarOpen: false }"
     x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))"
     :class="{ 'dark': darkMode }"
     class="panel-ui min-h-screen bg-gradient-to-br from-slate-100 via-slate-50 to-white dark:bg-slate-900 dark:from-slate-900 dark:via-slate-900 dark:to-slate-900 text-slate-900 dark:text-slate-100 antialiased transition-colors duration-300">

    {{-- Navbar --}}
    <nav class="glass-card sticky top-0 z-30 border-b border-slate-200/60 shadow-sm">
        <div class="mx-auto px-4 lg:px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = !sidebarOpen" aria-label="Toggle sidebar" class="lg:hidden w-9 h-9 rounded-full bg-white/80 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center">
                    <svg class="w-4 h-4 text-slate-700 dark:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="w-10 h-10 bg-gradient-to-br from-slate-900 to-slate-700 rounded-xl flex items-center justify-center text-white font-extrabold text-lg shadow-lg shadow-slate-900/20">O</div>
                <div class="hidden sm:block">
                    <p class="font-extrabold text-sm leading-none tracking-tight bg-gradient-to-r from-slate-900 to-slate-600 dark:from-slate-100 dark:to-slate-400 bg-clip-text text-transparent">Owner Panel</p>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5 mono font-medium tracking-[0.15em]">{{ strtoupper($owner->nama_usaha) }}</p>
                </div>
                <x-owner.badge-langganan :owner="$owner" />
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

                {{-- Dropdown Nama Owner --}}
                <div class="relative" x-data="{ userMenuOpen: false }">
                    <button @click="userMenuOpen = !userMenuOpen" class="flex items-center gap-2 pl-1 pr-1.5 py-1 md:pl-4 md:pr-1.5 md:py-1.5 bg-white/60 dark:bg-slate-800/60 rounded-full border border-slate-200/70 dark:border-slate-700/50 shadow-sm hover:shadow-md transition-all">
                        <p class="hidden md:block text-sm font-bold leading-none text-slate-800 dark:text-slate-200">{{ $owner->nama }}</p>
                        <x-avatar :name="$owner->nama" :photo="$owner->foto_url" size="w-8 h-8" />
                        <svg class="hidden md:block w-3 h-3 text-slate-400 dark:text-slate-500 transition-transform" :class="{ 'rotate-180': userMenuOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="userMenuOpen" @click.outside="userMenuOpen = false" x-transition
                         class="absolute right-0 mt-2 w-56 glass-card rounded-2xl shadow-2xl border border-white/50 dark:border-slate-700/50 overflow-hidden z-40 p-1.5"
                         style="display:none;">
                        <div class="px-3 py-2.5 mb-1 border-b border-slate-200/50 dark:border-slate-700/50">
                            <p class="text-sm font-bold text-slate-800 dark:text-slate-200 truncate">{{ $owner->nama }}</p>
                            <p class="text-[10px] text-slate-400 dark:text-slate-500 truncate">{{ $owner->nama_usaha }}</p>
                        </div>
                        <a href="{{ route('owner.user') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                            Manajemen User
                        </a>
                        <a href="{{ route('owner.akun') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Pengaturan Akun
                        </a>
                        <button wire:click="logout" class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-semibold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex">
        {{-- Sidebar (desktop) --}}
        <aside class="hidden lg:flex flex-col w-64 shrink-0 min-h-[calc(100vh-4rem)] glass-card border-r border-slate-200/60 dark:border-slate-700/50 p-4 gap-1 sticky top-16 self-start">
            <x-owner.sidebar-menu :menus="$menus" :active="$active" :owner="$owner" />
            <div class="mt-auto pt-2 border-t border-slate-200/50 dark:border-slate-700/50">
                @livewire('owner.saran-masukan-modal')
            </div>
        </aside>

        {{-- Sidebar (mobile overlay) --}}
        <div x-show="sidebarOpen" x-transition.opacity class="lg:hidden fixed inset-0 z-40 bg-slate-900/50" @click="sidebarOpen = false" style="display:none;"></div>
        <aside x-show="sidebarOpen" x-transition class="lg:hidden fixed inset-y-0 left-0 z-50 w-72 glass-card p-4 gap-1 flex flex-col overflow-y-auto" style="display:none;">
            <div class="flex items-center justify-between mb-4">
                <p class="font-extrabold text-sm dark:text-white">Menu</p>
                <button @click="sidebarOpen = false" aria-label="Tutup menu" class="w-8 h-8 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center text-slate-500 dark:text-slate-400">✕</button>
            </div>
            <x-owner.sidebar-menu :menus="$menus" :active="$active" :owner="$owner" />
            <div class="mt-auto pt-2 border-t border-slate-200/50 dark:border-slate-700/50">
                @livewire('owner.saran-masukan-modal')
            </div>
        </aside>

        {{-- Main content --}}
        <main class="flex-1 min-w-0 max-w-7xl mx-auto px-4 lg:px-6 py-6 lg:py-8 w-full">
            {{ $slot }}
        </main>
    </div>
</div>
