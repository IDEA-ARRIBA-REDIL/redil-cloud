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
        Schema::create('horarios_materia_periodo', function (Blueprint $table) {
            $table->id(); // ID único del horario habilitado
            $table->integer('materia_periodo_id'); // Relación con materia-periodo
            $table->integer('horario_base_id'); // Relación con horarios base
            $table->boolean('habilitado')->default(true); // Indica si el horario está habilitado
            $table->date('fecha_inicio_habilitado')->nullable(); // Fecha de inicio de habilitación
            $table->date('fecha_fin_habilitado')->nullable(); // Fecha de fin de habilitación
            $table->integer('capacidad')->default(0); // Capacidad del aula
            $table->integer('capacidad_limite')->default(50); // Capacidad del aula aumentada
            $table->boolean('ampliar_cupos_limite')->default(false); // aqui esto se pone para aumentar la capacidad de cupos de la cantidad normal a la cantidad limite 
            $table->integer('cupos_disponibles')->nullable();
            $table->timestamps(); // Fechas de creación y actualización
            $table->index(['materia_periodo_id', 'horario_base_id']); // Índice para mejorar consultas
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horarios_materia_periodo');
    }
};
