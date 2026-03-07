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
        Schema::table('niveles_agrupacion_configuracion', function (Blueprint $table) {
            // Campos de control de asistencia y calificaciones
            $table->boolean('habilitar_calificaciones')->default(true);
            $table->boolean('habilitar_asistencias')->default(true);
            $table->boolean('habilitar_inasistencias')->default(true);
            $table->boolean('habilitar_alerta_inasistencias')->default(false);

            // Límites de reportes y alertas
            $table->integer('limite_reportes')->nullable();
            $table->integer('asistencias_minima_alerta')->nullable();
            $table->integer('cantidad_inasistencias_alerta')->nullable();

            // Configuración de día límite de reporte
            $table->boolean('dia_limite_habilitado')->default(false);
            $table->integer('dia_limite_reporte')->nullable()->comment('0=Domingo, 1=Lunes, etc.');

            // Plazos y cantidades de reporte
            $table->integer('cantidad_reportes_semana')->nullable();
            $table->integer('dias_plazo_reporte')->nullable();

            // Otros controles
            $table->boolean('habilitar_traslado')->default(false);
            $table->boolean('caracter_obligatorio')->default(true);

            // Relación con tipo de usuario objetivo (si aplica cambio por asistencia)
            $table->foreignId('tipo_usuario_objetivo_id')->nullable()->constrained('tipo_usuarios')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('niveles_agrupacion_configuracion', function (Blueprint $table) {
            $table->dropForeign(['tipo_usuario_objetivo_id']);
            $table->dropColumn([
                'habilitar_calificaciones',
                'habilitar_asistencias',
                'habilitar_inasistencias',
                'habilitar_alerta_inasistencias',
                'limite_reportes',
                'asistencias_minima_alerta',
                'cantidad_inasistencias_alerta',
                'dia_limite_habilitado',
                'dia_limite_reporte',
                'cantidad_reportes_semana',
                'dias_plazo_reporte',
                'habilitar_traslado',
                'caracter_obligatorio',
                'tipo_usuario_objetivo_id'
            ]);
        });
    }
};
