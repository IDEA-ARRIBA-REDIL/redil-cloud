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
    Schema::create('tipo_ofrenda', function (Blueprint $table) {
      $table->id();
      $table->text('descripcion');
      $table->boolean('generica')->default(false);
      $table->string('nombre');
      $table->boolean('formulario_donaciones')->default(false);
      $table->string('codigo_sap')->nullable();
      $table->boolean('tipo_reunion')->nullable();
      $table->boolean('ofrenda_obligatoria')->default(false); // Esto funciona para las ofrendas de reporte de grupo
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('tipo_ofrenda');
  }
};
