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
        Schema::create('periodos', function (Blueprint $table) {
            $table->id(); // ID único del periodo
            $table->date('fecha_inicio'); // Fecha de inicio del periodo
            $table->date('fecha_fin'); // Fecha de fin del periodo
            $table->foreignId('escuela_id')->constrained('escuelas')->onDelete('cascade'); // Relación con escuelas
            $table->string('nombre', 200); // Nombre del periodo
            $table->date('fecha_inicio_matricula')->nullable(); // Fecha de inicio de matrícula
            $table->date('fecha_fin_matricula')->nullable(); // Fecha de fin de matrícula
            $table->boolean('estado')->default(true); // Estado del periodo (activo/inactivo)
            $table->integer('sistema_calificaciones_id'); // Relación con sistemas de calificación
            $table->boolean('porcentaje_general_corte')->default(true); // Porcentaje general del corte
            $table->integer('tipo_corte_id')->default(1); // Relación con tipos de corte
            $table->date('fecha_maxima_entrega_notas')->nullable(); // Fecha máxima para entregar notas
            $table->timestamps(); // Fechas de creación y actualización

            // Índices
            $table->index('escuela_id');
            $table->index('sistema_calificaciones_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periodos');
    }
};
