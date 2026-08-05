@props(['owner'])

@php
    $aksesPenuh = $owner->punyaAksesPenuh();
    $isTrial = $owner->mode_langganan === 'trial';
@endphp

@if($isTrial)
    <span class="shrink-0 text-[10px] font-bold px-2.5 py-1 rounded-full {{ $aksesPenuh ? 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">
        @if($aksesPenuh)
            Trial &bull; {{ (int) ceil(now()->diffInDays($owner->trial_berakhir_at)) }} hari lagi
        @else
            Trial Berakhir
        @endif
    </span>
@else
    <span class="shrink-0 text-[10px] font-bold px-2.5 py-1 rounded-full {{ $aksesPenuh ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">
        @if($aksesPenuh)
            Pro
        @else
            Pro Berakhir
        @endif
    </span>
@endif
