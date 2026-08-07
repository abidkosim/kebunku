<x-dynamic-component :component="$actorType === 'owner' ? 'owner.shell' : 'staff.shell'" :owner="$owner" active="tandon" :logs="$logs" :actor-type="$actorType" :actor-nama="$actorNama" :actor-foto-url="$actorFotoUrl">

    @php
        $tolPpm = 30;
        $tolPh = 0.2;
        $pompaLabel = ['nutrisi' => 'Mengisi Nutrisi', 'ph_up' => 'Menaikkan pH', 'ph_down' => 'Menurunkan pH'];
    @endphp

    @if(!$selected)
    {{-- ===================== LIST VIEW ===================== --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div>
            <h3 class="font-extrabold text-lg flex items-center gap-2 dark:text-white">
                <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21c-3.87 0-7-2.5-7-6.5C5 10 12 3 12 3s7 7 7 11.5c0 4-3.13 6.5-7 6.5z"/></svg>
                Monitor Tandon
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">TDS/pH/suhu masih data simulasi (dummy) - sensor IoT asli belum terpasang. Update realtime lewat queue+job.</p>
        </div>
        <div class="flex items-center gap-3 flex-1 lg:flex-none justify-end">
            <div class="relative flex-1 lg:w-64 lg:flex-none">
                <input wire:model.live.debounce.300ms="search" placeholder="Cari nama tandon/kebun..." class="input-fancy w-full lg:w-[260px] pl-10 pr-4 py-2.5 rounded-full text-[13px] outline-none">
                <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <button wire:click="openCreate" class="btn-primary px-4 py-2.5 rounded-full text-xs font-bold shadow-md transition-all flex items-center gap-1.5 whitespace-nowrap w-fit">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Tandon
            </button>
        </div>
    </div>

    <div class="glass-card rounded-2xl p-4 mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="min-w-0">
            <p class="text-xs font-extrabold dark:text-white flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M12 20h.01M2 8.82a15 15 0 0120 0M5 12.859a10 10 0 0114 0"/></svg>
                Monitor Publik (buat layar TV/monitor luar jaringan)
            </p>
            @if($owner->kunci_monitor)
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 font-mono truncate">{{ rtrim(config('app.url'), '/') }}/monitor/{{ $owner->kunci_monitor }}</p>
            @else
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Belum ada link. Buat sekali, bisa dibuka di monitor manapun tanpa perlu login.</p>
            @endif
        </div>
        <button wire:click="generateKunciMonitor" @if($owner->kunci_monitor) wire:confirm="Generate ulang? Link lama otomatis tidak berlaku lagi." @endif class="shrink-0 text-xs font-bold px-4 py-2.5 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition">
            {{ $owner->kunci_monitor ? 'Generate Ulang' : 'Buat Link Monitor' }}
        </button>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse($list as $item)
        @php
            $ppmOk = $item->ppm_terkini !== null && abs($item->ppm_terkini - $item->target_ppm) <= $tolPpm;
            $phOk = $item->ph_terkini !== null && abs($item->ph_terkini - $item->target_ph) <= $tolPh;
        @endphp
        <div wire:key="tandon-card-{{ $item->id }}" wire:click="viewDetail({{ $item->id }})" class="glass-card rounded-2xl shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20 p-5 flex flex-col gap-4 cursor-pointer hover:shadow-xl hover:-translate-y-0.5 transition-all">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <p class="font-extrabold text-sm dark:text-white truncate">{{ $item->nama }}</p>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5 truncate">{{ $item->kebun->nama_kebun }}</p>
                </div>
                <span class="shrink-0 text-[9px] font-bold px-2 py-1 rounded-full {{ $item->status_simulasi === 'aktif' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' }}">
                    {{ $item->status_simulasi === 'aktif' ? 'Live' : 'Berhenti' }}
                </span>
            </div>

            <div class="grid grid-cols-3 gap-2 text-center">
                <div class="rounded-xl p-2.5 {{ $item->ppm_terkini === null ? 'bg-slate-50 dark:bg-slate-800' : ($ppmOk ? 'bg-emerald-50 dark:bg-emerald-900/20' : 'bg-amber-50 dark:bg-amber-900/20') }}">
                    <p class="text-[9px] text-slate-400 dark:text-slate-500 font-bold">PPM</p>
                    <p class="text-sm font-extrabold mt-0.5 {{ $item->ppm_terkini === null ? 'text-slate-400' : ($ppmOk ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400') }}">{{ $item->ppm_terkini ?? '-' }}</p>
                    <p class="text-[9px] text-slate-400 dark:text-slate-500">tgt {{ $item->target_ppm }}</p>
                </div>
                <div class="rounded-xl p-2.5 {{ $item->ph_terkini === null ? 'bg-slate-50 dark:bg-slate-800' : ($phOk ? 'bg-emerald-50 dark:bg-emerald-900/20' : 'bg-amber-50 dark:bg-amber-900/20') }}">
                    <p class="text-[9px] text-slate-400 dark:text-slate-500 font-bold">pH</p>
                    <p class="text-sm font-extrabold mt-0.5 {{ $item->ph_terkini === null ? 'text-slate-400' : ($phOk ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400') }}">{{ $item->ph_terkini ?? '-' }}</p>
                    <p class="text-[9px] text-slate-400 dark:text-slate-500">tgt {{ $item->target_ph }}</p>
                </div>
                <div class="rounded-xl p-2.5 bg-slate-50 dark:bg-slate-800">
                    <p class="text-[9px] text-slate-400 dark:text-slate-500 font-bold">SUHU</p>
                    <p class="text-sm font-extrabold mt-0.5 text-slate-600 dark:text-slate-300">{{ $item->suhu_terkini ?? '-' }}°</p>
                    <p class="text-[9px] text-slate-400 dark:text-slate-500">&nbsp;</p>
                </div>
            </div>

            @if($item->status_pompa)
            <div class="text-[10px] font-bold px-2.5 py-1.5 rounded-lg bg-sky-50 dark:bg-sky-900/20 text-sky-700 dark:text-sky-400 flex items-center gap-1.5 w-fit">
                <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-pulse"></span>
                {{ $pompaLabel[$item->status_pompa] ?? $item->status_pompa }}
            </div>
            @endif

            <div class="flex items-center justify-between gap-2 pt-1 border-t border-slate-200/50 dark:border-slate-700/50 mt-auto">
                <p class="text-[10px] text-slate-400 dark:text-slate-500">
                    {{ $item->terakhir_baca_at ? $item->terakhir_baca_at->diffForHumans() : 'Belum ada data' }}
                </p>
                <div class="flex items-center gap-1.5" onclick="event.stopPropagation()">
                    <button wire:click="toggleSimulasi({{ $item->id }})" title="{{ $item->status_simulasi === 'aktif' ? 'Hentikan simulasi' : 'Mulai simulasi' }}" class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition flex items-center justify-center">
                        @if($item->status_simulasi === 'aktif')
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @else
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-6.518-3.76A1 1 0 007 8.24v7.52a1 1 0 001.234.972l6.518-3.76a1 1 0 000-1.804zM21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @endif
                    </button>
                    <button wire:click="openEdit({{ $item->id }})" class="text-xs font-bold px-2.5 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition">Target</button>
                    <button wire:click="delete({{ $item->id }})" wire:confirm="Hapus tandon '{{ $item->nama }}'?" class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-red-100 dark:hover:bg-red-900/30 hover:text-red-500 dark:hover:text-red-400 transition">✕</button>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full glass-card rounded-2xl p-10 text-center text-sm text-slate-400 dark:text-slate-500">
            @if($search)
                Tidak ada tandon yang cocok dengan pencarian "{{ $search }}".
            @else
                Belum ada tandon. Tambahkan dulu lewat tombol "Tambah Tandon" di atas.
            @endif
        </div>
        @endforelse
    </div>

    @if($list->total() > 0)
    <div class="glass-card rounded-2xl p-4 lg:px-6 mt-4 flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
            <span>Tampilkan</span>
            <select wire:model.live="perPage" class="input-fancy px-3 py-1.5 rounded-lg text-xs outline-none w-auto">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
            <span>dari {{ $list->total() }} tandon</span>
        </div>
        <div class="flex items-center gap-2">
            <button wire:click="previousPage" @disabled($list->onFirstPage()) class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <span class="text-xs font-semibold text-slate-600 dark:text-slate-300 px-2">Hal {{ $list->currentPage() }} / {{ $list->lastPage() }}</span>
            <button wire:click="nextPage" @disabled(!$list->hasMorePages()) class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
    </div>
    @endif

    @else
    {{-- ===================== DETAIL VIEW ===================== --}}
    @php
        $ppmOk = $selected->ppm_terkini !== null && abs($selected->ppm_terkini - $selected->target_ppm) <= $tolPpm;
        $phOk = $selected->ph_terkini !== null && abs($selected->ph_terkini - $selected->target_ph) <= $tolPh;
    @endphp

    <button wire:click="backToList" class="mb-4 flex items-center gap-1.5 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke Daftar
    </button>

    <div class="glass-card rounded-2xl shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20 p-6 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-extrabold dark:text-white">{{ $selected->nama }}</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $selected->kebun->nama_kebun }}</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-[10px] font-bold px-3 py-1.5 rounded-full {{ $selected->status_simulasi === 'aktif' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' }}">
                {{ $selected->status_simulasi === 'aktif' ? 'Simulasi Live' : 'Simulasi Berhenti' }}
            </span>
            <button wire:click="toggleSimulasi({{ $selected->id }})" class="px-4 py-2 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition">
                {{ $selected->status_simulasi === 'aktif' ? 'Hentikan' : 'Mulai' }}
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="glass-card rounded-2xl p-5">
            <p class="text-[10px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wide">TDS (PPM)</p>
            <p class="text-2xl font-extrabold mt-1 {{ $selected->ppm_terkini === null ? 'text-slate-400' : ($ppmOk ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400') }}">{{ $selected->ppm_terkini ?? '-' }}</p>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Target {{ $selected->target_ppm }} ppm</p>
        </div>
        <div class="glass-card rounded-2xl p-5">
            <p class="text-[10px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wide">pH</p>
            <p class="text-2xl font-extrabold mt-1 {{ $selected->ph_terkini === null ? 'text-slate-400' : ($phOk ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400') }}">{{ $selected->ph_terkini ?? '-' }}</p>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Target {{ $selected->target_ph }}</p>
        </div>
        <div class="glass-card rounded-2xl p-5">
            <p class="text-[10px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wide">Suhu</p>
            <p class="text-2xl font-extrabold mt-1 text-slate-600 dark:text-slate-300">{{ $selected->suhu_terkini ?? '-' }}°C</p>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                {{ $selected->terakhir_baca_at ? 'Update '.$selected->terakhir_baca_at->diffForHumans() : 'Belum ada pembacaan' }}
            </p>
        </div>
    </div>

    @if($selected->status_pompa)
    <div class="glass-card rounded-2xl p-4 mb-6 flex items-center gap-2.5 bg-sky-50/60 dark:bg-sky-900/10">
        <span class="w-2 h-2 rounded-full bg-sky-500 animate-pulse"></span>
        <p class="text-sm font-bold text-sky-700 dark:text-sky-400">Auto-dosing aktif: {{ $pompaLabel[$selected->status_pompa] ?? $selected->status_pompa }}</p>
    </div>
    @endif

    <div class="glass-card rounded-2xl shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20 p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
            <div>
                <h3 class="font-extrabold text-sm dark:text-white">Riwayat Sensor</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Buat lihat jam berapa ada lonjakan PPM/pH/suhu. Data lebih dari 7 hari otomatis dihapus supaya tidak membebani database.</p>
            </div>
            <select wire:model.live="rentangGrafik" class="input-fancy px-3 py-2 rounded-xl text-xs outline-none w-fit">
                <option value="24jam">24 Jam Terakhir</option>
                <option value="7hari">7 Hari Terakhir</option>
            </select>
        </div>

        <div wire:ignore
             x-data="tandonChart()"
             x-init="init({{ $selected->id }})"
             x-on:tandon-grafik-data.window="onData($event.detail)"
             class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="rounded-xl bg-slate-50 dark:bg-slate-800 p-3">
                <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-2">PPM</p>
                <div class="h-40"><canvas x-ref="canvasPpm"></canvas></div>
            </div>
            <div class="rounded-xl bg-slate-50 dark:bg-slate-800 p-3">
                <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-2">pH</p>
                <div class="h-40"><canvas x-ref="canvasPh"></canvas></div>
            </div>
            <div class="rounded-xl bg-slate-50 dark:bg-slate-800 p-3">
                <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-2">Suhu</p>
                <div class="h-40"><canvas x-ref="canvasSuhu"></canvas></div>
            </div>
            <p x-show="kosong" class="lg:col-span-3 text-center text-xs text-slate-400 dark:text-slate-500 py-6" style="display:none;">Belum ada data riwayat untuk rentang ini.</p>
        </div>
    </div>

    <div class="glass-card rounded-2xl shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20 p-6">
        <h3 class="font-extrabold text-sm dark:text-white mb-4">Ubah Target Otomatis</h3>
        <form wire:submit="save" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <input type="hidden" wire:model="id_kebun_form">
            <input type="hidden" wire:model="nama_form">
            <div>
                <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Target PPM</label>
                <input type="number" wire:model="target_ppm_form" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                @error('target_ppm_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Target pH</label>
                <input type="number" step="0.1" wire:model="target_ph_form" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                @error('target_ph_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-2 pt-2 border-t border-slate-200/50 dark:border-slate-700/50">
                <p class="text-xs font-bold text-slate-600 dark:text-slate-300">Timing Auto-Dosing</p>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Atur berapa lama pompa menyala & berapa lama tunggu sebelum dicek ulang. Rekomendasi default cocok buat tandon kecil-menengah dengan sirkulasi aktif - perbesar jeda cek kalau tandon lebih besar/tanpa sirkulasi.</p>
            </div>
            <div>
                <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Durasi Pompa Dosing (detik)</label>
                <input type="number" wire:model="durasi_dosing_detik_form" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                <p class="mt-1 text-[10px] text-slate-400 dark:text-slate-500">Rekomendasi: 5 detik (pulse pendek, hindari overdosis sekali suntik)</p>
                @error('durasi_dosing_detik_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Jeda Cek Ulang (detik)</label>
                <input type="number" wire:model="jeda_cek_detik_form" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                <p class="mt-1 text-[10px] text-slate-400 dark:text-slate-500">Rekomendasi: 60 detik (waktu larutan bercampur sebelum dibaca ulang)</p>
                @error('jeda_cek_detik_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Maks Percobaan Sebelum Berhenti</label>
                <input type="number" wire:model="maks_percobaan_dosing_form" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                <p class="mt-1 text-[10px] text-slate-400 dark:text-slate-500">Rekomendasi: 5x (batas keamanan - kalau sensor error, dosing tidak akan terus-menerus)</p>
                @error('maks_percobaan_dosing_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-2">
                <button type="submit" class="btn-primary px-5 py-3 rounded-xl text-sm font-bold transition-all">Simpan Target</button>
            </div>
        </form>
    </div>

    {{-- ===================== PENGATURAN PERANGKAT IOT ===================== --}}
    <div class="glass-card rounded-2xl shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20 p-6 mt-6" x-data="{ copied: false }">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
            <div>
                <h3 class="font-extrabold text-sm dark:text-white">Pengaturan Perangkat IoT</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Hubungkan ESP32 sensor asli untuk tandon ini, menggantikan data simulasi.</p>
            </div>
            <button wire:click="ubahSumberData({{ $selected->id }})" wire:confirm="{{ $selected->sumber_data === 'iot' ? 'Kembalikan tandon ini ke mode simulasi?' : 'Pindahkan tandon ini ke mode IoT? Simulasi otomatis akan dihentikan.' }}" class="px-4 py-2 rounded-full text-xs font-bold {{ $selected->sumber_data === 'iot' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300' }} hover:opacity-80 transition">
                Mode: {{ $selected->sumber_data === 'iot' ? 'IoT (Sensor Asli)' : 'Simulasi' }} - klik untuk ganti
            </button>
        </div>

        @if($selected->sumber_data === 'iot')
        <div class="space-y-4">
            <div>
                <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">URL Endpoint (dipanggil ESP32 main lewat HTTP POST)</label>
                <div class="mt-1.5 flex items-center gap-2">
                    <input type="text" readonly value="{{ rtrim(config('app.url'), '/') }}/api/tandon/{{ $selected->id }}/bacaan" class="input-fancy flex-1 px-4 py-2.5 rounded-xl text-xs outline-none font-mono" onclick="this.select()">
                </div>
            </div>
            <div>
                <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Device Token (header <code class="font-mono">X-Device-Token</code>)</label>
                <div class="mt-1.5 flex items-center gap-2">
                    <input type="text" readonly value="{{ $selected->device_token }}" class="input-fancy flex-1 px-4 py-2.5 rounded-xl text-xs outline-none font-mono" onclick="this.select()">
                    <button wire:click="generateUlangToken({{ $selected->id }})" wire:confirm="Generate ulang token? ESP32 yang masih pakai token lama harus diupdate." class="shrink-0 text-xs font-bold px-3 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition">Generate Ulang</button>
                </div>
            </div>
            <div class="rounded-xl bg-slate-50 dark:bg-slate-800 p-4 text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                <p class="font-bold text-slate-600 dark:text-slate-300 mb-1.5">Alur yang disarankan:</p>
                <p>1. Tiap ESP32 tandon baca sensor (ppm/pH/suhu), publish ke broker MQTT gratis - direkomendasikan <a href="https://www.hivemq.com/mqtt-cloud-broker/" target="_blank" class="underline font-semibold">HiveMQ Cloud (free tier)</a>, topic unik per tandon mis. <code class="font-mono">kebunku/tandon/{{ $selected->device_token }}/sensor</code>.</p>
                <p class="mt-1">2. ESP32 main subscribe semua topic tandon di kebun ini, lalu forward tiap pembacaan ke URL endpoint di atas via HTTPS POST (header token di atas), body JSON <code class="font-mono">{"ppm":..,"ph":..,"suhu":..}</code>.</p>
                <p class="mt-1">3. Belum ada ESP32 fisik? Tes jalurnya dari terminal server: <code class="font-mono">php artisan tandon:uji-iot {{ $selected->id }} --kali=5 --jeda=5</code> - datanya akan muncul di kartu &amp; grafik atas secara realtime, persis seperti data asli nanti.</p>
            </div>

            <div class="rounded-xl bg-slate-50 dark:bg-slate-800 p-4 text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                <p class="font-bold text-slate-600 dark:text-slate-300 mb-2">Rekomendasi pin ESP32 per sensor:</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <div class="rounded-lg bg-white dark:bg-slate-900 p-2.5">
                        <p class="font-bold text-slate-700 dark:text-slate-200">Sensor TDS/EC (ppm)</p>
                        <p class="mt-0.5">Pin analog <span class="font-mono font-semibold">GPIO34</span> (ADC1, input-only)</p>
                    </div>
                    <div class="rounded-lg bg-white dark:bg-slate-900 p-2.5">
                        <p class="font-bold text-slate-700 dark:text-slate-200">Sensor pH</p>
                        <p class="mt-0.5">Pin analog <span class="font-mono font-semibold">GPIO33</span> (ADC1, channel beda dari TDS)</p>
                    </div>
                    <div class="rounded-lg bg-white dark:bg-slate-900 p-2.5">
                        <p class="font-bold text-slate-700 dark:text-slate-200">Sensor Suhu (DS18B20)</p>
                        <p class="mt-0.5">Pin digital <span class="font-mono font-semibold">GPIO4</span> (OneWire) + resistor pull-up 4.7kΩ wajib</p>
                    </div>
                    <div class="rounded-lg bg-white dark:bg-slate-900 p-2.5">
                        <p class="font-bold text-slate-700 dark:text-slate-200">Modul Relay (auto-dosing)</p>
                        <p class="mt-0.5">Pin digital <span class="font-mono font-semibold">GPIO25/26/27</span> (hindari pin strapping 0,2,12,15)</p>
                    </div>
                </div>
                <p class="mt-2.5 text-[11px]">⚠️ Sensor analog (TDS, pH) <span class="font-semibold">wajib</span> di pin ADC1 (GPIO32-39) - JANGAN ADC2 (GPIO0,2,4,12-15,25-27), karena ADC2 sering bentrok/ngaco pembacaannya kalau WiFi ESP32 aktif.</p>
            </div>
        </div>
        @else
        <p class="text-xs text-slate-500 dark:text-slate-400">Tandon ini masih pakai data simulasi otomatis. Ganti ke mode IoT begitu ESP32 &amp; sensornya siap dipasang.</p>
        @endif
    </div>
    @endif

    {{-- MODAL TAMBAH/EDIT TANDON --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div wire:click="$set('showModal', false)" class="modal-backdrop absolute inset-0"></div>
        <div class="modal-content relative w-full sm:max-w-sm bg-white dark:bg-slate-800 rounded-t-2xl sm:rounded-2xl p-6 sm:p-7 shadow-2xl border border-white/50 dark:border-slate-700/50">
            <div class="flex justify-between items-start mb-4">
                <h3 class="font-extrabold text-lg dark:text-white">{{ $isEditMode ? 'Edit Tandon' : 'Tambah Tandon' }}</h3>
                <button type="button" wire:click="$set('showModal', false)" class="w-8 h-8 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center transition text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">✕</button>
            </div>
            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Kebun</label>
                    <select wire:model="id_kebun_form" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                        <option value="">-- pilih kebun --</option>
                        @foreach($kebunList as $k)
                        <option value="{{ $k->id }}">{{ $k->nama_kebun }}</option>
                        @endforeach
                    </select>
                    @error('id_kebun_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Nama Tandon</label>
                    <input wire:model="nama_form" placeholder="Contoh: Tandon A" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                    @error('nama_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Target PPM</label>
                        <input type="number" wire:model="target_ppm_form" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                        @error('target_ppm_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Target pH</label>
                        <input type="number" step="0.1" wire:model="target_ph_form" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                        @error('target_ph_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" wire:click="$set('showModal', false)" class="flex-1 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 py-3.5 rounded-xl text-sm font-bold transition dark:text-white">Batal</button>
                    <button type="submit" class="btn-primary flex-1 py-3.5 rounded-xl text-sm font-bold transition-all">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</x-dynamic-component>
