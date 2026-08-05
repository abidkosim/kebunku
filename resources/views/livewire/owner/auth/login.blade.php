<div x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }"
     x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))"
     :class="{ 'dark': darkMode }"
     class="panel-ui min-h-screen w-full flex items-center justify-center bg-gradient-to-br from-slate-100 via-slate-50 to-white dark:bg-slate-900 dark:from-slate-900 dark:via-slate-900 dark:to-slate-900 px-4 py-12 transition-colors duration-300">
    <div class="w-full max-w-md">
        {{-- Header --}}
        <div class="text-center mb-8">
            <div class="mx-auto w-14 h-14 bg-gradient-to-br from-slate-900 to-slate-700 rounded-2xl flex items-center justify-center mb-4 shadow-lg shadow-slate-900/20">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Login Kebunku</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">Untuk Owner, Teknisi, dan Keuangan</p>
        </div>

        {{-- Card --}}
        <div class="glass-card rounded-2xl shadow-lg shadow-slate-200/50 dark:shadow-slate-800/20 p-8">
            <form wire:submit="login" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Username</label>
                    <input wire:model="username" type="text" placeholder="username" class="input-fancy w-full px-4 py-3 rounded-xl text-sm outline-none">
                    @error('username') <p class="mt-2 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Password</label>
                    <div class="relative">
                        <input wire:model="password" :type="$showPassword ? 'text' : 'password'" placeholder="••••••••" class="input-fancy w-full pl-4 pr-11 py-3 rounded-xl text-sm outline-none">
                        <button type="button" wire:click="$toggle('showPassword')" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        </button>
                    </div>
                    @error('password') <p class="mt-2 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <label class="flex items-center gap-2 cursor-pointer w-fit">
                    <input wire:model="remember" type="checkbox" class="w-4 h-4 rounded border-slate-300 dark:border-slate-600 text-slate-900 focus:ring-slate-900">
                    <span class="text-sm text-slate-600 dark:text-slate-400">Ingat saya</span>
                </label>

                <button type="submit" wire:loading.attr="disabled" class="btn-primary w-full py-3.5 rounded-xl text-sm font-bold transition-all disabled:opacity-70">
                    <span wire:loading.remove>Login Sekarang</span>
                    <span wire:loading>Memproses...</span>
                </button>

                <p class="text-center text-xs text-slate-400 dark:text-slate-500 pt-2">
                    Akun Owner dibuat oleh Superadmin, akun Teknisi/Keuangan dibuat oleh Owner
                </p>
            </form>
        </div>
    </div>
</div>
