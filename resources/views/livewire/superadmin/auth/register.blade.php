<div class="max-w-md mx-auto mt-10 p-6 bg-white rounded shadow">
  <h1 class="text-xl font-bold mb-4">Register Superadmin</h1>

  @if(session('sukses'))
    <div class="bg-green-100 text-green-700 p-2 rounded mb-3">{{ session('sukses') }}</div>
  @endif

  <form wire:submit="register" class="space-y-3">
    <div>
      <label>Nama</label>
      <input type="text" wire:model="nama" class="input input-bordered w-full">
      @error('nama') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>
    <div>
      <label>Username</label>
      <input type="text" wire:model="username" class="input input-bordered w-full">
      @error('username') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>
    <div>
      <label>Password</label>
      <input type="password" wire:model="password" class="input input-bordered w-full">
      @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>
    <button type="submit" class="btn btn-primary w-full">Daftar</button>
    <a href="/superadmin/login" class="text-sm text-blue-600">Sudah punya akun? Login</a>
  </form>
</div>