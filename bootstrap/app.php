<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'sudah.login' => \App\Http\Middleware\PastikanSudahLogin::class,
            'belum.login' => \App\Http\Middleware\PastikanBelumLogin::class,
        ]);

        // Produksi kini di depan Cloudflare Tunnel: pengunjung terhubung HTTPS ke edge
        // Cloudflare, lalu cloudflared (proses LOKAL di VPS) meneruskannya ke Nginx lewat
        // 127.0.0.1 - itu sebabnya cuma loopback yang perlu dipercaya di sini, BUKAN '*'
        // (trust-all). Aman dipercaya karena source IP 127.0.0.1 pada koneksi TCP tidak
        // bisa dipalsukan dari jaringan luar - siapa pun yang mengakses langsung lewat
        // port 9980 publik (jalur lama, masih hidup paralel) datang dengan IP asli
        // mereka sendiri, bukan 127.0.0.1, jadi X-Forwarded-* mereka tetap diabaikan.
        // Tanpa ini Laravel salah kira semua trafik HTTP polos walau pengunjung sudah
        // HTTPS ke Cloudflare - asset/redirect bisa salah skema (mixed content).
        $middleware->trustProxies(at: ['127.0.0.1', '::1']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
