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
        Schema::create('recurso_alumno_horario', function (Blueprint $table) {
            $table->id();
            $table->integer('horario_materia_periodo_id');
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('tipo', 50); // Video, Clase, Predica, Libro, Documento, Enlace
            $table->string('link_externo')->nullable();
            $table->string('link_youtube')->nullable();
            $table->string('nombre_archivo')->nullable(); // Guardamos solo el nombre del archivo
            $table->string('ruta_archivo')->nullable(); // Y su ruta en el storage
            $table->boolean('visible')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurso_alumno_horario');
    }
};
