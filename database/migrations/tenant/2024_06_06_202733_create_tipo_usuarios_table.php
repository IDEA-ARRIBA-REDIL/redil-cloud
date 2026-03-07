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
    // Esta tabla era "tipo_asistentes"
    Schema::create('tipo_usuarios', function (Blueprint $table) {
      $table->id();
      $table->string('nombre', 50);
      $table->string('descripcion', 200)->nullable();
      $table->timestamps();
      $table->string('color', 10)->nullable();
      $table->string('icono', 50)->nullable();
      $table->string('imagen', 50)->nullable();
      $table->string('nombre_plural', 50)->nullable();
      $table->boolean('tipo_pastor')->default(0);
      $table->boolean('tipo_pastor_principal')->default(0);
      $table->string('id_rol_dependiente')->nullable(); // antes id_tipo_usuario_dependiente
      $table->smallInteger('orden')->nullable();
      $table->boolean('seguimiento_actividad_grupo')->nullable()->default(1);
      $table->boolean('seguimiento_actividad_reunion')->nullable()->default(1);
      $table->boolean('habilitado_para_consolidacion')->nullable()->default(0); // Es true si se va a listar en el módulo de consolidación
      $table->smallInteger('puntaje')->nullable()->default(0);
      $table->boolean('visible')->default(1); // Este campo sirve para ocutar o mostrar a los usuarios que tengan este tipo de usuario en los listados y busquedas de la plataforma
      $table->boolean('default')->default(0); // Este campo sirve para determinar que tipo usuario por defecto
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('tipo_usuarios');
  }
};
