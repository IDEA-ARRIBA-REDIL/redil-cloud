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
        // Tabla para Tareas Prerrequisito en Niveles (Grados)
        Schema::create('nivel_tarea_requisito', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nivel_agrupacion_id')->constrained('niveles_agrupacion')->onDelete('cascade');
            $table->foreignId('tarea_consolidacion_id')->constrained('tareas_consolidacion')->onDelete('cascade');
            $table->foreignId('estado_tarea_consolidacion_id')->constrained('estados_tarea_consolidacion')->onDelete('cascade');
            $table->integer('indice')->default(0);
            $table->timestamps();
        });

        // Tabla para Tareas a Culminar en Niveles (Grados)
        Schema::create('nivel_tarea_culminada', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nivel_agrupacion_id')->constrained('niveles_agrupacion')->onDelete('cascade');
            $table->foreignId('tarea_consolidacion_id')->constrained('tareas_consolidacion')->onDelete('cascade');
            $table->foreignId('estado_tarea_consolidacion_id')->constrained('estados_tarea_consolidacion')->onDelete('cascade');
            $table->integer('indice')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nivel_tarea_culminada');
        Schema::dropIfExists('nivel_tarea_requisito');
    }
};
