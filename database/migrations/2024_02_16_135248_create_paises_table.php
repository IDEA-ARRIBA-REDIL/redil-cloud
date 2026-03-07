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
    Schema::create('paises', function (Blueprint $table) {
      $table->id();
      $table->string('nombre', '50');
      $table->integer('continente_id')->nullable();
      $table->string('latitud', '25')->nullable();
      $table->string('longitud', '25')->nullable();
      $table->string('prefijo', '50')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('paises');
  }
};
