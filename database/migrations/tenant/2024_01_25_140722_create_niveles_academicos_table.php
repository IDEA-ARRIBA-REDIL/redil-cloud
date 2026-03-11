<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('niveles_academicos', function (Blueprint $table) {
        $table->id();
            $table->string('nombre', 100); // Nombre del nivel
            $table->integer('escuela_id');
            $table->integer('orden')->default(1);
            $table->boolean('habilitar_calificaciones')->default(false); // Habilitar calificaciones
            $table->boolean('habilitar_asistencias')->default(false); // Habilitar asistencias
            $table->integer('asistencias_minimas')->nullable(); // Asistencias mínimas requeridas
            $table->integer('asistencias_minima_alerta')->nullable();
            $table->text('descripcion')->nullable(); // Descripción de la materia
            $table->boolean('habilitar_inasistencias')->default(false);
            $table->boolean('habilitar_alerta_inasistencias')->default(false); // Habilitar alerta de inasistencias
            $table->boolean('habilitar_traslado')->default(false); // Habilitar traslados
            $table->boolean('caracter_obligatorio')->default(false); // Carácter obligatorio de la materia
            $table->string('portada',500)->default("default.png")->nullable();
            $table->timestamps(); // Fechas de creación y actualización
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('niveles_academicos');
  }
};
