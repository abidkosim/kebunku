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
        Schema::create('tandons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_kebun')->constrained('kebun')->cascadeOnDelete();
            $table->string('nama');
            $table->unsignedInteger('target_ppm')->default(750);
            $table->decimal('target_ph', 3, 1)->default(6.0);
            $table->unsignedInteger('ppm_terkini')->nullable();
            $table->decimal('ph_terkini', 3, 1)->nullable();
            $table->decimal('suhu_terkini', 4, 1)->nullable();
            $table->string('status_simulasi')->default('berhenti'); // aktif | berhenti
            $table->string('status_pompa')->nullable(); // null | nutrisi | ph_up | ph_down
            $table->timestamp('terakhir_baca_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tandons');
    }
};
