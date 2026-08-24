@php
    // Owner (desktop, tanpa bottom-nav) -> pojok kanan bawah.
    // Staff (mobile, ada bottom-nav + tombol Absensi terapung di kanan) -> pojok
    // kiri bawah supaya tidak bertabrakan dengan tombol Absensi.
    $posisiTombol = $actorType === 'owner' ? 'bottom-6 right-6' : 'bottom-24 left-4';
    $posisiPanel = $actorType === 'owner' ? 'bottom-24 right-6' : 'bottom-40 left-4';

    $pertanyaanCepat = [
        'Bagaimana cara mulai pakai aplikasi ini?',
        'Bagaimana cara mencatat hasil panen?',
        'Di mana saya bisa lihat laporan keuangan?',
    ];
@endphp

<div x-data="{ open: false }" @keydown.escape.window="open = false">
    {{-- Tombol bulat terapung --}}
    <button type="button" @click="open = !open" aria-label="Tanya AI Kebunku"
            class="fixed z-40 flex items-center justify-center w-14 h-14 rounded-full btn-primary shadow-xl {{ $posisiTombol }}">
        <svg x-show="!open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
        <svg x-show="open" style="display:none" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>

    {{-- Panel chat --}}
    <div x-show="open" x-transition @click.outside="open = false"
         class="fixed z-40 w-[calc(100vw-2rem)] max-w-sm {{ $posisiPanel }}" style="display:none;">
        <div class="glass-card rounded-2xl shadow-2xl border border-white/50 dark:border-slate-700/50 overflow-hidden flex flex-col" style="height:min(28rem, 70vh);">

            {{-- Header --}}
            <div class="p-4 border-b border-slate-200/50 dark:border-slate-700/50 flex items-center justify-between bg-white dark:bg-slate-800 shrink-0">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 shrink-0 rounded-full btn-primary flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.121 12.728a4 4 0 105.657 0M9 12a3 3 0 116 0c0 1.657-1.5 2.5-1.5 4h-3c0-1.5-1.5-2.343-1.5-4z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="font-extrabold text-sm leading-none dark:text-white truncate">Kebunku Assistant</p>
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">Tanya seputar cara pakai aplikasi</p>
                    </div>
                </div>
                <div class="flex items-center gap-1 shrink-0">
                    @if(count($pesanPesan) > 0)
                    <button type="button" wire:click="resetChat" wire:confirm="Hapus riwayat chat ini?" aria-label="Reset chat"
                            class="w-7 h-7 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center text-slate-400 dark:text-slate-500">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                    @endif
                    <button type="button" @click="open = false" aria-label="Tutup" class="w-7 h-7 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center text-slate-400 dark:text-slate-500">✕</button>
                </div>
            </div>

            {{-- Daftar pesan --}}
            <div x-ref="daftarPesan" x-init="$watch('open', v => v && $nextTick(() => $refs.daftarPesan.scrollTop = $refs.daftarPesan.scrollHeight))"
                 class="flex-1 overflow-y-auto p-4 space-y-3">
                @if(count($pesanPesan) === 0)
                <div class="text-center py-4">
                    <p class="text-xs text-slate-400 dark:text-slate-500 mb-3">Halo{{ $actorNama ? ', ' . $actorNama : '' }}! Ada yang bisa saya bantu soal pemakaian aplikasi Kebunku?</p>
                    <div class="flex flex-col gap-2 items-stretch">
                        @foreach($pertanyaanCepat as $q)
                        <button type="button" wire:click="tanyaCepat(@js($q))" wire:loading.attr="disabled" wire:target="tanyaCepat"
                                class="text-xs font-semibold text-left px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-700/50 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                            {{ $q }}
                        </button>
                        @endforeach
                    </div>
                </div>
                @endif

                @foreach($pesanPesan as $p)
                <div class="flex {{ $p['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[85%] px-3.5 py-2.5 rounded-2xl text-xs leading-relaxed whitespace-pre-wrap {{ $p['role'] === 'user' ? 'btn-primary text-white rounded-br-sm' : 'bg-slate-100 dark:bg-slate-700/60 text-slate-700 dark:text-slate-200 rounded-bl-sm' }}">{{ $p['content'] }}</div>
                </div>
                @endforeach

                <div wire:loading wire:target="kirim,tanyaCepat" class="flex justify-start">
                    <div class="max-w-[85%] px-3.5 py-2.5 rounded-2xl rounded-bl-sm bg-slate-100 dark:bg-slate-700/60 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400 dark:bg-slate-500 animate-bounce" style="animation-delay:0ms"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400 dark:bg-slate-500 animate-bounce" style="animation-delay:150ms"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400 dark:bg-slate-500 animate-bounce" style="animation-delay:300ms"></span>
                    </div>
                </div>
            </div>

            {{-- Form input --}}
            <form wire:submit="kirim" class="p-3 border-t border-slate-200/50 dark:border-slate-700/50 bg-white dark:bg-slate-800 shrink-0 flex items-center gap-2">
                <input type="text" wire:model="pertanyaan" maxlength="1000" placeholder="Tulis pertanyaan..."
                       wire:loading.attr="disabled" wire:target="kirim,tanyaCepat"
                       class="input-fancy flex-1 rounded-xl px-3.5 py-2.5 text-xs focus:outline-none" />
                <button type="submit" wire:loading.attr="disabled" wire:target="kirim,tanyaCepat"
                        aria-label="Kirim" class="w-9 h-9 shrink-0 rounded-xl btn-primary flex items-center justify-center disabled:opacity-50">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </button>
            </form>
        </div>
    </div>
</div>
