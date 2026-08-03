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
        Schema::table('superadmins', function (Blueprint $table) {
            $table->enum('akses', ['full', 'read_only'])->default('full')->after('password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('superadmins', function (Blueprint $table) {
            $table->dropColumn('akses');
        });
    }
};
