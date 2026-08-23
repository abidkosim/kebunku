{{--
    Tombol pemicu modal "Saran & Masukan". Sengaja BUKAN komponen Livewire: dia cuma
    memancarkan event 'buka-saran' yang ditangkap satu-satunya instance
    SaranMasukanModal di shell. Dengan begitu tombolnya bisa muncul di beberapa tempat
    (sidebar desktop & sidebar mobile) tanpa menggandakan komponen Livewire-nya.
--}}
<button type="button"
        onclick="window.Livewire.dispatch('buka-saran')"
        {{ $attributes->merge(['class' => 'w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50 transition']) }}>
    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
    <span>Saran &amp; Masukan</span>
</button>
