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
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 0 017.5 0zM4.501 20.118a7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Login Superadmin</h1>
            <p class="text-sm text-slate-500 mt-2">Gunakan username yang terdaftar di tabel superadmins</p>
        </div>

        {{-- Card --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-8">
            <form wire:submit="login" class="space-y-5">
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
                        <input wire:model="password" type="{{ $showPassword ? 'text' : 'password' }}" placeholder="••••••••"
                            class="w-full pl-4 pr-11 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-slate-900 transition-all">
                        <button type="button" wire:click="$toggle('showPassword')" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        </button>
                    </div>
                    @error('password') <p class="mt-2 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input wire:model="remember" type="checkbox" class="w-4 h-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                        <span class="text-sm text-slate-600">Ingat saya</span>
                    </label>
                </div>

                <button type="submit" wire:loading.attr="disabled"
                    class="w-full bg-slate-900 hover:bg-black text-white font-medium py-3 rounded-xl text-sm shadow-lg transition-all disabled:opacity-70">
                    <span wire:loading.remove>Login Sekarang</span>
                    <span wire:loading>Memproses...</span>
                </button>

                <p class="text-center text-sm text-slate-500 pt-2">
                    Belum punya akun? <a href="/superadmin/register" class="font-semibold text-slate-900 hover:underline">Daftar</a>
                </p>
            </form>
        </div>
    </div>
</div>
{{--
    SweetAlert2 diambil dari bundle aplikasi sendiri (window.tampilkanAlert), BUKAN dari
    CDN luar. Versi sebelumnya memuat <script src="cdn.jsdelivr.net/..."> di sini padahal
    pustaka yang sama sudah ikut ter-bundle - artinya satu permintaan ke server pihak
    ketiga yang memblokir render, plus pustaka yang sama diunduh dua kali.
--}}
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('login-success', (event) => {
                window.tampilkanAlert({
                    title: 'Login Berhasil!',
                    text: 'Selamat datang, ' + event.nama,
                    icon: 'success',
                    confirmButtonColor: '#0f172a',
                    confirmButtonText: 'Lanjut ke Dashboard'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "/superadmin/dashboard";
                    }
                });
            });
        });
    </script>