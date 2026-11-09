<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Membuat thumbnail untuk foto Absensi & Galeri lama yang belum punya thumbnail.
 * Aman diulang - hanya memproses baris yang kolom thumbnail-nya masih kosong.
 * Dijalankan sekali setelah deploy fitur thumbnail Absensi (2026-11-09).
 */
Artisan::command('kebunku:buat-thumbnail', function () {
    $ownerTersentuh = [];

    \App\Models\Absensi::whereNull('thumbnail')->whereNotNull('foto')->orderBy('id')
        ->each(function ($item) use (&$ownerTersentuh) {
            (new \App\Jobs\BuatThumbnailAbsensi($item->id))->handle();
            $ok = $item->fresh()->thumbnail ? 'OK' : 'GAGAL';
            $this->line("Absensi #{$item->id}: {$ok}");
            $ownerTersentuh[$item->id_owners] = true;
        });

    \App\Models\Galeri::where('jenis', 'foto')->whereNull('thumbnail')->orderBy('id')
        ->each(function ($item) {
            (new \App\Jobs\BuatThumbnailGaleri($item->id))->handle();
            $this->line("Galeri #{$item->id}: ".($item->fresh()->status));
        });

    $this->info('Selesai. Owner yang cache absensinya dibersihkan: '.implode(',', array_keys($ownerTersentuh)));
})->purpose('Buat thumbnail untuk foto Absensi & Galeri yang belum punya');
