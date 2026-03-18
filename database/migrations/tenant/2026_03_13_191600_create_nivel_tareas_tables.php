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
        // Tabla para Tareas Requeridas en Niveles
        Schema::create('nivel_tarea_requisito', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nivel_id');
            $table->unsignedBigInteger('tarea_consolidacion_id');
            $table->unsignedBigInteger('estado_tarea_consolidacion_id');
            $table->integer('indice')->default(0);
            $table->timestamps();

            $table->index('nivel_id');
            $table->index('tarea_consolidacion_id');
        });

        // Tabla para Tareas al Culminar en Niveles
        Schema::create('nivel_tarea_culminada', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nivel_id');
            $table->unsignedBigInteger('tarea_consolidacion_id');
            $table->unsignedBigInteger('estado_tarea_consolidacion_id');
            $table->integer('indice')->default(0);
            $table->timestamps();

            $table->index('nivel_id');
            $table->index('tarea_consolidacion_id');
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
