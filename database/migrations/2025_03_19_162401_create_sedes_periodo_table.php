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
         Schema::create('sedes_periodo', function (Blueprint $table) {
            $table->id(); // ID único de la relación
            $table->foreignId('sede_id')->constrained('sedes')->onDelete('cascade'); // Relación con sedes
            $table->foreignId('periodo_id')->constrained('periodos')->onDelete('cascade'); // Relación con periodos
            $table->timestamps(); // Fechas de creación y actualización

            // Índices
            $table->index(['sede_id', 'periodo_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sedes_periodo');
    }
};
