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
    Schema::create('crecimiento_usuario', function (Blueprint $table) {
      $table->id();
      $table->integer('paso_crecimiento_id');
      $table->integer('user_id');
      $table->timestamps();
      $table->smallInteger('estado_id')->nullable(); // antes estado
      $table->date('fecha')->nullable();
      $table->text('detalle')->nullable();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('crecimiento_usuario');
  }
};
