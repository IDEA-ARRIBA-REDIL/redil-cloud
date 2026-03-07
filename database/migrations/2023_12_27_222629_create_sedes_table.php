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
    Schema::create('sedes', function (Blueprint $table) {
      $table->id();
      $table->timestamps();
      $table->string('nombre', 50)->nullable();
      $table->string('telefono', 20)->nullable();
      $table->string('direccion', 100)->nullable();
      $table->integer('tipo_sede_id');
      $table->integer('grupo_id');
      $table->text('descripcion')->nullable();
      $table->integer('barrio_id')->nullable();
      $table->string('barrio_auxiliar', 50)->nullable();
      $table->date('fecha_creacion')->nullable();
      $table->string('foto', 20)->nullable();
      $table->smallInteger('capacidad')->default(0);
      $table->integer('continente_id')->nullable();
      $table->integer('pais_id')->nullable();
      $table->integer('region_id')->nullable();
      $table->integer('departamento_id')->nullable();
      $table->integer('municipio_id')->nullable();
      $table->boolean('default')->default(0); // Nuevo campo
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {

    Schema::dropIfExists('sedes', function (Blueprint $table) {
      $table->dropForeign(['reuniones_id']);
      $table->dropColumn('reunion_id');
    });
  }
};
