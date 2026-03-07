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
    Schema::create('reporte_bajas_altas', function (Blueprint $table) {
      $table->id();
      $table->timestamps();
      $table->string('motivo', 100)->nullable();
      $table->text('observaciones')->nullable();
      $table->date('fecha')->nullable();
      $table->integer('user_id'); // antes asistente_id
      $table->boolean('dado_baja'); //	FALSE: es dado alta; TRUE: dado bajaa
      $table
        ->integer('tipo_baja_alta_id')
        ->default(1)
        ->nullable();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('reporte_bajas_altas');
  }
};
