<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Model lokasi berubah total (teks bebas -> Kebun+Meja terstruktur),
        // data lama tidak bisa dipetakan otomatis sehingga direset (masih data uji coba).
        DB::table('jadwals')->delete();
        DB::table('tanaman')->delete();

        Schema::table('tanaman', function (Blueprint $table) {
            $table->dropColumn('lokasi');
            $table->foreignId('meja_id')->after('id_owners')->constrained('meja')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tanaman', function (Blueprint $table) {
            $table->dropConstrainedForeignId('meja_id');
            $table->string('lokasi')->nullable();
        });
    }
};
