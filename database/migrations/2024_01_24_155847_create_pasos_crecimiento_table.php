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
    Schema::create('pasos_crecimiento', function (Blueprint $table) {
      $table->id();
      $table->string('nombre', 50);
      $table->smallInteger('orden')->nullable();
      $table->string('descripcion', 200)->nullable();
      $table->integer('seccion_paso_crecimiento_id');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('pasos_crecimiento');
  }
};
