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
        Schema::create('tahapans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tanaman_id')->constrained('tanaman')->cascadeOnDelete();
            $table->enum('jenis', ['semai', 'peremajaan', 'pendewasaan', 'panen']);
            $table->unsignedInteger('durasi_rencana'); // dalam hari
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai_rencana');
            $table->date('tanggal_selesai_aktual')->nullable();
            $table->enum('status', ['berjalan', 'selesai'])->default('berjalan');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tahapans');
    }
};
