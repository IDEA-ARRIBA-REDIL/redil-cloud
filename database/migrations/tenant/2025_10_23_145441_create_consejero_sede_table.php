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
        Schema::create('consejero_sede', function (Blueprint $table) {
            $table->id();

            $table->foreignId('consejero_id')->constrained('consejeros')->onDelete('cascade');
            $table->foreignId('sede_id')->constrained('sedes')->onDelete('cascade');

            // Opcional: Evita duplicados (que un consejero esté 2 veces en la misma sede)
            $table->unique(['consejero_id', 'sede_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consejero_sede');
    }
};
