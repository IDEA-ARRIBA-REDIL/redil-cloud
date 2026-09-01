<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabla principal de hitos con relación a tipo_hitos y parámetros de triggers.
     */
    public function up(): void
    {
        Schema::create('hitos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_hito_id')->constrained('tipo_hitos')->onDelete('restrict');
            $table->foreignId('user_id')->constrained()->comment('Usuario administrativo que creó el hito');

            // Contenido informativo y visual
            $table->string('titulo', 150);
            $table->text('descripcion')->nullable();
            $table->text('mensaje_usuario')->nullable()
                ->comment('Mensaje personalizado que se muestra al usuario en su línea de vida');
            $table->string('portada_path', 255)->nullable();
            $table->string('video_url', 500)->nullable();
            $table->date('fecha_evento')->nullable()
                ->comment('Fecha representativa para eventos generales o fechas fijas');

            // Configuración para hitos vinculados a Actividades
            $table->foreignId('actividad_id')->nullable()->constrained('actividades')->onDelete('set null');
            $table->boolean('requiere_asistencia')->default(false)
                ->comment('Si true, solo aparece a usuarios con registro de asistencia en la actividad');

            // Configuración para hitos Automáticos (Triggers)
            $table->string('trigger_modulo', 50)->nullable()
                ->comment('pasos_crecimiento | tareas_consolidacion | escuelas | grupos');
            $table->string('trigger_tipo', 50)->nullable()
                ->comment('cambio_estado | aprobacion_materia | aprobacion_nivel | asignacion_integrante | designacion_lider');
            $table->json('trigger_config')->nullable()
                ->comment('Condiciones específicas: {paso_id, estado_id, materia_id, nivel_id, tipo_grupo_id}');

            // Parámetros de interacción
            $table->boolean('permite_fotos_usuario')->default(false);
            $table->unsignedSmallInteger('max_fotos_usuario')->default(3);
            $table->unsignedSmallInteger('max_peso_kb')->default(2048);
            $table->boolean('requiere_sesion')->default(true);
            $table->boolean('activo')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // Índices de optimización
            $table->index(['tipo_hito_id', 'activo']);
            $table->index('trigger_modulo');
            $table->index('actividad_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hitos');
    }
};
