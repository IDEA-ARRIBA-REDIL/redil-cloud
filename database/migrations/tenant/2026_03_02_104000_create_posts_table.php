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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('image_path')->nullable();
            $table->text('descripcion')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->boolean('visualizar_siempre')->default(false);
            $table->boolean('visible_todos')->default(true);
            $table->integer('genero')->default(3); // 1: Masculino, 2: Femenino, 3: Ambos
            $table->timestamps();
        });

        // Tablas de restricciones para posts
        Schema::create('post_sedes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->foreignId('sede_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('post_estados_civiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->foreignId('estado_civil_id')->constrained('estados_civiles')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('post_rangos_edad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->foreignId('rango_edad_id')->constrained('rangos_edad')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('post_tipos_usuarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->foreignId('tipo_usuario_id')->constrained('tipo_usuarios')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('post_procesos_requisito', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->foreignId('paso_crecimiento_id')->constrained('pasos_crecimiento')->onDelete('cascade');
            $table->foreignId('estado_paso_crecimiento_usuario_id')->constrained('estados_pasos_crecimiento_usuario')->onDelete('cascade');
            $table->integer('indice')->default(0);
            $table->timestamps();
        });

        Schema::create('post_tareas_requisito', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
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
        Schema::dropIfExists('post_tareas_requisito');
        Schema::dropIfExists('post_procesos_requisito');
        Schema::dropIfExists('post_tipos_usuarios');
        Schema::dropIfExists('post_rangos_edad');
        Schema::dropIfExists('post_estados_civiles');
        Schema::dropIfExists('post_sedes');
        Schema::dropIfExists('posts');
    }
};
