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
        Schema::table('owners', function (Blueprint $table) {
            // default 'pro' + pro_berakhir_at null (tanpa batas) supaya owner yang SUDAH ADA
            // sebelum fitur ini tidak kehilangan akses apapun begitu di-deploy.
            $table->string('mode_langganan')->default('pro')->after('kunci_monitor'); // trial | pro
            $table->timestamp('trial_berakhir_at')->nullable()->after('mode_langganan');
            $table->timestamp('pro_berakhir_at')->nullable()->after('trial_berakhir_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('owners', function (Blueprint $table) {
            $table->dropColumn(['mode_langganan', 'trial_berakhir_at', 'pro_berakhir_at']);
        });
    }
};
