<div class="min-h-screen bg-slate-950 text-white p-6 lg:p-10" x-data="{ jam: '' }" x-init="jam = new Date().toLocaleTimeString('id-ID'); setInterval(() => jam = new Date().toLocaleTimeString('id-ID'), 1000)">

    @php
        $tolPpm = 30;
        $tolPh = 0.2;
        $pompaLabel = ['nutrisi' => 'Mengisi Nutrisi', 'ph_up' => 'Menaikkan pH', 'ph_down' => 'Menurunkan pH'];
    @endphp

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-8">
        <div>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">Monitor Tandon</p>
            <h1 class="text-2xl lg:text-3xl font-extrabold mt-1">{{ $owner->nama_usaha ?? $owner->nama }}</h1>
        </div>
        <div class="flex items-center gap-2 text-slate-400">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span class="text-sm font-mono" x-text="jam"></span>
        </div>
    </div>

    @if($list->isEmpty())
    <div class="rounded-3xl border border-slate-800 p-16 text-center text-slate-500">
        Belum ada tandon untuk ditampilkan.
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach($list as $item)
        @php
            $ppmOk = $item->ppm_terkini !== null && abs($item->ppm_terkini - $item->target_ppm) <= $tolPpm;
            $phOk = $item->ph_terkini !== null && abs($item->ph_terkini - $item->target_ph) <= $tolPh;
        @endphp
        <div wire:key="publik-{{ $item->id }}" class="rounded-3xl bg-slate-900 border border-slate-800 p-6 flex flex-col gap-5">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <p class="font-extrabold text-lg truncate">{{ $item->nama }}</p>
                    <p class="text-xs text-slate-500 truncate">{{ $item->kebun->nama_kebun }}</p>
                </div>
                <span class="shrink-0 text-[10px] font-bold px-2.5 py-1 rounded-full {{ $item->sumber_data === 'iot' ? 'bg-sky-500/15 text-sky-400' : 'bg-slate-700/60 text-slate-400' }}">
                    {{ $item->sumber_data === 'iot' ? 'IoT' : 'Simulasi' }}
                </span>
            </div>

            <div class="grid grid-cols-3 gap-3 text-center">
                <div class="rounded-2xl p-3 {{ $item->ppm_terkini === null ? 'bg-slate-800/60' : ($ppmOk ? 'bg-emerald-500/10' : 'bg-amber-500/10') }}">
                    <p class="text-[10px] text-slate-500 font-bold">PPM</p>
                    <p class="text-2xl font-extrabold mt-1 {{ $item->ppm_terkini === null ? 'text-slate-500' : ($ppmOk ? 'text-emerald-400' : 'text-amber-400') }}">{{ $item->ppm_terkini ?? '-' }}</p>
                    <p class="text-[10px] text-slate-500">tgt {{ $item->target_ppm }}</p>
                </div>
                <div class="rounded-2xl p-3 {{ $item->ph_terkini === null ? 'bg-slate-800/60' : ($phOk ? 'bg-emerald-500/10' : 'bg-amber-500/10') }}">
                    <p class="text-[10px] text-slate-500 font-bold">pH</p>
                    <p class="text-2xl font-extrabold mt-1 {{ $item->ph_terkini === null ? 'text-slate-500' : ($phOk ? 'text-emerald-400' : 'text-amber-400') }}">{{ $item->ph_terkini ?? '-' }}</p>
                    <p class="text-[10px] text-slate-500">tgt {{ $item->target_ph }}</p>
                </div>
                <div class="rounded-2xl p-3 bg-slate-800/60">
                    <p class="text-[10px] text-slate-500 font-bold">SUHU</p>
                    <p class="text-2xl font-extrabold mt-1 text-slate-300">{{ $item->suhu_terkini ?? '-' }}°</p>
                    <p class="text-[10px] text-slate-500">&nbsp;</p>
                </div>
            </div>

            @if($item->status_pompa)
            <div class="text-xs font-bold px-3 py-2 rounded-xl bg-sky-500/10 text-sky-400 flex items-center gap-2 w-fit">
                <span class="w-1.5 h-1.5 rounded-full bg-sky-400 animate-pulse"></span>
                {{ $pompaLabel[$item->status_pompa] ?? $item->status_pompa }}
            </div>
            @endif

            <p class="text-[11px] text-slate-500 mt-auto pt-2 border-t border-slate-800">
                {{ $item->terakhir_baca_at ? 'Update '.$item->terakhir_baca_at->diffForHumans() : 'Belum ada data' }}
            </p>
        </div>
        @endforeach
    </div>
    @endif
</div>
