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
        // 1. Tabla Principal: cursos
        Schema::create('cursos', function (Blueprint $table) {
            $table->id();

            // Básicos
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->text('descripcion_corta')->nullable();
            $table->longText('descripcion_larga')->nullable();
            $table->string('imagen_portada')->nullable();
            $table->string('video_preview_url')->nullable();

            // Relaciones (Categoría - por ahora nullable o entero si no existe la tabla aun)
            $table->unsignedBigInteger('categoria_id')->nullable();

            // Configuración
            $table->enum('nivel_dificultad', ['Principiante', 'Intermedio', 'Avanzado', 'Todas'])->default('Todas');
            $table->boolean('es_obligatorio')->default(false);
            $table->enum('estado', ['Borrador', 'Publicado', 'Inactivo'])->default('Borrador');
            $table->integer('orden_destacado')->default(0);

            // Restricciones de Acceso / Tiempo
            $table->integer('cupos_totales')->nullable()->comment('Null = Ilimitado');
            $table->integer('dias_acceso_limitado')->nullable()->comment('Null = Acceso de por vida');
            $table->integer('duracion_estimada_dias')->default(0)->comment('En días');
            $table->dateTime('fecha_inicio')->nullable()->comment('Para cursos sincrónicos o cohortes');

            // Relaciones
            $table->foreignId('carrera_id')->nullable()->comment('Relación con Carreras (si existe tabla en futuro)');

            // Precios y Pagos
            $table->boolean('es_gratuito')->default(false);
            $table->decimal('precio', 10, 2)->default(0);
            $table->decimal('precio_comparacion', 10, 2)->nullable();

            // Relación con Moneda (Asumimos que existe la tabla 'monedas')
            $table->foreignId('moneda_id')->nullable()->constrained('monedas')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Tablas Pivote / Relaciones

        // pivote: curso_roles_restriccion
        Schema::create('curso_roles_restriccion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained('cursos')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->timestamps();
        });

        // pivote: curso_tipos_pago
        Schema::create('curso_tipos_pago', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained('cursos')->onDelete('cascade');
            $table->foreignId('tipo_pago_id')->constrained('tipos_pago')->onDelete('cascade');
            $table->timestamps();
        });

        // pivote: curso_paso_requisito (Matches actividad_procesos_requisito)
        Schema::create('curso_paso_requisito', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained('cursos')->onDelete('cascade');
            $table->foreignId('paso_crecimiento_id')->constrained('pasos_crecimiento')->onDelete('cascade');
            // Campos adicionales para lógica de negocio
            $table->integer('estado')->comment('El estado requerido del paso');
            $table->integer('estado_paso_crecimiento_usuario_id')->comment('ID de estado especifico en tabla pivote usuario');
            $table->integer('indice')->default(0);
            $table->timestamps();
        });

        // pivote: curso_paso_iniciar (Al iniciar el curso)
        Schema::create('curso_paso_iniciar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained('cursos')->onDelete('cascade');
            $table->foreignId('paso_crecimiento_id')->constrained('pasos_crecimiento')->onDelete('cascade');
            $table->integer('estado')->nullable();
            $table->integer('estado_paso_crecimiento_usuario_id')->nullable();
            $table->integer('indice')->default(0);
            $table->timestamps();
        });

        // pivote: curso_paso_culminar (Al terminar el curso)
        // Matches actividad_procesos_culminados
        Schema::create('curso_paso_culminar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained('cursos')->onDelete('cascade');
            $table->foreignId('paso_crecimiento_id')->constrained('pasos_crecimiento')->onDelete('cascade');
            $table->integer('estado')->nullable();
            $table->integer('estado_paso_crecimiento_usuario_id')->nullable();
            $table->integer('indice')->default(0);
            $table->timestamps();
        });

        // pivote: curso_tarea_requisito (Matches actividad_tareas_requisito)
        Schema::create('curso_tarea_requisito', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained('cursos')->onDelete('cascade');
            $table->foreignId('tarea_consolidacion_id')->constrained('tareas_consolidacion')->onDelete('cascade');
            // Relacion con estado de tarea (si existe tabla estados_tarea_consolidacion)
            // En el ejemplo usaban foreignId pero aqui usare un entero o foreignId si estoy seguro.
            // El usuario mostro: $table->foreignId('estado_tarea_consolidacion_id')->constrained('estados_tarea_consolidacion')...
            $table->foreignId('estado_tarea_consolidacion_id')->constrained('estados_tarea_consolidacion')->onDelete('cascade');
            $table->integer('indice')->default(0);
            $table->timestamps();
        });

        // pivote: curso_tarea_culminar (Matches actividad_tareas_culminadas)
        Schema::create('curso_tarea_culminar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained('cursos')->onDelete('cascade');
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
        Schema::dropIfExists('curso_tarea_culminar');
        Schema::dropIfExists('curso_tarea_requisito');
        Schema::dropIfExists('curso_paso_culminar');
        Schema::dropIfExists('curso_paso_iniciar');
        Schema::dropIfExists('curso_paso_requisito');
        Schema::dropIfExists('curso_tipos_pago');
        Schema::dropIfExists('curso_roles_restriccion');
        Schema::dropIfExists('cursos');
    }
};
