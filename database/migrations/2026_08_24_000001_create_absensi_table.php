<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Log kunjungan ke kebun oleh Teknisi - foto + lokasi GPS + jam, direkam otomatis
 * saat itu juga (bukan absen jam-kerja masuk/pulang). Bersifat dokumentasi: sekali
 * tercatat, TIDAK ADA fitur edit/hapus di aplikasi (lihat App\Livewire\Owner\KelolaAbsensi) -
 * supaya catatannya tetap bisa dipercaya sebagai bukti kunjungan riil.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_owners')->constrained('owners')->cascadeOnDelete();
            $table->string('actor_type'); // 'teknisi' untuk saat ini, generik seperti pola galeri/sarans
            $table->unsignedBigInteger('actor_id');
            $table->string('actor_nama');
            $table->string('foto');
            $table->decimal('lokasi_lat', 10, 7);
            $table->decimal('lokasi_lng', 10, 7);
            $table->text('kegiatan')->nullable();
            $table->timestamps();

            $table->index(['id_owners', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};
