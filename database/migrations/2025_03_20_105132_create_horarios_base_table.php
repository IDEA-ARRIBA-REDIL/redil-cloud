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
        Schema::create('horarios_base', function (Blueprint $table) {
            $table->id(); // ID único del horario base
            $table->integer('materia_id'); // Relación con materias
            $table->integer('aula_id'); // Relación con aulas
            $table->integer('capacidad')->default(0); // Capacidad del aula
            $table->integer('capacidad_limite')->default(50); // Capacidad del aula
            $table->integer('dia'); // Día de la semana
            $table->time('hora_inicio'); // Hora de inicio (ej: "07:00")
            $table->time('hora_fin'); // Hora de fin (ej: "09:00")
            $table->boolean('activo')->default(TRUE);
            $table->timestamps(); // Fechas de creación y actualización
            $table->softDeletes(); // Soft deletes para mantener historial
            $table->index(['materia_id', 'aula_id']); // Índice para mejorar consultas
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horarios_base');
    }
};
