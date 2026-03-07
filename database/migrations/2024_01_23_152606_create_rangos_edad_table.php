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
    Schema::create('rangos_edad', function (Blueprint $table) {
      $table->id();
      $table->string('nombre', 30)->nullable();
      $table->integer('configuracion_id');
      $table->string('descripcion', 100)->nullable();
      $table->smallInteger('edad_maxima');
      $table->smallInteger('edad_minima');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('rangos_edad');
  }
};
