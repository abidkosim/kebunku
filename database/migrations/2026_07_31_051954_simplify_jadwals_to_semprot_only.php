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
        // Semai/tanam/panen sekarang ditangani tabel tahapans (siklus pertumbuhan).
        // Tabel jadwals selanjutnya murni untuk aktivitas berulang: semprot.
        Schema::table('jadwals', function (Blueprint $table) {
            $table->dropColumn('jenis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwals', function (Blueprint $table) {
            $table->enum('jenis', ['semai', 'tanam', 'semprot', 'panen'])->default('semprot');
        });
    }
};
