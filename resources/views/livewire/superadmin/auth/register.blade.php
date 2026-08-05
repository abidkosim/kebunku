<div class="min-h-screen w-full flex items-center justify-center bg-slate-50 px-4 py-12">
    <div class="w-full max-w-md">
        @if(session('sukses'))
            <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-xl px-4 py-3">
                {{ session('sukses') }}
            </div>
        @endif
        {{-- Header --}}
        <div class="text-center mb-8">
            <div class="mx-auto w-14 h-14 bg-slate-900 rounded-2xl flex items-center justify-center mb-4 shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Daftar Superadmin</h1>
            <p class="text-sm text-slate-500 mt-2">Buat akun superadmin baru untuk mengelola Kebunku</p>
        </div>

        {{-- Card --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-8">
            <form wire:submit="register" class="space-y-5">
                {{-- Nama --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Nama</label>
                    <input wire:model="nama" type="text" placeholder="Nama lengkap"
                        class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-slate-900 transition-all">
                    @error('nama') <p class="mt-2 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Username --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Username</label>
                    <input wire:model="username" type="text" placeholder="superadmin01"
                        class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-slate-900 transition-all">
                    @error('username') <p class="mt-2 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Password</label>
                    <div class="relative">
                        <input wire:model="password" :type="$showPassword ? 'text' : 'password'" placeholder="••••••••"
                            class="w-full pl-4 pr-11 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-slate-900 transition-all">
                        <button type="button" wire:click="$toggle('showPassword')" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        </button>
                    </div>
                    @error('password') <p class="mt-2 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <button type="submit" wire:loading.attr="disabled"
                    class="w-full bg-slate-900 hover:bg-black text-white font-medium py-3 rounded-xl text-sm shadow-lg transition-all disabled:opacity-70">
                    <span wire:loading.remove>Daftar Sekarang</span>
                    <span wire:loading>Memproses...</span>
                </button>

                <p class="text-center text-sm text-slate-500 pt-2">
                    Sudah punya akun? <a href="/superadmin/login" class="font-semibold text-slate-900 hover:underline">Login</a>
                </p>
            </form>
        </div>
    </div>
</div>
