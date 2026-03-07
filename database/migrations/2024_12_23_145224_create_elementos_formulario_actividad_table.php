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
    Schema::create('elementos_formulario_actividad', function (Blueprint $table) {
      $table->id();
      $table->string('titulo', 100);
      $table->integer('tipo_elemento_id');
      $table->integer('actividad_id')->nullable();
      $table->boolean('required')->default('false')->nullable();
      $table->boolean('visible')->default('true')->nullable();
      $table->text('descripcion')->nullable();
      $table->integer('orden')->nullable();
      $table->integer('long_max')->nullable();
      $table->integer('long_min')->nullable();
      $table->integer('peso_maximo')->nullable();
      $table->integer('largo')->nullable();
      $table->integer('ancho')->nullable();
      $table->boolean('visible_asistencia')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('elementos_formulario_actividad');
  }
};
