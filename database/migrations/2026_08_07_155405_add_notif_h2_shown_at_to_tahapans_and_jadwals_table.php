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
            $table->date('notif_h2_shown_at')->nullable()->after('catatan');
        });

        Schema::table('jadwals', function (Blueprint $table) {
            $table->date('notif_h2_shown_at')->nullable()->after('catatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tahapans', function (Blueprint $table) {
            $table->dropColumn('notif_h2_shown_at');
        });

        Schema::table('jadwals', function (Blueprint $table) {
            $table->dropColumn('notif_h2_shown_at');
        });
    }
};
