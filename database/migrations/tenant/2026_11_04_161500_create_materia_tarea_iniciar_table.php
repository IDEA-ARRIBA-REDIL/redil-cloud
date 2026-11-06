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
        Schema::create('materia_tarea_iniciar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('materia_id')->constrained('materias')->onDelete('cascade');
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
        Schema::dropIfExists('materia_tarea_iniciar');
    }
};
