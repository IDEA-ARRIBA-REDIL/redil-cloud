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
    Schema::create('tipo_sedes', function (Blueprint $table) {
      $table->id();
      $table->timestamps();

      $table->string('nombre', 30)->nullable();
      $table->string('descripcion', 200)->nullable();
      $table
        ->boolean('realiza_reuniones')
        ->nullable()
        ->default(1);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('tipo_sedes');
  }
};
