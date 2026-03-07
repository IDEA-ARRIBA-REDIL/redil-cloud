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
    Schema::create('tipo_vinculaciones', function (Blueprint $table) {
      $table->id();
      $table->string('nombre', 30);
      $table->boolean('por_grupo')->default(0); // indicar que es por grupo me ayudara para obtener el tipo de vinculación para cuando sea registros automatizados de nuevos usuarios desde el reporte de grupo
      $table->timestamps();
      $table->softDeletes();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('tipo_vinculaciones');
  }
};
