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
        Schema::create('materias', function (Blueprint $table) {
            $table->id(); // ID único de la materia
            $table->string('nombre', 100); // Nombre de la materia
            $table->integer('nivel_id')->nullable(); // Relación con niveles
            $table->integer('escuela_id'); // Relación con escuelas
            $table->boolean('habilitar_calificaciones')->default(false); // Habilitar calificaciones
            $table->boolean('habilitar_asistencias')->default(false); // Habilitar asistencias
            $table->integer('asistencias_minimas')->nullable(); // Asistencias mínimas requeridas
            $table->integer('asistencias_minima_alerta')->nullable();
            $table->text('descripcion')->nullable(); // Descripción de la materia
            $table->boolean('habilitar_inasistencias')->default(false);
            $table->boolean('habilitar_alerta_inasistencias')->default(false); // Habilitar alerta de inasistencias
            $table->boolean('habilitar_traslado')->default(false); // Habilitar traslados
            $table->boolean('caracter_obligatorio')->default(false); // Carácter obligatorio de la materia
            $table->string('portada', 500)->default("default.png")->nullable();
            $table->integer('limite_reporte_asistencias')->nullable();
            $table->integer('dia_limite_reporte')->nullable();
            $table->integer('dias_plazo_reporte')->nullable();
            $table->boolean('tiene_dia_limite')->default(false);
            $table->smallinteger('cantidad_limite_reportes_semana')->default(1);
            $table->integer('tipo_usuario_objetivo_id')->nullable();
            $table->integer('orden')->default(1)->comment('Define el orden secuencial de la materia');
            $table->softDeletes(); // Esto añade la columna 'deleted_at'
            $table->timestamps(); // Fechas de creación y actualización

            // Índices
            $table->index('nivel_id');
            $table->index('escuela_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materias');
    }
};
