<x-dynamic-component :component="$actorType === 'owner' ? 'owner.shell' : 'staff.shell'" :owner="$owner" active="absensi" :logs="$logs" :actor-type="$actorType" :actor-nama="$actorNama" :actor-foto-url="$actorFotoUrl">

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="font-extrabold text-lg flex items-center gap-2 dark:text-white">
                <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0zM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                Absensi Kunjungan
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                @if($actorType === 'teknisi')
                    Catat tiap kunjungan ke kebun - foto & lokasi diambil otomatis saat itu juga.
                @else
                    Riwayat kunjungan Teknisi ke kebun - foto, lokasi, dan waktu tercatat otomatis. Rekap ini bersifat lihat-saja.
                @endif
            </p>
        </div>
        @if($actorType === 'teknisi')
        <button wire:click="openCatat" class="btn-primary px-4 py-2.5 rounded-full text-xs font-bold shadow-md transition-all flex items-center gap-1.5 w-fit shrink-0">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Catat Kunjungan
        </button>
        @endif
    </div>

    {{-- Filter periode --}}
    <div class="glass-card rounded-2xl p-4 mb-4 flex flex-col sm:flex-row sm:items-center gap-3">
        <div class="flex items-center gap-2 flex-1">
            <input type="date" wire:model.live="dariTanggal" class="input-fancy px-3 py-2 rounded-xl text-xs outline-none w-full">
            <span class="text-xs text-slate-400">s/d</span>
            <input type="date" wire:model.live="sampaiTanggal" class="input-fancy px-3 py-2 rounded-xl text-xs outline-none w-full">
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <button wire:click="setPeriode('bulan-ini')" class="text-xs font-bold px-3 py-2 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition whitespace-nowrap">Bulan Ini</button>
            <button wire:click="setPeriode('tahun-ini')" class="text-xs font-bold px-3 py-2 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition whitespace-nowrap">Tahun Ini</button>
            <button wire:click="setPeriode('semua')" class="text-xs font-bold px-3 py-2 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition whitespace-nowrap">Semua</button>
        </div>
    </div>

    {{-- Rekap per karyawan (periode terpilih) - klik kartu untuk mempersempit daftar ke orang itu --}}
    @if($rekapTeknisi->isNotEmpty())
    <div class="mb-6">
        <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide mb-2">Rekap per Karyawan (periode terpilih)</p>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
            @foreach($rekapTeknisi as $rekap)
                @php $aktif = (string) $filterTeknisiId === (string) $rekap['actor_id']; @endphp
                <button type="button" wire:click="filterKeTeknisi({{ $rekap['actor_id'] }})"
                        class="text-left rounded-2xl p-4 shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20 transition {{ $aktif ? 'bg-slate-900 dark:bg-slate-600 text-white' : 'glass-card hover:-translate-y-0.5' }}">
                    <p class="text-xs font-bold truncate {{ $aktif ? 'text-white' : 'dark:text-white' }}">{{ $rekap['actor_nama'] }}</p>
                    <p class="text-2xl font-extrabold mt-1 {{ $aktif ? 'text-white' : 'dark:text-white' }}">{{ $rekap['jumlah'] }}</p>
                    <p class="text-[10px] mt-0.5 {{ $aktif ? 'text-slate-300' : 'text-slate-400 dark:text-slate-500' }}">kunjungan • terakhir {{ \Illuminate\Support\Carbon::parse($rekap['terakhir'])->diffForHumans() }}</p>
                </button>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Pencarian + filter Teknisi/Kebun --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-4">
        <div class="relative flex-1">
            <input wire:model.live.debounce.300ms="search" placeholder="Cari kegiatan..." class="input-fancy w-full pl-10 pr-4 py-2.5 rounded-full text-[13px] outline-none">
            <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
        <select wire:model.live="filterTeknisiId" class="input-fancy px-4 py-2.5 rounded-full text-xs outline-none">
            <option value="">Semua Karyawan</option>
            @foreach($teknisiList as $t)
                <option value="{{ $t->id }}">{{ $t->nama }}</option>
            @endforeach
        </select>
        <select wire:model.live="filterKebunId" class="input-fancy px-4 py-2.5 rounded-full text-xs outline-none">
            <option value="">Semua Kebun</option>
            @foreach($kebunList as $k)
                <option value="{{ $k->id }}">{{ $k->nama_kebun }}</option>
            @endforeach
        </select>
        @if($search || $filterTeknisiId || $filterKebunId)
            <button wire:click="resetFilter" class="shrink-0 text-xs font-bold px-4 py-2.5 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition whitespace-nowrap">Reset Filter</button>
        @endif
    </div>

    <div class="glass-card rounded-2xl shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20 border-slate-200/60 dark:border-slate-700/50 overflow-hidden">
        <div class="hidden md:block table-scroll">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr class="text-[10px] mono tracking-[0.1em] text-slate-500 dark:text-slate-400">
                            <th scope="col" class="px-6 py-4 text-left">FOTO</th>
                            <th scope="col" class="px-6 py-4 text-left">TANGGAL &amp; JAM</th>
                            <th scope="col" class="px-6 py-4 text-left">NAMA</th>
                            <th scope="col" class="px-6 py-4 text-left">KEBUN</th>
                            <th scope="col" class="px-6 py-4 text-left">KEGIATAN</th>
                            <th scope="col" class="px-6 py-4 text-left">LOKASI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($list as $item)
                        <tr wire:key="absensi-row-{{ $item->id }}" class="group transition-all duration-200 dark:hover:bg-slate-800/30">
                            <td class="px-6 py-3 align-middle">
                                <a href="{{ $item->foto_url }}" target="_blank" rel="noopener" class="block w-12 h-12 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-700 shrink-0">
                                    <img src="{{ $item->foto_url }}" class="w-full h-full object-cover" alt="Foto kunjungan {{ $item->actor_nama }}">
                                </a>
                            </td>
                            <td class="px-6 py-4 align-middle">
                                <p class="text-sm font-bold dark:text-white">{{ $item->created_at->format('d M Y') }}</p>
                                <p class="text-xs mono text-slate-400 dark:text-slate-500">{{ $item->created_at->format('H:i') }}</p>
                            </td>
                            <td class="px-6 py-4 text-sm font-bold dark:text-white align-middle">{{ $item->actor_nama }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 align-middle">{{ $item->kebun?->nama_kebun ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 align-middle max-w-xs">{{ $item->kegiatan ?: '-' }}</td>
                            <td class="px-6 py-4 align-middle">
                                <a href="{{ $item->lokasi_maps_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-xs font-bold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0zM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                                    Lihat peta
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-6 py-10 text-center text-sm text-slate-400 dark:text-slate-500">{{ $search || $filterTeknisiId || $filterKebunId ? 'Tidak ada kunjungan yang cocok dengan filter ini.' : 'Belum ada kunjungan tercatat.' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="md:hidden divide-y divide-slate-100/70 dark:divide-slate-700/50">
            @forelse($list as $item)
            <div wire:key="absensi-card-{{ $item->id }}" class="p-4 flex items-start gap-3">
                <a href="{{ $item->foto_url }}" target="_blank" rel="noopener" class="block w-14 h-14 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-700 shrink-0">
                    <img src="{{ $item->foto_url }}" class="w-full h-full object-cover" alt="Foto kunjungan {{ $item->actor_nama }}">
                </a>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-bold dark:text-white truncate">{{ $item->actor_nama }}</p>
                    <p class="text-[10px] mono text-slate-400 dark:text-slate-500">{{ $item->created_at->format('d M Y') }} • {{ $item->created_at->format('H:i') }} @if($item->kebun) • {{ $item->kebun->nama_kebun }} @endif</p>
                    @if($item->kegiatan)
                        <p class="text-xs text-slate-600 dark:text-slate-300 mt-1">{{ $item->kegiatan }}</p>
                    @endif
                    <a href="{{ $item->lokasi_maps_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-[10px] font-bold text-slate-500 dark:text-slate-400 mt-1.5">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0zM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                        Lihat peta
                    </a>
                </div>
            </div>
            @empty
            <div class="p-10 text-center text-sm text-slate-400 dark:text-slate-500">{{ $search || $filterTeknisiId || $filterKebunId ? 'Tidak ada kunjungan yang cocok dengan filter ini.' : 'Belum ada kunjungan tercatat.' }}</div>
            @endforelse
        </div>

        @if($list->total() > 0)
        <div class="p-4 lg:px-6 flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-slate-200/50 dark:border-slate-700/50">
            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                <span>Tampilkan</span>
                <select wire:model.live="perPage" class="input-fancy px-3 py-1.5 rounded-lg text-xs outline-none w-auto">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span>dari {{ $list->total() }} kunjungan</span>
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
    </div>

    @if($actorType === 'teknisi' && $showModal)
    {{-- MODAL CATAT KUNJUNGAN --}}
    <div x-data="{
            uploading: false, progress: 0,
            lokasiStatus: 'mencari', lokasiAkurasi: null,
            kebunList: @js($kebunKoordinat),
            kebunTerdekat: null, jarakMeter: null,
            radiusMaks: {{ \App\Models\Kebun::RADIUS_ABSENSI_METER }},

            // Formula Haversine yang SAMA persis dengan App\Models\Kebun::jarakMeter() -
            // dipakai CUMA untuk feedback instan di layar (supaya Teknisi tidak perlu
            // menekan Simpan dulu baru tahu dia kejauhan). Keputusan final tetap dari
            // server (KelolaAbsensi::simpanAbsensi()), yang menghitung ulang jarak ini
            // sendiri dari data yang tersimpan - nilai di sini TIDAK PERNAH dikirim
            // ke server, jadi tidak bisa dipalsukan untuk melewati validasi.
            jarakMeterJs(lat1, lng1, lat2, lng2) {
                const R = 6371000;
                const dLat = (lat2 - lat1) * Math.PI / 180;
                const dLng = (lng2 - lng1) * Math.PI / 180;
                const a = Math.sin(dLat / 2) ** 2
                    + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLng / 2) ** 2;
                return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            },

            ambilLokasi() {
                this.lokasiStatus = 'mencari';
                if (!navigator.geolocation) {
                    this.lokasiStatus = 'gagal';
                    return;
                }
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        this.lokasiAkurasi = Math.round(pos.coords.accuracy);
                        $wire.set('lokasiLat_form', pos.coords.latitude);
                        $wire.set('lokasiLng_form', pos.coords.longitude);

                        if (this.kebunList.length === 0) {
                            this.lokasiStatus = 'tanpa_kebun';
                            return;
                        }

                        let terdekat = null, jarakMin = Infinity;
                        this.kebunList.forEach((k) => {
                            const j = this.jarakMeterJs(pos.coords.latitude, pos.coords.longitude, k.lat, k.lng);
                            if (j < jarakMin) { jarakMin = j; terdekat = k; }
                        });
                        this.kebunTerdekat = terdekat;
                        this.jarakMeter = Math.round(jarakMin);
                        this.lokasiStatus = jarakMin <= this.radiusMaks ? 'dalam_radius' : 'luar_radius';
                    },
                    () => { this.lokasiStatus = 'gagal'; },
                    { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
                );
            }
         }"
         x-init="ambilLokasi()"
         x-on:livewire-upload-start.window="uploading = true; progress = 0"
         x-on:livewire-upload-progress.window="progress = $event.detail.progress"
         x-on:livewire-upload-finish.window="uploading = false; progress = 100"
         x-on:livewire-upload-error.window="uploading = false"
         x-on:livewire-upload-cancel.window="uploading = false"
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div wire:click="$set('showModal', false)" class="modal-backdrop absolute inset-0"></div>
        <div class="modal-content relative w-full sm:max-w-sm bg-white dark:bg-slate-800 rounded-t-2xl sm:rounded-2xl p-6 sm:p-7 shadow-2xl border border-white/50 dark:border-slate-700/50 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-start mb-4">
                <h3 class="font-extrabold text-lg dark:text-white">Catat Kunjungan</h3>
                <button type="button" wire:click="$set('showModal', false)" class="w-8 h-8 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center transition text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">✕</button>
            </div>
            <form wire:submit="simpanAbsensi" class="space-y-4">
                {{-- Lokasi + jarak ke kebun terdekat --}}
                <div class="rounded-xl p-3 text-xs flex items-center justify-between gap-3"
                     :class="{
                        'bg-emerald-50 dark:bg-emerald-900/20': lokasiStatus === 'dalam_radius',
                        'bg-red-50 dark:bg-red-900/20': lokasiStatus === 'luar_radius' || lokasiStatus === 'gagal',
                        'bg-amber-50 dark:bg-amber-900/20': lokasiStatus === 'tanpa_kebun',
                        'bg-slate-50 dark:bg-slate-700': lokasiStatus === 'mencari',
                     }">
                    <div class="flex items-center gap-2 min-w-0">
                        <svg class="w-4 h-4 shrink-0" :class="{
                                'text-emerald-600 dark:text-emerald-400': lokasiStatus === 'dalam_radius',
                                'text-red-500': lokasiStatus === 'luar_radius' || lokasiStatus === 'gagal',
                                'text-amber-500': lokasiStatus === 'tanpa_kebun',
                                'text-slate-400 animate-pulse': lokasiStatus === 'mencari',
                             }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0zM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                        <span x-show="lokasiStatus === 'mencari'" class="text-slate-500 dark:text-slate-400">Mendeteksi lokasi...</span>
                        <span x-show="lokasiStatus === 'dalam_radius'" class="text-emerald-700 dark:text-emerald-400 font-semibold truncate">
                            Di <span x-text="kebunTerdekat?.nama"></span> (±<span x-text="jarakMeter"></span>m)
                        </span>
                        <span x-show="lokasiStatus === 'luar_radius'" class="text-red-600 dark:text-red-400 font-semibold truncate">
                            <span x-text="jarakMeter"></span>m dari <span x-text="kebunTerdekat?.nama"></span> (maks <span x-text="radiusMaks"></span>m)
                        </span>
                        <span x-show="lokasiStatus === 'tanpa_kebun'" class="text-amber-700 dark:text-amber-400 font-semibold">Owner belum atur lokasi kebun manapun</span>
                        <span x-show="lokasiStatus === 'gagal'" class="text-red-600 dark:text-red-400">Lokasi gagal dideteksi. Aktifkan izin lokasi.</span>
                    </div>
                    <button type="button" x-show="lokasiStatus !== 'mencari'" x-on:click="ambilLokasi()" class="shrink-0 text-[10px] font-bold px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600">Coba Lagi</button>
                </div>
                @error('lokasiLat_form') <p class="text-xs text-red-500">{{ $message }}</p> @enderror

                {{-- Foto --}}
                <div>
                    <label class="cursor-pointer flex flex-col items-center justify-center gap-2 border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-xl p-6 text-center hover:border-slate-400 dark:hover:border-slate-500 transition">
                        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400">
                            @if($foto_upload) {{ $foto_upload->getClientOriginalName() }} @else Ambil / pilih foto @endif
                        </span>
                        <input type="file" wire:model="foto_upload" accept="image/*" capture="environment" class="hidden">
                    </label>
                    <div x-show="uploading" class="mt-2" style="display:none;">
                        <div class="w-full h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-full bg-slate-900 dark:bg-emerald-500 transition-all duration-150" :style="`width: ${progress}%`"></div>
                        </div>
                    </div>
                    @error('foto_upload') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                @if($foto_upload)
                    <div class="rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-700 aspect-video flex items-center justify-center">
                        <img src="{{ $foto_upload->temporaryUrl() }}" class="max-h-full max-w-full object-contain">
                    </div>
                @endif

                {{-- Kegiatan --}}
                <div>
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Kegiatan (opsional)</label>
                    <textarea wire:model="kegiatan_form" rows="2" placeholder="Contoh: semprot hama meja 3-5" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none"></textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" wire:click="$set('showModal', false)" class="flex-1 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 py-3.5 rounded-xl text-sm font-bold transition dark:text-white">Batal</button>
                    <button type="submit" :disabled="uploading || lokasiStatus !== 'dalam_radius'" :class="{ 'opacity-50 cursor-not-allowed': uploading || lokasiStatus !== 'dalam_radius' }" class="btn-primary flex-1 py-3.5 rounded-xl text-sm font-bold transition-all">
                        <span x-show="!uploading">Simpan</span>
                        <span x-show="uploading" style="display:none;">Menunggu foto selesai...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

</x-dynamic-component>
