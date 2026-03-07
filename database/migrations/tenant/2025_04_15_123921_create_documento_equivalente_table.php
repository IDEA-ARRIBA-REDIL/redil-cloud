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
    Schema::create('documento_equivalente', function (Blueprint $table) {
      $table->id();
      $table->string('nombre', 100);
      $table->string('identificacion', 20);
      $table->integer('cantidad');
      $table->text('detalle')->nullable();
      $table->string('telefono', 20)->nullable();
      $table->string('direccion', 200)->nullable();
      $table->double('valor', 12, 4);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('documento_equivalente');
  }
};
