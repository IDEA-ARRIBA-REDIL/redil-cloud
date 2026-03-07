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
        Schema::table('cursos', function (Blueprint $table) {
            $table->integer('genero')->default(3)->comment('1: Masculino, 2: Femenino, 3: Ambos');
            $table->integer('vinculacion_grupo')->default(3)->comment('1: Pertenece, 2: No pertenece, 3: Ambos');
            $table->integer('actividad_grupo')->default(3)->comment('1: Activos, 2: Inactivos, 3: Ambos');
            $table->boolean('excluyente')->default(false);
        });

        // Pivot: Curso - Sede
        Schema::create('curso_sede', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained('cursos')->onDelete('cascade');
            $table->foreignId('sede_id')->constrained('sedes')->onDelete('cascade');
            $table->timestamps();
        });

        // Pivot: Curso - RangoEdad
        Schema::create('curso_rango_edad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained('cursos')->onDelete('cascade');
            $table->foreignId('rango_edad_id')->constrained('rangos_edad')->onDelete('cascade');
            $table->timestamps();
        });

        // Pivot: Curso - EstadoCivil
        Schema::create('curso_estado_civil', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained('cursos')->onDelete('cascade');
            $table->foreignId('estado_civil_id')->constrained('estados_civiles')->onDelete('cascade');
            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curso_tipo_servicio');
        Schema::dropIfExists('curso_estado_civil');
        Schema::dropIfExists('curso_rango_edad');
        Schema::dropIfExists('curso_sede');

        Schema::table('cursos', function (Blueprint $table) {
            $table->dropColumn(['genero', 'vinculacion_grupo', 'actividad_grupo', 'excluyente']);
        });
    }
};
