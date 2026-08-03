<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tanaman', function (Blueprint $table) {
            // saat diisi, siklus tanam ini resmi ditutup (sudah dipanen tuntas) - meja bebas lagi,
            // tapi baris tanaman & riwayat tahapnya tetap tersimpan sebagai histori
            $table->timestamp('siklus_selesai_at')->nullable()->after('catatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tanaman', function (Blueprint $table) {
            $table->dropColumn('siklus_selesai_at');
        });
    }
};
