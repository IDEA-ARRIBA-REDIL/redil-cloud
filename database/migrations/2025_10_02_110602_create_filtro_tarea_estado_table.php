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
        Schema::create('filtro_tarea_estado', function (Blueprint $table) {
            // La conexión al filtro
            $table->foreignId('filtro_consolidacion_id')->constrained('filtros_consolidacion')->onDelete('cascade');

            // La conexión a la tarea
            $table->foreignId('tarea_consolidacion_id')->constrained('tareas_consolidacion')->onDelete('cascade');

            // La condición del estado para esa tarea
            $table->foreignId('estado_tarea_consolidacion_id')->constrained('estados_tarea_consolidacion')->onDelete('cascade');

            $table->boolean('incluir')->default(true)->after('estado_tarea_consolidacion_id');


            // ----> ESTA ES LA LÍNEA CORREGIDA <----
            // La clave primaria ahora es la combinación de los tres campos.
            // Esto permite (Filtro 1, Tarea 1, Estado 3) y (Filtro 1, Tarea 1, Estado 4) como filas válidas.
            $table->primary([
                'filtro_consolidacion_id',
                'tarea_consolidacion_id',
                'estado_tarea_consolidacion_id'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('filtro_tarea_estado');
    }
};
