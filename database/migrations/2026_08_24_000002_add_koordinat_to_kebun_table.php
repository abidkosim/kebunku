<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Titik koordinat kebun - dasar untuk validasi radius 20m di Absensi (lihat
 * App\Livewire\Owner\KelolaAbsensi). Nullable & TIDAK wajib diisi saat kebun dibuat -
 * kebun yang sudah ada sebelum fitur ini tetap jalan seperti biasa, cuma fitur Absensi
 * untuk kebun itu "terkunci" (lihat pesan error di KelolaAbsensi) sampai Owner
 * mengisi koordinatnya lewat Kelola Kebun & Meja.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kebun', function (Blueprint $table) {
            $table->decimal('lat', 10, 7)->nullable()->after('nama_kebun');
            $table->decimal('lng', 10, 7)->nullable()->after('lat');
        });
    }

    public function down(): void
    {
        Schema::table('kebun', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng']);
        });
    }
};
