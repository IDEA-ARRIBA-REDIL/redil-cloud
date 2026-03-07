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
    Schema::create('clasificacion_asistente_reporte_reunion', function (Blueprint $table) {
      $table->id();
      $table->integer('reporte_reunion_id');
      $table->integer('clasificacion_asistente_id');
      $table->integer('cantidad');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('clasificacion_asistente_reporte_reunion');
  }
};
