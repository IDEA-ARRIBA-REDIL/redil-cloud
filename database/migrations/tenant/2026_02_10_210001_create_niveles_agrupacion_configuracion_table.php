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
        Schema::create('niveles_agrupacion_configuracion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nivel_agrupacion_id')->constrained('niveles_agrupacion')->onDelete('cascade');

            // Restricciones Académicas Globales del Nivel
            $table->integer('asistencias_minimas')->nullable();
            $table->integer('max_reportes_permitidos')->nullable();
            $table->integer('dias_alerta_inasistencia')->nullable();

            // Reglas de Aprobación
            $table->boolean('requiere_aprobacion_total')->default(true)->comment('Si true, debe aprobar todas las materias para aprobar el nivel');
            $table->integer('minimo_materias_aprobadas')->nullable()->comment('Si false anterior, cuantas materias minimo debe aprobar');

            // Configuraciones de Comportamiento
            $table->boolean('habilitar_clases_espejo')->default(false);
            $table->boolean('bloquear_matricula_extemporanea')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('niveles_agrupacion_configuracion');
    }
};
