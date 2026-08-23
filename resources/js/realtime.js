/**
 * Laravel Echo + Pusher (klien WebSocket ke Laravel Reverb).
 *
 * Dipisah dari bundle inti dan HANYA dimuat di halaman yang benar-benar punya listener
 * realtime, yaitu yang komponennya memakai atribut #[On('echo:...')]:
 *   - Monitor Tandon (owner)   -> pages/owner-tandon.blade.php
 *   - Galeri                   -> pages/owner-galeri.blade.php
 *   - Monitor Publik           -> pages/monitor-publik.blade.php
 *
 * Halaman lain sebelumnya ikut mengunduh pustaka ini DAN membuka koneksi WebSocket
 * walau tidak ada satu pun listener yang mendengarkan - beban jaringan (dan satu
 * koneksi menganggur ke server Reverb) yang murni sia-sia.
 */
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
    enabledTransports: ['ws', 'wss'],
});
