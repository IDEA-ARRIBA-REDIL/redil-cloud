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
        Schema::table('planes_lectores', function (Blueprint $table) {
            $table->boolean('visible_todos')->default(true)->after('estado');
            $table->integer('genero')->default(3)->after('visible_todos'); // 1: Masculino, 2: Femenino, 3: Ambos
        });

        Schema::create('plan_lector_sedes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_lector_id')->constrained('planes_lectores')->onDelete('cascade');
            $table->foreignId('sede_id')->constrained('sedes')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('plan_lector_estados_civiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_lector_id')->constrained('planes_lectores')->onDelete('cascade');
            $table->foreignId('estado_civil_id')->constrained('estados_civiles')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('plan_lector_rangos_edad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_lector_id')->constrained('planes_lectores')->onDelete('cascade');
            $table->foreignId('rango_edad_id')->constrained('rangos_edad')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('plan_lector_tipos_usuarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_lector_id')->constrained('planes_lectores')->onDelete('cascade');
            $table->foreignId('tipo_usuario_id')->constrained('tipo_usuarios')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('plan_lector_procesos_requisito', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_lector_id')->constrained('planes_lectores')->onDelete('cascade');
            $table->foreignId('paso_crecimiento_id')->constrained('pasos_crecimiento')->onDelete('cascade');
            $table->foreignId('estado_paso_crecimiento_usuario_id')->constrained('estados_pasos_crecimiento_usuario')->onDelete('cascade');
            $table->integer('indice')->default(0);
            $table->timestamps();
        });

        Schema::create('plan_lector_tareas_requisito', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_lector_id')->constrained('planes_lectores')->onDelete('cascade');
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
        Schema::dropIfExists('plan_lector_tareas_requisito');
        Schema::dropIfExists('plan_lector_procesos_requisito');
        Schema::dropIfExists('plan_lector_tipos_usuarios');
        Schema::dropIfExists('plan_lector_rangos_edad');
        Schema::dropIfExists('plan_lector_estados_civiles');
        Schema::dropIfExists('plan_lector_sedes');

        Schema::table('planes_lectores', function (Blueprint $table) {
            $table->dropColumn(['visible_todos', 'genero']);
        });
    }
};
