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
        // Tablas de restricciones / segmentación para productos de la tienda
        Schema::create('producto_tienda_sedes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos_tienda')->onDelete('cascade');
            $table->foreignId('sede_id')->constrained('sedes')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('producto_tienda_estados_civiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos_tienda')->onDelete('cascade');
            $table->foreignId('estado_civil_id')->constrained('estados_civiles')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('producto_tienda_rangos_edad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos_tienda')->onDelete('cascade');
            $table->foreignId('rango_edad_id')->constrained('rangos_edad')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('producto_tienda_tipos_usuarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos_tienda')->onDelete('cascade');
            $table->foreignId('tipo_usuario_id')->constrained('tipo_usuarios')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('producto_tienda_procesos_requisito', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos_tienda')->onDelete('cascade');
            $table->foreignId('paso_crecimiento_id')->constrained('pasos_crecimiento')->onDelete('cascade');
            $table->foreignId('estado_paso_crecimiento_usuario_id')->constrained('estados_pasos_crecimiento_usuario')->onDelete('cascade');
            $table->integer('indice')->default(0);
            $table->timestamps();
        });

        Schema::create('producto_tienda_tareas_requisito', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos_tienda')->onDelete('cascade');
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
        Schema::dropIfExists('producto_tienda_tareas_requisito');
        Schema::dropIfExists('producto_tienda_procesos_requisito');
        Schema::dropIfExists('producto_tienda_tipos_usuarios');
        Schema::dropIfExists('producto_tienda_rangos_edad');
        Schema::dropIfExists('producto_tienda_estados_civiles');
        Schema::dropIfExists('producto_tienda_sedes');

        Schema::table('productos_tienda', function (Blueprint $table) {
            if (Schema::hasColumn('productos_tienda', 'genero')) {
                $table->dropColumn('genero');
            }
            if (Schema::hasColumn('productos_tienda', 'visible_todos')) {
                $table->dropColumn('visible_todos');
            }
        });
    }
};
