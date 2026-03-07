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
    Schema::create('tipo_asignaciones', function (Blueprint $table) {
      $table->id();
      $table->timestamps();
      $table->string('nombre', 50);
      $table->string('descripcion', 100)->nullable();
      $table->boolean('para_asignar_lideres')->default(0)->nullable();
      $table->boolean('para_asignar_asistentes')->default(0)->nullable();
      $table->boolean('para_desvincular_asistentes')->default(0);
      $table->boolean('para_desvincular_lideres')->default(0);
      $table->boolean('default')->default(0); // Nuevo campo

    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('tipo_asignaciones');
  }
};
