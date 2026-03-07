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
        Schema::create('horarios_adicionales_consejero', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consejero_id')->constrained('consejeros')->onDelete('cascade');

            /** * Rango de fecha y hora EXACTO de la disponibilidad adicional. */
            $table->dateTime('fecha_inicio'); // Ejemplo: '2025-11-29 09:00:00' (Un sábado)
            $table->dateTime('fecha_fin');   // Ejemplo: '2025-11-29 13:00:00'

            $table->string('motivo')->nullable(); // Opcional: "Jornada especial fin de semana"

            $table->timestamps();

            // Índice para buscar rápidamente adiciones en un rango de fechas
            $table->index(['consejero_id', 'fecha_inicio', 'fecha_fin']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horarios_adicionales_consejero');
    }
};
