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
        Schema::create('tipos_cargo_cursos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->boolean('puede_responder_preguntas')->default(false);
            $table->boolean('puede_editar_curso')->default(false);
            $table->boolean('puede_editar_restricciones')->default(false);
            $table->boolean('puede_editar_contenido')->default(false);
            $table->boolean('puede_gestionar_equipo')->default(false);
            $table->boolean('puede_gestionar_estudiantes')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipos_cargo_cursos');
    }
};
