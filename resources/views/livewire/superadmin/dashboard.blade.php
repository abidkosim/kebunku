<div class="min-h-screen bg-[#f6f7f9] text-slate-900 font-sans antialiased">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400&display=swap');
        *{font-family:'Inter',sans-serif}
       .mono{font-family:'JetBrains Mono',monospace}
    </style>

    {{-- Navbar --}}
    <nav class="bg-white/80 backdrop-blur-xl border-b border-slate-200/80 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 lg:px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-9 h-9 bg-slate-900 rounded-xl flex items-center justify-center text-white font-bold shadow-lg shadow-slate-900/20">S</div>
                <div class="hidden sm:block">
                    <p class="font-bold text-sm leading-none tracking-tight">Superadmin Panel</p>
                    <p class="text-xs text-slate-500 mt-1 mono">SECURE • v2.1 POWER</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="hidden md:flex items-center gap-3 pl-3 pr-1 py-1 bg-slate-100 rounded-full border border-slate-200">
                    <div class="text-right">
                        <p class="text-sm font-semibold leading-none">{{ session('superadmin_nama') }}</p>
                    </div>
                    <x-avatar :name="session('superadmin_nama')" size="w-8 h-8" />
                </div>
                <button wire:click="logout" class="w-9 h-9 rounded-full bg-white border border-slate-200 hover:bg-red-50 hover:text-red-500 hover:border-red-200 flex items-center justify-center transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 0 0013.5 3h-6a2.25 0 00-2.25 2.25v13.5A2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/></svg>
                </button>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 lg:px-6 py-6 lg:py-8">
        <div class="grid grid-cols-12 gap-6 items-start">

            {{-- Left --}}
            <div class="col-span-12 lg:col-span-4 space-y-6">
                {{-- Stats --}}
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-[0_2px_20px_rgba(0,0,0,0.04)]">
                        <p class="text-xs mono font-bold tracking-widest text-slate-400">TOTAL</p>
                        <p class="text-2xl font-bold mt-2">{{ $list->count() }}</p>
                        <p class="text-xs text-slate-500 mt-1">Akun terdaftar</p>
                    </div>
                    <div class="bg-slate-900 rounded-2xl p-5 text-white shadow-[0_8px_30px_rgba(0,0,0,0.2)] relative overflow-hidden">
                        <div class="absolute -right-6 -top-6 w-20 h-20 bg-white/10 blur-2xl rounded-full"></div>
                        <p class="text-xs mono font-bold tracking-widest text-white/50">STATUS</p>
                        <p class="text-2xl font-semibold mt-2 flex items-center gap-2"><span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></span> Online</p>
                        <p class="text-xs text-white/60 mt-1 mono">{{ now()->format('H:i • d M') }}</p>
                    </div>
                </div>

                {{-- My Account --}}
                <div class="bg-white rounded-2xl border border-slate-200 shadow-[0_2px_20px_rgba(0,0,0,0.04)] overflow-hidden">
                    <div class="p-6 pb-4">
                        <h3 class="font-bold text-lg">Akun Saya</h3>
                        <p class="text-xs text-slate-500 mt-1">Kelola kredensial pribadimu</p>
                    </div>
                    <form wire:submit="updateAkunPribadi" class="p-6 pt-2 space-y-4">
                        <div>
                            <label class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Nama Lengkap</label>
                            <input wire:model="nama" class="mt-2 w-full px-4 py-3 bg-[#f6f7f9] border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-4 focus:ring-slate-900/10 focus:border-slate-900 outline-none transition">
                        </div>
                        <div>
                            <label class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Username</label>
                            <input wire:model="username" class="mt-2 w-full px-4 py-3 bg-[#f6f7f9] border border-slate-200 rounded-xl text-sm mono focus:bg-white focus:ring-4 focus:ring-slate-900/10 focus:border-slate-900 outline-none transition">
                        </div>
                        <div>
                            <label class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Password Baru</label>
                            <input wire:model="password_baru" type="password" placeholder="••••••••" class="mt-2 w-full px-4 py-3 bg-[#f6f7f9] border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-4 focus:ring-slate-900/10 focus:border-slate-900 outline-none transition">
                        </div>
                        <button class="w-full bg-slate-900 hover:bg-black text-white py-3.5 rounded-xl text-sm font-semibold shadow-[0_4px_14px_rgba(0,0,0,0.15)] hover:shadow-[0_6px_20px_rgba(0,0,0,0.2)] transition-all">Simpan Perubahan</button>
                    </form>
                </div>
            </div>

            {{-- Right: Table --}}
            <div class="col-span-12 lg:col-span-8">
                <div class="bg-white rounded-2xl border border-slate-200 shadow-[0_2px_20px_rgba(0,0,0,0.04)] overflow-hidden">
                    <div class="p-5 lg:p-6 flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-100">
                        <div>
                            <h3 class="font-bold text-lg tracking-tight">Manajemen Superadmin</h3>
                            <p class="text-xs text-slate-500 mt-1 hidden sm:block">Kelola semua akses superadmin dalam satu tempat</p>
                        </div>
                        <div class="flex items-center gap-2 w-full lg:w-auto">
                            <div class="relative flex-1 lg:w-64">
                                
<input wire:model.live.debounce.300ms="search" placeholder="Cari nama / username..." class="w-full lg:w-[260px] pl-9 pr-4 py-2.5 bg-[#f6f7f9] border border-slate-200 rounded-full text-[13px] focus:bg-white focus:ring-4 focus:ring-slate-900/10 focus:border-slate-900 outline-none transition">
                                <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                            </div>
                            <button wire:click="openCreate" class="shrink-0 bg-slate-900 text-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-black shadow-[0_4px_14px_rgba(0,0,0,0.15)] transition">+ Tambah</button>
                        </div>
                    </div>

                    {{-- DESKTOP TABLE --}}
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-[#fcfcfd]">
                                <tr class="text-xs mono tracking-widest text-slate-400">
                                    <th class="px-6 py-3.5 font-semibold">USER</th>
                                    <th class="px-6 py-3.5 font-semibold">USERNAME</th>
                                    <th class="px-6 py-3.5 font-semibold">DIBUAT</th>
                                    <th class="px-6 py-3.5 font-semibold text-right">AKSI</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($list as $item)
                                <tr class="hover:bg-slate-50/70 group transition">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <x-avatar :name="$item->nama" />
                                            <div>
                                                <p class="text-sm font-semibold flex items-center gap-2">{{ $item->nama }} @if($item->id == session('superadmin_id')) <span class="bg-slate-900 text-white text-[10px] px-1.5 py-0.5 rounded-full">YOU</span> @endif</p>
                                                <p class="text-xs mono text-slate-500">#{{ str_pad($item->id, 3, '0', STR_PAD_LEFT) }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm mono text-slate-600">{{ $item->username }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-500">{{ $item->created_at->format('d M Y') }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-end gap-1.5 opacity-0 group-hover:opacity-100 transition">
                                            <button wire:click="openEdit({{ $item->id }})" class="w-8 h-8 rounded-full bg-white border border-slate-200 hover:bg-slate-900 hover:text-white hover:border-slate-900 shadow-sm flex items-center justify-center transition">✎</button>
                                            <button wire:click="delete({{ $item->id }})" wire:confirm="Yakin hapus {{ $item->nama }}?" class="w-8 h-8 rounded-full bg-white border border-slate-200 hover:bg-red-500 hover:text-white hover:border-red-500 shadow-sm flex items-center justify-center transition">✕</button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- MOBILE CARD LIST --}}
                    <div class="md:hidden divide-y divide-slate-100">
                        @foreach($list as $item)
                        <div class="p-5 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <x-avatar :name="$item->nama" />
                                <div>
                                    <p class="text-sm font-semibold">{{ $item->nama }}</p>
                                    <p class="text-xs mono text-slate-500">@{{ $item->username }} • {{ $item->created_at->format('d M') }}</p>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button wire:click="openEdit({{ $item->id }})" class="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center">✎</button>
                                <button wire:click="delete({{ $item->id }})" wire:confirm="Hapus?" class="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center">✕</button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div wire:click="$set('showModal', false)" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"></div>
        <div class="relative w-full sm:max-w-md bg-white rounded-t-2xl sm:rounded-2xl p-6 sm:p-7 shadow-[0_20px_80px_rgba(0,0,0,0.2)] border border-slate-200 animate-[slideUp_0.3s_ease]">
            <div class="w-10 h-1 bg-slate-200 rounded-full mx-auto mb-6 sm:hidden"></div>
            <h3 class="font-bold text-lg">{{ $isEditMode? 'Edit Superadmin' : 'Tambah Superadmin' }}</h3>
            <p class="text-sm text-slate-500 mt-1 mb-6">Password otomatis di-hash bcrypt</p>
            <form wire:submit="save" class="space-y-4">
                <div><label class="text-xs font-semibold text-slate-500">Nama</label><input wire:model="nama_form" class="mt-1.5 w-full px-4 py-3 bg-[#f6f7f9] border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-4 focus:ring-slate-900/10 outline-none"></div>
                <div><label class="text-xs font-semibold text-slate-500">Username</label><input wire:model="username_form" class="mt-1.5 w-full px-4 py-3 bg-[#f6f7f9] border border-slate-200 rounded-xl text-sm mono focus:bg-white focus:ring-4 focus:ring-slate-900/10 outline-none"></div>
                <div><label class="text-xs font-semibold text-slate-500">Password</label><input wire:model="password_form" type="password" placeholder="{{ $isEditMode? 'Kosongkan jika tidak ganti' : 'Min. 6 karakter' }}" class="mt-1.5 w-full px-4 py-3 bg-[#f6f7f9] border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-4 focus:ring-slate-900/10 outline-none"></div>
                <div class="flex gap-3 pt-2">
                    <button type="button" wire:click="$set('showModal', false)" class="flex-1 bg-slate-100 hover:bg-slate-200 py-3.5 rounded-xl text-sm font-semibold transition">Batal</button>
                    <button type="submit" class="flex-1 bg-slate-900 hover:bg-black text-white py-3.5 rounded-xl text-sm font-semibold shadow-lg transition">{{ $isEditMode? 'Update' : 'Simpan' }}</button>
                </div>
            </form>
        </div>
    </div>
    <style>@keyframes slideUp{from{transform:translateY(20px);opacity:0}to{transform:translateY(0);opacity:1}}</style>
    @endif
</div>