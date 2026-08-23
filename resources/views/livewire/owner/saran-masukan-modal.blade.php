{{-- Hanya modalnya. Tombol pemicunya ada di <x-owner.tombol-saran />. --}}
<div>
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div wire:click="$set('showModal', false)" class="modal-backdrop absolute inset-0"></div>
        <div class="modal-content relative w-full sm:max-w-md bg-white dark:bg-slate-800 rounded-t-2xl sm:rounded-2xl p-6 sm:p-7 shadow-2xl border border-white/50 dark:border-slate-700/50">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="font-extrabold text-lg dark:text-white">Saran &amp; Masukan</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Langsung terkirim ke tim Kebunku (Superadmin)</p>
                </div>
                <button type="button" wire:click="$set('showModal', false)" class="w-8 h-8 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center transition text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">✕</button>
            </div>
            <form wire:submit="kirim" class="space-y-4">
                <div>
                    <textarea wire:model="pesan" rows="5" placeholder="Tulis saran, masukan, atau kendala yang Anda alami..." class="input-fancy w-full px-4 py-3 rounded-xl text-sm outline-none resize-none"></textarea>
                    @error('pesan') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div class="flex gap-3 pt-1">
                    <button type="button" wire:click="$set('showModal', false)" class="flex-1 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 py-3.5 rounded-xl text-sm font-bold transition dark:text-white">Batal</button>
                    <button type="submit" wire:loading.attr="disabled" class="btn-primary flex-1 py-3.5 rounded-xl text-sm font-bold transition-all disabled:opacity-70">Kirim</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
