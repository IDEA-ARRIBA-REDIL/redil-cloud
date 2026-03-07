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
       // antes clasificacion_asistente_tipo_asistente
        Schema::create('clasificacion_asistente_tipo_usuario', function (Blueprint $table) {
            $table->id();
            $table->integer('tipo_usuario_id'); // antes tipo_asistente_id
            $table->integer('clasificacion_asistente_id'); // antes clasificacion_asistente_reporte_grupo_id
            $table->smallInteger('edad_minima')->nullable();
            $table->smallInteger('edad_maxima')->nullable();
            $table->smallInteger('paso_id')->nullable();
            $table->smallInteger('estado_paso')->nullable();
            $table->boolean('fecha_ingreso_igual_fecha_reporte')->default(0);
            $table->boolean('fecha_dado_alta_igual_fecha_reporte')->default(0); // Mira que no hay codigo de esto, preguntar a felipe
            $table->boolean('fecha_paso_igual_fecha_reporte')->default(0);
            $table->timestamps();

            $table->comment('Esta tabla surge de la relación de muchos a muchos entre la tabla clasificaciones_asistentes y la tabla tipo_usuario');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clasificacion_asistente_tipo_usuario');
    }
};
