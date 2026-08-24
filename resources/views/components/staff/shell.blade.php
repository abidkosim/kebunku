@props(['owner', 'active' => 'dashboard', 'logs' => collect(), 'actorType' => null, 'actorNama' => null, 'actorFotoUrl' => null])

@php
$role = $actorType ?? session('user_role');
$nama = $actorNama ?? session('user_nama');

$navTeknisi = [
    ['slug' => 'dashboard', 'label' => 'Beranda', 'href' => route('portal.dashboard'), 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
    ['slug' => 'tanaman-kebun', 'label' => 'Kebun', 'href' => route('portal.tanaman.kebun'), 'icon' => 'M4 4h7v7H4V4zm9 0h7v7h-7V4zM4 13h7v7H4v-7zm9 0h7v7h-7v-7z'],
    ['slug' => 'tanaman-kelola', 'label' => 'Tanaman', 'href' => route('portal.tanaman'), 'icon' => 'M12 3c-2.5 2.5-4 5-4 8a4 4 0 008 0c0-3-1.5-5.5-4-8zM12 13v8m-4 0h8'],
    ['slug' => 'tanaman-semprot', 'label' => 'Semprot', 'href' => route('portal.tanaman.semprot'), 'icon' => 'M12 3v3m6.36.64l-2.12 2.12M21 12h-3m.36 6.36l-2.12-2.12M12 21v-3m-6.36-.64l2.12-2.12M3 12h3m-.36-6.36l2.12 2.12M12 12a3 3 0 100 6 3 3 0 000-6z'],
    ['slug' => 'tanaman-panen', 'label' => 'Panen', 'href' => route('portal.tanaman.panen'), 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v1m0 8v1m0-1v-1m0 4v1m0-1v-1'],
    ['slug' => 'galeri', 'label' => 'Galeri', 'href' => route('portal.galeri'), 'icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 8h16M4 4h16v16H4V4z'],
];
/*
 * Absensi SENGAJA tidak masuk baris nav di atas (dulu ada di sini) - dipindah jadi
 * tombol bulat terapung supaya baris ikon utama (Beranda/Kebun/Tanaman/Semprot/
 * Panen/Galeri) tidak makin sesak. Lihat tombol mengambang di bawah komponen ini.
 */

$navKeuangan = [
    ['slug' => 'dashboard', 'label' => 'Beranda', 'href' => route('portal.dashboard'), 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
    ['slug' => 'keuangan', 'label' => 'Keuangan', 'href' => route('portal.keuangan'), 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v1m0 8v1m0-1v-1m0 4v1m0-1v-1M4.5 19.5h15A1.5 1.5 0 0021 18V6a1.5 1.5 0 00-1.5-1.5h-15A1.5 1.5 0 003 6v12a1.5 1.5 0 001.5 1.5z'],
    ['slug' => 'laporan', 'label' => 'Laporan', 'href' => route('portal.laporan'), 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
    ['slug' => 'galeri', 'label' => 'Galeri', 'href' => route('portal.galeri'), 'icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 8h16M4 4h16v16H4V4z'],
];

$nav = $role === 'keuangan' ? $navKeuangan : $navTeknisi;
$roleLabel = $role === 'keuangan' ? 'Keuangan' : 'Teknisi';
@endphp

<div x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }"
     x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))"
     :class="{ 'dark': darkMode }"
     class="panel-ui min-h-screen bg-gradient-to-br from-slate-100 via-slate-50 to-white dark:bg-slate-900 dark:from-slate-900 dark:via-slate-900 dark:to-slate-900 text-slate-900 dark:text-slate-100 antialiased transition-colors duration-300 pb-20">

    {{-- Top bar --}}
    <nav class="glass-card sticky top-0 z-30 border-b border-slate-200/60 shadow-sm">
        <div class="px-4 h-16 flex items-center justify-between gap-3">
            <div class="relative min-w-0" x-data="{ userMenuOpen: false }">
                <button @click="userMenuOpen = !userMenuOpen" class="flex items-center gap-3 min-w-0">
                    <x-avatar :name="$nama" :photo="$actorFotoUrl" size="w-9 h-9" />
                    <div class="min-w-0 text-left">
                        <p class="font-extrabold text-sm leading-none truncate dark:text-white">{{ $nama }}</p>
                        <p class="text-[9px] mono font-bold tracking-[0.1em] text-slate-400 dark:text-slate-500 mt-0.5 uppercase">{{ $roleLabel }} • {{ $owner->nama_usaha }}</p>
                    </div>
                </button>
                <div x-show="userMenuOpen" @click.outside="userMenuOpen = false" x-transition
                     class="absolute left-0 mt-2 w-52 glass-card rounded-2xl shadow-2xl border border-white/50 dark:border-slate-700/50 overflow-hidden z-40 p-1.5"
                     style="display:none;">
                    <a href="{{ route('portal.akun') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Pengaturan Akun
                    </a>
                    <x-owner.tombol-saran class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50 transition" />
                    <button wire:click="logout" class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-semibold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Logout
                    </button>
                </div>
            </div>
            <x-owner.badge-langganan :owner="$owner" />
            <div class="flex items-center gap-2 shrink-0 ml-auto">
                @php
                    $notifTerbaru = $logs->max('created_at');
                    $adaNotifBaru = $notifTerbaru && (!$owner->notifikasi_dibaca_at || $notifTerbaru->gt($owner->notifikasi_dibaca_at));
                @endphp
                <div class="relative" x-data="{ notifOpen: false }">
                    <button @click="notifOpen = !notifOpen; if (notifOpen) $wire.tandaiNotifikasiDibaca()" aria-label="Notifikasi" class="w-9 h-9 rounded-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center relative">
                        <svg class="w-4 h-4 text-slate-700 dark:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        @if($adaNotifBaru)
                            <span class="absolute top-0.5 right-0.5 w-2 h-2 bg-red-500 rounded-full border border-white dark:border-slate-800"></span>
                        @endif
                    </button>
                    <div x-show="notifOpen" @click.outside="notifOpen = false" x-transition
                         class="absolute right-0 mt-2 w-72 glass-card rounded-2xl shadow-2xl border border-white/50 dark:border-slate-700/50 overflow-hidden z-40" style="display:none;">
                        <div class="p-3.5 border-b border-slate-200/50 dark:border-slate-700/50 bg-white dark:bg-slate-800">
                            <h4 class="font-extrabold text-xs dark:text-white">Aktivitas Terbaru</h4>
                        </div>
                        <div class="max-h-80 overflow-y-auto divide-y divide-slate-100/70 dark:divide-slate-700/50">
                            @forelse($logs as $log)
                            <div class="p-3 hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                                <p class="text-xs text-slate-700 dark:text-slate-300"><span class="font-bold">{{ $log->actor_nama }}</span> {{ $log->keterangan }}</p>
                                <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">{{ $log->modul }} • {{ $log->created_at->diffForHumans() }}</p>
                            </div>
                            @empty
                            <div class="p-5 text-center text-xs text-slate-400 dark:text-slate-500">Belum ada aktivitas</div>
                            @endforelse
                        </div>
                    </div>
                </div>
                <button @click="darkMode = !darkMode" aria-label="Toggle theme" class="w-9 h-9 rounded-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center">
                    <svg x-show="!darkMode" class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    <svg x-show="darkMode" class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </button>
            </div>
        </div>
    </nav>

    {{-- Content --}}
    <main class="px-4 py-5 max-w-lg mx-auto w-full">
        {{ $slot }}
    </main>

    {{-- Bottom nav --}}
    <nav class="fixed bottom-0 inset-x-0 z-30 glass-card border-t border-slate-200/60 dark:border-slate-700/50">
        <div class="max-w-lg mx-auto grid gap-1 px-2 py-2" style="grid-template-columns: repeat({{ count($nav) }}, minmax(0, 1fr));">
            @foreach($nav as $item)
                @php $isActive = $active === $item['slug']; @endphp
                <a href="{{ $item['href'] }}" wire:navigate class="flex flex-col items-center gap-1 py-1.5 rounded-xl transition {{ $isActive ? 'text-slate-900 dark:text-white' : 'text-slate-400 dark:text-slate-500' }}">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center {{ $isActive ? 'bg-slate-900 dark:bg-slate-600 text-white' : '' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/></svg>
                    </div>
                    <span class="text-[9px] font-bold">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>
    </nav>

    {{-- Tombol bulat terapung ke Absensi - hanya Teknisi (Keuangan tidak punya akses
         fitur ini sama sekali). Ditaruh di atas bottom nav, kanan bawah, seperti
         tombol live-chat, supaya tidak menambah baris ikon yang sudah padat. --}}
    @if($role === 'teknisi')
    <a href="{{ route('portal.absensi') }}" wire:navigate
       aria-label="Absensi Karyawan"
       class="fixed z-40 flex items-center justify-center w-14 h-14 rounded-full btn-primary shadow-xl {{ $active === 'absensi' ? 'ring-4 ring-slate-900/20 dark:ring-white/20' : '' }}"
       style="bottom: 5.25rem; right: 1rem;">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0zM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
    </a>
    @endif

    {{-- Modalnya sendiri (tombol pemicunya ada di dropdown profil di atas). --}}
    @livewire('owner.saran-masukan-modal')
</div>
