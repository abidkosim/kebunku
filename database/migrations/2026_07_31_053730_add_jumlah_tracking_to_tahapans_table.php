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
        Schema::table('tahapans', function (Blueprint $table) {
            // default 0 untuk backfill data lama (belum ada tracking jumlah saat itu)
            $table->unsignedInteger('jumlah_awal')->default(0)->after('jenis');
            $table->unsignedInteger('jumlah_lolos')->nullable()->after('tanggal_selesai_aktual');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tahapans', function (Blueprint $table) {
            $table->dropColumn(['jumlah_awal', 'jumlah_lolos']);
        });
    }
};
