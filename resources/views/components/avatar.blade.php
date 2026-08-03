@props(['name' => 'S', 'size' => 'w-9 h-9', 'photo' => null])
@if($photo)
    <img src="{{ $photo }}" alt="{{ $name }}" class="{{ $size }} rounded-full object-cover ring-1 ring-slate-200 shrink-0">
@else
    @php
        $initial = strtoupper(substr($name, 0, 1));
        $colors = ['bg-slate-900 text-white', 'bg-blue-600 text-white', 'bg-emerald-600 text-white', 'bg-orange-500 text-white'];
        $color = $colors[crc32($name) % count($colors)];
    @endphp
    <div class="{{ $size }} {{ $color }} rounded-full flex items-center justify-center font-bold text- ring-1 ring-slate-200 shrink-0">
        {{ $initial }}
    </div>
@endif