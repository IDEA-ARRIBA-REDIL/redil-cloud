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
    Schema::create('servicios_servidores_grupo', function (Blueprint $table) {
      $table->id();
      $table->timestamps();
      $table->integer('servidores_grupo_id');
      $table->integer('tipo_servicio_grupos_id');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('servicios_servidores_grupo');
  }
};
