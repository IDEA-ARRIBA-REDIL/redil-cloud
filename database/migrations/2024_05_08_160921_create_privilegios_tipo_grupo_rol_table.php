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
    // antes privilegios_tipo_grupo_tipo_usuario
    Schema::create('privilegios_tipo_grupo_rol', function (Blueprint $table) {
      $table->id();
      $table->timestamps();
      $table->integer('tipo_grupo_id');
      $table->integer('rol_id'); // ante tipo_usuario_id
      $table->boolean('asignar_encargado')->default(0);
      $table->boolean('desvincular_encargado')->default(0);
      $table->boolean('asignar_asistente')->default(0);
      $table->boolean('desvincular_asistente')->default(0)->nullable();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('privilegios_tipo_grupo_rol');
  }
};
