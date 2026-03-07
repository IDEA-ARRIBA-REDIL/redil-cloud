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
        Schema::create('materia_periodo', function (Blueprint $table) {
            $table->id(); // ID único de la materia-periodo
            $table->foreignId('materia_id')->constrained('materias')->onDelete('cascade'); // Relación con materias
            $table->foreignId('periodo_id')->constrained('periodos')->onDelete('cascade'); // Relación con periodos
            $table->foreignId('maestro_id')->nullable(); // Relación con maestros
            $table->boolean('habilitar_calificaciones')->default(true); // Habilitar gestión de calificaciones
            $table->boolean('habilitar_asistencias')->default(false); // Habilitar gestión de asistencias
            $table->integer('asistencias_minimas')->nullable(); // Asistencias mínimas requeridas
            $table->boolean('auto_matricula')->default(false); // Habilitar auto-matrícula
            $table->smallInteger('estado_auto_matricula')->default(2); // Estado de la auto-matrícula
            $table->boolean('finalizado')->default(false); // Indica si la materia-periodo está finalizada
            $table->text('descripcion')->nullable(); // Descripción de la materia-periodo
            $table->smallInteger('cantidad_inasistencias_alerta')->nullable(); // Cantidad de inasistencias para alerta
            $table->boolean('habilitar_alerta_inasistencias')->default(false); // Habilitar alerta de inasistencias
            $table->boolean('habilitar_traslado')->default(false); // Habilitar traslados
            $table->timestamps(); // Fechas de creación y actualización

            // Índices
            $table->index(['materia_id', 'periodo_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materia_periodo');
    }
};
