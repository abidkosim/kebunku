@props(['tahap', 'fallback' => 'Belum mulai'])

@if($tahap && $tahap->progress_persen !== null)
    @php
        $persen = $tahap->progress_persen;
        $sisa = $tahap->sisa_hari;
        $warna = $tahap->hampir_habis ? 'bg-red-500' : ($persen >= 70 ? 'bg-amber-500' : 'bg-emerald-500');
        $warnaTeks = $tahap->hampir_habis ? 'text-red-600 dark:text-red-400 font-bold' : 'text-slate-400 dark:text-slate-500';
    @endphp
    <div class="flex items-center gap-2.5">
        <div class="flex-1 h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden min-w-[56px]">
            <div class="h-full rounded-full {{ $warna }} transition-all duration-500" style="width: {{ $persen }}%"></div>
        </div>
        <span class="shrink-0 text-[10px] mono {{ $warnaTeks }}">
            @if($sisa < 0)
                Telat {{ abs($sisa) }}h
            @elseif($sisa === 0)
                Hari ini
            @else
                {{ $sisa }}h lagi
            @endif
        </span>
    </div>
@else
    <span class="text-xs text-slate-400 dark:text-slate-500">{{ $fallback }}</span>
@endif
