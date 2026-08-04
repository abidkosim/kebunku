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
        Schema::table('tandons', function (Blueprint $table) {
            $table->unsignedInteger('durasi_dosing_detik')->default(5)->after('status_pompa');
            $table->unsignedInteger('jeda_cek_detik')->default(60)->after('durasi_dosing_detik');
            $table->unsignedTinyInteger('maks_percobaan_dosing')->default(5)->after('jeda_cek_detik');
            $table->unsignedTinyInteger('percobaan_dosing_saat_ini')->default(0)->after('maks_percobaan_dosing');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tandons', function (Blueprint $table) {
            $table->dropColumn(['durasi_dosing_detik', 'jeda_cek_detik', 'maks_percobaan_dosing', 'percobaan_dosing_saat_ini']);
        });
    }
};
