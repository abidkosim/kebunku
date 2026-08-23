<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kebun mana yang tervalidasi radius-nya saat kunjungan dicatat (kebun terdekat dari
 * lokasi GPS staff, lihat App\Models\Kebun::terdekatDenganKoordinat()). Nullable +
 * nullOnDelete: kalau kebun itu nanti dihapus Owner, riwayat kunjungan yang sudah
 * tercatat TETAP UTUH (foto/lokasi/jam/actor semuanya masih ada) - cuma tautan ke
 * kebun-nya yang lepas, sesuai sifat Absensi yang permanen/tidak boleh hilang.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->foreignId('id_kebun')->nullable()->after('id_owners')
                ->constrained('kebun')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_kebun');
        });
    }
};
