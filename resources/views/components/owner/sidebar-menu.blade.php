@props(['menus', 'active', 'owner' => null])

@php
    $fiturTerkunci = ['tandon', 'galeri'];
    $aksesTerkunci = $owner && !$owner->punyaAksesPenuh();
@endphp

@foreach($menus as $menu)
    @if(isset($menu['children']))
        @php $groupActive = collect($menu['children'])->pluck('slug')->contains($active); @endphp
        <div x-data="{ open: {{ $groupActive ? 'true' : 'false' }} }">
            <button type="button" @click="open = !open" class="sidebar-link {{ $groupActive ? 'active' : 'text-slate-600 dark:text-slate-300' }} w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $menu['icon'] }}"/></svg>
                <span class="flex-1 text-left truncate">{{ $menu['label'] }}</span>
                <svg class="w-3.5 h-3.5 shrink-0 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-transition class="pl-4 mt-1 space-y-1" style="display:none;">
                @foreach($menu['children'] as $child)
                    <a href="{{ $child['href'] }}" class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-xs font-semibold transition {{ $active === $child['slug'] ? 'bg-slate-900 dark:bg-slate-600 text-white' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                        <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $active === $child['slug'] ? 'bg-white' : 'bg-slate-300 dark:bg-slate-600' }}"></span>
                        {{ $child['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    @else
        @php $terkunci = $aksesTerkunci && in_array($menu['slug'], $fiturTerkunci, true); @endphp
        <a href="{{ $menu['href'] }}" class="sidebar-link {{ $active === $menu['slug'] ? 'active' : 'text-slate-600 dark:text-slate-300' }} {{ $terkunci ? 'opacity-50' : '' }} flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $menu['icon'] }}"/></svg>
            <span class="flex-1 truncate">{{ $menu['label'] }}</span>
            @if($terkunci)
                <span class="shrink-0 text-[9px] font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400">Terkunci</span>
            @endif
        </a>
    @endif
@endforeach
