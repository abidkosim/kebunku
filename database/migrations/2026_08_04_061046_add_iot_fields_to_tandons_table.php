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
            $table->string('sumber_data')->default('simulasi')->after('status_simulasi'); // simulasi | iot
            $table->string('device_token', 40)->nullable()->unique()->after('sumber_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tandons', function (Blueprint $table) {
            $table->dropColumn(['sumber_data', 'device_token']);
        });
    }
};
