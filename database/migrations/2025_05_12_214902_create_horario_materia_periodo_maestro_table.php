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
        Schema::create('horario_materia_periodo_maestro', function (Blueprint $table) {
            $table->id();
            $table->foreignId('horario_materia_periodo_id')
                  ->constrained('horarios_materia_periodo') // Nombre de tu tabla de horarios de materia-periodo
                  ->onDelete('cascade');
            $table->foreignId('maestro_id')
                  ->constrained('maestros') // Nombre de tu tabla de maestros
                  ->onDelete('cascade');
            // $table->string('rol_maestro')->nullable(); // Ejemplo: 'Principal', 'Asistente' si quieres añadir un rol.
            $table->timestamps();

            // Clave única para evitar duplicados de asignación
            $table->unique(['horario_materia_periodo_id', 'maestro_id'], 'horario_maestro_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horario_materia_periodo_maestro');
    }
};
