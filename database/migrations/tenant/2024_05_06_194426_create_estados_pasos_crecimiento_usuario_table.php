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
    Schema::create('estados_pasos_crecimiento_usuario', function (Blueprint $table) {
      $table->id();
      $table->string('nombre', 100);
      $table->string('color', 100);
      $table->smallInteger('puntaje')->nullable()->default(0);
      $table->boolean('default')->default(0);
      $table->softDeletes();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('estados_pasos_crecimiento_usuario');
  }
};
