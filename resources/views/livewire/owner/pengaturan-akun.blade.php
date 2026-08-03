<x-dynamic-component :component="$actorType === 'owner' ? 'owner.shell' : 'staff.shell'" :owner="$owner" active="akun" :logs="$logs" :actor-type="$actorType" :actor-nama="$actorNama" :actor-foto-url="$actorFotoUrl">

    <div class="mb-6">
        <h3 class="font-extrabold text-lg flex items-center gap-2 dark:text-white">
            <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Pengaturan Akun
        </h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Kelola profil, foto, dan password akunmu sendiri</p>
    </div>

    <div class="glass-card rounded-2xl shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20 p-6 max-w-2xl">
        <form wire:submit="save" class="space-y-5">
            <div class="flex items-center gap-4">
                <div class="relative">
                    @if($foto_upload)
                        <img src="{{ $foto_upload->temporaryUrl() }}" class="w-20 h-20 rounded-full object-cover ring-2 ring-slate-200 dark:ring-slate-700">
                    @else
                        <x-avatar :name="$nama_form" :photo="$fotoUrlSaatIni" size="w-20 h-20" />
                    @endif
                </div>
                <div>
                    <label class="cursor-pointer inline-flex items-center gap-2 text-xs font-bold px-4 py-2.5 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                        Ganti Foto
                        <input type="file" wire:model="foto_upload" accept="image/*" class="hidden">
                    </label>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1.5">JPG/PNG, maks 2MB</p>
                    @error('foto_upload') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Nama</label>
                    <input wire:model="nama_form" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                    @error('nama_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Username</label>
                    <input wire:model="username_form" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                    @error('username_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            @if($actorType === 'owner')
            <div>
                <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Nama Usaha</label>
                <input wire:model="namaUsaha_form" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                @error('namaUsaha_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            @endif

            <div>
                <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Alamat</label>
                <textarea wire:model="alamat_form" rows="2" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none"></textarea>
                @error('alamat_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div class="pt-2 border-t border-slate-200/50 dark:border-slate-700/50">
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 mb-3">Ganti Password (opsional)</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Password Baru</label>
                        <input type="password" wire:model="passwordBaru_form" placeholder="Kosongkan jika tidak ganti" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                        @error('passwordBaru_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Konfirmasi Password Baru</label>
                        <input type="password" wire:model="passwordKonfirmasi_form" class="input-fancy mt-1.5 w-full px-4 py-3 rounded-xl text-sm outline-none">
                        @error('passwordKonfirmasi_form') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="btn-primary px-6 py-3 rounded-xl text-sm font-bold transition-all">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</x-dynamic-component>
