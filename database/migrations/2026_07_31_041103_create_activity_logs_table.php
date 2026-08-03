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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('actor_type'); // superadmin | owner | user
            $table->unsignedBigInteger('actor_id');
            $table->string('actor_nama');
            $table->string('aksi'); // tambah | update | hapus
            $table->string('modul'); // Superadmin | Owner | User | Tanaman | dst
            $table->text('keterangan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
