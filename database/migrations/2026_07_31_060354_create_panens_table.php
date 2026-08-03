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
        Schema::create('panens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tanaman_id')->constrained('tanaman')->cascadeOnDelete();
            $table->foreignId('pembeli_id')->constrained('pembeli')->cascadeOnDelete();
            $table->string('pemanen');
            $table->date('tanggal');
            $table->decimal('berat_kg', 8, 2);
            $table->decimal('harga_per_kg', 12, 2)->nullable(); // boleh kosong dulu kalau hutang
            $table->decimal('jumlah_dibayar', 12, 2)->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('panens');
    }
};
