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
        // 1. Secciones de Informes Personalizados
        if (! Schema::hasTable('secciones_informes')) {
            Schema::create('secciones_informes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('informe_personalizado_id')
                    ->constrained('informes_personalizados')
                    ->cascadeOnDelete();
                $table->string('nombre');
                $table->integer('orden')->default(0);
                $table->timestamps();

                $table->index(['informe_personalizado_id', 'orden']);
            });
        }

        // 2. Subsecciones de Informes Personalizados
        if (! Schema::hasTable('subsecciones_informes')) {
            Schema::create('subsecciones_informes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('seccion_informe_id')
                    ->constrained('secciones_informes')
                    ->cascadeOnDelete();
                $table->string('nombre');
                $table->integer('orden')->default(0);
                $table->timestamps();

                $table->index(['seccion_informe_id', 'orden']);
            });
        }

        // 3. Items de Subsecciones (Métricas y Filtros)
        if (! Schema::hasTable('items_subsecciones_informes')) {
            Schema::create('items_subsecciones_informes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('subseccion_informe_id')
                    ->constrained('subsecciones_informes')
                    ->cascadeOnDelete();
                $table->string('nombre');
                $table->integer('orden')->default(0);

                // Configuración de temporalidad
                $table->boolean('visualizar_por_mes')->default(false);
                $table->boolean('visualizar_por_semanas')->default(false);
                $table->boolean('fecha_creacion')->default(false);

                // Paso de crecimiento 1
                $table->unsignedBigInteger('paso_crecimiento_id')->nullable();
                $table->unsignedBigInteger('estado_paso_crecimiento')->nullable();
                $table->boolean('filtrar_fecha_paso_crecimiento')->default(false);

                // Paso de crecimiento 2 y comparación
                $table->string('parametro_de_comparacion')->nullable(); // 'paso-crecimiento' | 'fecha-creacion'
                $table->unsignedBigInteger('paso_crecimiento_id_2')->nullable();
                $table->boolean('no_existe_paso_crecimiento_id_2')->default(false);
                $table->unsignedBigInteger('estado_paso_crecimiento_2')->nullable();
                $table->boolean('filtrar_fecha_paso_crecimiento_2')->default(false);
                $table->integer('cantidad_dias_dilacion')->nullable();

                // Filtros de personas y datos congregacionales
                $table->smallInteger('grupo_de_personas')->default(1); // 1: Todos, 2: Alta/Activos, 3: Baja/Eliminados
                $table->string('filtrar_tipo_vinculacion')->nullable(); // CSV de IDs
                $table->string('filtrar_estado_civil')->nullable(); // CSV de IDs

                // Bajas y altas
                $table->unsignedBigInteger('tipo_baja_alta_id')->nullable();
                $table->boolean('estado_reporte_dado_baja')->nullable();
                $table->boolean('filtrar_fecha_reporte_baja_alta')->default(false);

                // Matrículas
                $table->string('estado_matricula')->nullable();
                $table->boolean('filtro_fecha_matricula')->default(false);

                // Operaciones y totalizadores
                $table->boolean('con_operacion')->default(false);
                $table->smallInteger('operacion')->nullable(); // 1: Suma, 2: Resta, 3: Multiplicación, 4: División, 5: Promedio
                $table->unsignedBigInteger('item_a')->nullable();
                $table->unsignedBigInteger('item_b')->nullable();
                $table->string('totalizar_items')->nullable(); // CSV de IDs

                $table->timestamps();

                $table->index(['subseccion_informe_id', 'orden']);
            });
        }

        // 4. Bloques de Informes (Agrupación por sedes)
        if (! Schema::hasTable('bloques_informes')) {
            Schema::create('bloques_informes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('informe_personalizado_id')
                    ->constrained('informes_personalizados')
                    ->cascadeOnDelete();
                $table->string('nombre');
                $table->text('ids_sedes')->nullable(); // CSV de IDs de sedes
                $table->timestamps();

                $table->index('informe_personalizado_id');
            });
        }

        // 5. Tabla de Jerarquía Aplanada de Grupos
        if (! Schema::hasTable('grupos_de_grupos')) {
            Schema::create('grupos_de_grupos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('grupo_padre');
                $table->unsignedBigInteger('tipo_grupo_id_padre')->nullable();
                $table->unsignedBigInteger('grupo_hijo');
                $table->unsignedBigInteger('tipo_grupo_id_hijo')->nullable();
                $table->timestamps();

                $table->index(['grupo_padre', 'tipo_grupo_id_hijo']);
                $table->index(['grupo_padre', 'grupo_hijo']);
                $table->index('grupo_hijo');
            });
        }

        // 6. Registro de Trazabilidad y Descargas en Cola
        if (! Schema::hasTable('informes_en_cola')) {
            Schema::create('informes_en_cola', function (Blueprint $table) {
                $table->id();
                $table->foreignId('informe_personalizado_id')
                    ->constrained('informes_personalizados')
                    ->cascadeOnDelete();
                $table->unsignedBigInteger('grupo_id');
                $table->unsignedBigInteger('agrupar_por_tipo_grupo_id');
                $table->integer('year');
                $table->string('periodo'); // 'semana', '1m'..'12m', '1t'..'4t', '1s'..'2s', 'anio'
                $table->string('semana')->nullable();
                $table->string('email');
                $table->unsignedBigInteger('usuario_creacion_id')->nullable();
                $table->string('nombre_archivo')->nullable();
                $table->string('estado')->default('pending'); // 'pending', 'processing', 'completed', 'failed'
                $table->text('error_message')->nullable();
                $table->integer('tiempo_ejecucion_segundos')->nullable();
                $table->timestamps();

                $table->index(['informe_personalizado_id', 'estado']);
                $table->index(['usuario_creacion_id', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('informes_en_cola');
        Schema::dropIfExists('grupos_de_grupos');
        Schema::dropIfExists('bloques_informes');
        Schema::dropIfExists('items_subsecciones_informes');
        Schema::dropIfExists('subsecciones_informes');
        Schema::dropIfExists('secciones_informes');
    }
};
