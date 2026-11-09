import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { local } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            // grafik.js & realtime.js adalah entry terpisah supaya Chart.js dan
            // Echo/Pusher tidak ikut masuk bundle inti yang dimuat setiap halaman.
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/realtime.js',
                'resources/js/grafik.js',
            ],
            refresh: true,
            // Font disimpan lokal (resources/fonts, subset latin dari Bunny) dan HANYA
            // woff2. Provider bunny() sebelumnya menulis @font-face woff2 + woff terpisah
            // untuk tiap bobot; browser memakai deklarasi terakhir (woff) sehingga setiap
            // font terunduh dua kali dan preload woff2-nya terbuang percuma.
            // Preload dibatasi ke 2 bobot yang paling banyak dipakai (font-bold & teks
            // biasa) - sisanya tetap dimuat saat dibutuhkan (font-display: swap).
            fonts: [
                local('Inter', {
                    variants: [400, 500, 600, 700, 800].map((weight) => ({
                        src: `resources/fonts/inter-${weight}.woff2`,
                        weight,
                    })),
                    preload: [{ weight: 400 }, { weight: 700 }],
                }),
                local('JetBrains Mono', {
                    variants: [400, 500].map((weight) => ({
                        src: `resources/fonts/jetbrains-mono-${weight}.woff2`,
                        weight,
                    })),
                    preload: false,
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
