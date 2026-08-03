<x-owner.shell :owner="$owner" active="user" :logs="$logs">
    <div class="glass-card rounded-2xl shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20 border-slate-200/60 dark:border-slate-700/50 overflow-hidden">
        <div class="p-5 lg:p-6 flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-200/50 dark:border-slate-700/50">
            <div>
                <h3 class="font-extrabold text-lg flex items-center gap-2 dark:text-white">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Manajemen User
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 hidden sm:block">Kelola anggota tim kebun "{{ $owner->nama_usaha }}"</p>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full lg:w-auto">
                <div class="relative flex-1 lg:w-64">
                    <input id="searchUser" wire:model.live.debounce.300ms="search" placeholder="Cari nama / username..." class="input-fancy w-full lg:w-[260px] pl-10 pr-4 py-2.5 rounded-full text-[13px] outline-none">
                    <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <button wire:click="openCreate" aria-label="Tambah User" class="btn-primary px-5 py-2.5 rounded-full text-sm font-bold shadow-md transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah User
                </button>
            </div>
        </div>

        {{-- DESKTOP TABLE --}}
        <div class="hidden md:block table-scroll">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr class="text-[10px] mono tracking-[0.1em] text-slate-500 dark:text-slate-400">
                            <th scope="col" class="px-6 py-4 text-left">NAMA</th>
                            <th scope="col" class="px-6 py-4 text-left">USERNAME</th>
                            <th scope="col" class="px-6 py-4 text-left">AKSES</th>
                            <th scope="col" class="px-6 py-4 text-left">DIBUAT</th>
                            <th scope="col" class="px-6 py-4 text-right">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($list as $item)
                        <tr class="group transition-all duration-200 dark:hover:bg-slate-800/30">
                            <td class="px-6 py-4 align-middle">
                                <div class="flex items-center gap-3">
                                    <x-avatar :name="$item->nama" />
                                    <p class="text-sm font-bold dark:text-white">{{ $item->nama }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm mono text-slate-600 dark:text-slate-300 align-middle font-medium">{{ $item->username }}</td>
                            <td class="px-6 py-4 align-middle">
                                @if($item->role === 'keuangan')
                                    <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-400">Keuangan</span>
                                @else
                                    <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Teknisi</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400 align-middle">{{ $item->created_at->format('d M Y') }}</td>
                            <td class="px-6 py-4 align-middle">
                                <div class="flex justify-end gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                    <button wire:click="openEdit({{ $item->id }})" aria-label="Edit user" class="action-btn w-8 h-8 rounded-full bg-white/70 dark:bg-slate-700/70 border border-slate-200 dark:border-slate-600 hover:bg-slate-900 dark:hover:bg-slate-600 hover:text-white dark:hover:text-white hover:border-slate-900 dark:hover:border-slate-500 shadow-sm flex items-center justify-center transition-all dark:text-slate-300">✎</button>
                                    <button wire:click="delete({{ $item->id }})" wire:confirm="Yakin hapus user {{ $item->nama }}?" aria-label="Hapus user" class="action-btn delete w-8 h-8 rounded-full bg-white/70 dark:bg-slate-700/70 border border-slate-200 dark:border-slate-600 hover:bg-red-500 hover:text-white hover:border-red-500 shadow-sm flex items-center justify-center transition-all dark:text-slate-300">✕</button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-6 py-10 text-center text-sm text-slate-400 dark:text-slate-500">Belum ada user</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- MOBILE CARD LIST --}}
        <div class="md:hidden divide-y divide-slate-100/70 dark:divide-slate-700/50">
            @forelse($list as $item)
            <div class="p-5 flex items-center justify-between gap-4 hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                <div class="flex items-center gap-3">
                    <x-avatar :name="$item->nama" />
                    <div>
                        <p class="text-sm font-bold dark:text-white">{{ $item->nama }}</p>
                        <p class="text-[10px] mono text-slate-500 dark:text-slate-400">{{ $item->username }} • {{ $item->role === 'keuangan' ? 'Keuangan' : 'Teknisi' }}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button wire:click="openEdit({{ $item->id }})" aria-label="Edit" class="w-9 h-9 rounded-full bg-slate-100/70 dark:bg-slate-700/50 flex items-center justify-center hover:bg-slate-200 dark:hover:bg-slate-600 transition dark:text-slate-300">✎</button>
                    <button wire:click="delete({{ $item->id }})" wire:confirm="Hapus?" aria-label="Hapus" class="w-9 h-9 rounded-full bg-slate-100/70 dark:bg-slate-700/50 flex items-center justify-center hover:bg-red-100 dark:hover:bg-red-900/30 hover:text-red-500 dark:hover:text-red-400 transition dark:text-slate-300">✕</button>
                </div>
            </div>
            @empty
            <div class="p-10 text-center text-sm text-slate-400 dark:text-slate-500">Belum ada user</div>
            @endforelse
        </div>
    </div>

    {{-- MODAL USER --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div wire:click="$set('showModal', false)" class="modal-backdrop absolute inset-0"></div>
        <div class="modal-content relative w-full sm:max-w-md bg-white/90 dark:bg-slate-800/95 backdrop-blur-xl rounded-t-2xl sm:rounded-2xl p-6 sm:p-7 shadow-2xl border border-white/50 dark:border-slate-700/50">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="font-extrabold text-lg dark:text-white">{{ $isEditMode ? 'Edit User' : 'Tambah User' }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Password otomatis di-hash bcrypt</p>
                </div>
                <button type="button" wire:click="$set('showModal', false)" aria-label="Tutup modal" class="w-8 h-8 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center transition text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">✕</button>
            </div>
            <form wire:submit="save" class="space-y-4">
                <div>
                    <label for="nama_form" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Nama</label>
                    <input id="nama_form" wire:model="nama_form" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                </div>
                <div>
                    <label for="username_form" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Username</label>
                    <input id="username_form" wire:model="username_form" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm mono outline-none">
                </div>
                <div>
                    <label for="password_form" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Password</label>
                    <input id="password_form" wire:model="password_form" type="password" placeholder="{{ $isEditMode ? 'Kosongkan jika tidak ganti' : 'Min. 6 karakter' }}" autocomplete="off" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                </div>
                <div>
                    <label for="alamat_form" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Alamat</label>
                    <textarea id="alamat_form" wire:model="alamat_form" rows="2" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none"></textarea>
                </div>
                <div>
                    <label for="role_form" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Role</label>
                    <select id="role_form" wire:model="role_form" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                        <option value="teknisi">Teknisi - akses semua Management Tanaman (termasuk Panen)</option>
                        <option value="keuangan">Keuangan - akses Dashboard, Keuangan, dan Laporan</option>
                    </select>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" wire:click="$set('showModal', false)" class="flex-1 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 py-3.5 rounded-xl text-sm font-bold transition dark:text-white">Batal</button>
                    <button type="submit" class="btn-primary flex-1 py-3.5 rounded-xl text-sm font-bold transition-all">{{ $isEditMode ? 'Update' : 'Simpan' }}</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</x-owner.shell>
