import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
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
            fonts: [
                bunny('Inter', {
                    weights: [400, 500, 600, 700, 800],
                }),
                bunny('JetBrains Mono', {
                    weights: [400, 500],
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
