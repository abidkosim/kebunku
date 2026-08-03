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
        Schema::create('tandon_bacaans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_tandon')->constrained('tandons')->cascadeOnDelete();
            $table->unsignedInteger('ppm');
            $table->decimal('ph', 3, 1);
            $table->decimal('suhu', 4, 1);
            $table->timestamp('created_at')->useCurrent();
            $table->index(['id_tandon', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tandon_bacaans');
    }
};
