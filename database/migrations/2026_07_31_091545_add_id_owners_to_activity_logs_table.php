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
        Schema::table('activity_logs', function (Blueprint $table) {
            // nullable: null untuk log superadmin (tidak terikat satu owner manapun)
            $table->unsignedBigInteger('id_owners')->nullable()->after('actor_id');
        });

        // Backfill: log lama actor_type=owner, actor_id-nya memang sudah id owner itu sendiri.
        DB::table('activity_logs')->where('actor_type', 'owner')->update([
            'id_owners' => DB::raw('actor_id'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropColumn('id_owners');
        });
    }
};
