<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Thumbnail kecil untuk foto kunjungan. Daftar Absensi sebelumnya memuat foto asli
 * kamera HP (5-8 MB per foto, dan dirender 2x: tabel desktop + kartu mobile) hanya
 * untuk kotak 48-56 px - itu penyebab utama halaman Absensi terasa sangat berat.
 * Nullable: selama thumbnail belum jadi, tampilan jatuh ke foto asli.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->string('thumbnail')->nullable()->after('foto');
        });
    }

    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->dropColumn('thumbnail');
        });
    }
};
