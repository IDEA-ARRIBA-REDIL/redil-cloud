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
        Schema::create('sistema_calificaciones', function (Blueprint $table) {
            $table->id(); // ID único del sistema de calificaciones
            $table->string('nombre', 200); // Nombre del sistema de calificaciones
            $table->boolean('es_numerico')->default(false); // Indica si el sistema es numérico
            $table->timestamps(); // Fechas de creación y actualización
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sistema_calificaciones');
    }
};
