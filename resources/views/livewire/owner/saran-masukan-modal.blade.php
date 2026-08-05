<div>
    <button wire:click="buka" type="button" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50 transition">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
        <span>Saran &amp; Masukan</span>
    </button>

    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div wire:click="$set('showModal', false)" class="modal-backdrop absolute inset-0"></div>
        <div class="modal-content relative w-full sm:max-w-md bg-white/90 dark:bg-slate-800/95 backdrop-blur-xl rounded-t-2xl sm:rounded-2xl p-6 sm:p-7 shadow-2xl border border-white/50 dark:border-slate-700/50">
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
