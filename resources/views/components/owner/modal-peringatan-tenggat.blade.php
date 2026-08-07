@props(['show', 'items', 'closeMethod', 'title' => 'Tenggat Waktu Hampir Habis'])

@if($show)
<div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
    <div wire:click="{{ $closeMethod }}" class="modal-backdrop absolute inset-0"></div>
    <div class="modal-content relative w-full sm:max-w-lg bg-white/95 dark:bg-slate-800/95 backdrop-blur-xl rounded-t-2xl sm:rounded-2xl shadow-2xl border border-white/50 dark:border-slate-700/50 overflow-hidden">
        <div class="p-6 pb-5 bg-gradient-to-br from-amber-50 to-red-50 dark:from-amber-950/30 dark:to-red-950/20 border-b border-amber-100 dark:border-amber-900/30">
            <div class="flex items-start gap-3.5">
                <div class="shrink-0 w-11 h-11 rounded-full bg-red-100 dark:bg-red-900/40 flex items-center justify-center">
                    <svg class="w-5.5 h-5.5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m0 3.75h.007v.008H12v-.008zM10.29 3.86L1.82 18a1.5 1.5 0 001.3 2.25h17.76a1.5 1.5 0 001.3-2.25L13.71 3.86a1.5 1.5 0 00-2.42 0z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-extrabold text-base dark:text-white">{{ $title }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ count($items) }} item butuh perhatian segera</p>
                </div>
                <button type="button" wire:click="{{ $closeMethod }}" class="shrink-0 w-8 h-8 rounded-full hover:bg-white/60 dark:hover:bg-slate-700/60 flex items-center justify-center transition text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">✕</button>
            </div>
        </div>

        <div class="max-h-[60vh] overflow-y-auto divide-y divide-slate-100/70 dark:divide-slate-700/50">
            @foreach($items as $item)
            <a href="{{ $item['link'] ?? '#' }}" wire:navigate class="flex items-center gap-3.5 p-4 hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition">
                <div class="shrink-0 w-2 h-2 rounded-full {{ $item['sisaHari'] < 0 ? 'bg-red-500' : 'bg-amber-500' }}"></div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold dark:text-white truncate">{{ $item['label'] }}</p>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">{{ $item['subLabel'] }}</p>
                </div>
                <span class="shrink-0 text-[10px] font-bold px-2.5 py-1 rounded-full {{ $item['sisaHari'] < 0 ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' }}">
                    @if($item['sisaHari'] < 0)
                        Telat {{ abs($item['sisaHari']) }} hari
                    @elseif($item['sisaHari'] === 0)
                        Hari ini
                    @else
                        {{ $item['sisaHari'] }} hari lagi
                    @endif
                </span>
            </a>
            @endforeach
        </div>

        <div class="p-4 border-t border-slate-200/50 dark:border-slate-700/50 bg-slate-50/50 dark:bg-slate-800/50">
            <button type="button" wire:click="{{ $closeMethod }}" class="w-full bg-slate-900 dark:bg-white text-white dark:text-slate-900 py-3 rounded-xl text-sm font-bold hover:opacity-90 transition">Mengerti, Tutup</button>
        </div>
    </div>
</div>
@endif
