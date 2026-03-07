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
    Schema::create('proveedores', function (Blueprint $table) {
      $table->id();
      $table->string('nombre', 100);
      $table->string('identificacion', 20);
      $table->string('tipo_identificacion', 10);
      $table->string('telefono', 20);
      $table->string('direccion', 200);
      $table->string('correo')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('proveedores');
  }
};
