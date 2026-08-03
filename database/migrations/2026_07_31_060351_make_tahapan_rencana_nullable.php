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
        // Tahap "panen" bersifat open-ended (ditutup manual, bukan berdasarkan target durasi),
        // jadi kolom rencana durasi/tanggal selesai perlu boleh kosong khusus untuk jenis ini.
        Schema::table('tahapans', function (Blueprint $table) {
            $table->unsignedInteger('durasi_rencana')->nullable()->change();
            $table->date('tanggal_selesai_rencana')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tahapans', function (Blueprint $table) {
            $table->unsignedInteger('durasi_rencana')->nullable(false)->change();
            $table->date('tanggal_selesai_rencana')->nullable(false)->change();
        });
    }
};
