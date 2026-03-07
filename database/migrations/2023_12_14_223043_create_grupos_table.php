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
    Schema::create('grupos', function (Blueprint $table) {
      $table->id();
      $table->timestamps();
      $table->string('nombre', 100);
      $table->string('portada', 500)->default("default.png")->nullable();
      $table->string('direccion', 200)->nullable();
      $table->string('telefono', 50)->nullable();
      $table->text('rhema')->nullable();
      $table->date('fecha_apertura')->nullable();
      $table->smallInteger('dia')->nullable();
      $table->time('hora')->nullable();
      $table->integer('nivel')->nullable();
      $table->boolean('dado_baja')->deafult(false);
      $table->integer('tipo_grupo_id');
      $table->smallInteger('tipo_vivienda_id')->nullable(); // antes tipo_vivienda
      $table->smallInteger('barrio_id')->nullable();
      $table->smallInteger('dia_planeacion')->nullable();
      $table->string('codigo', 100)->nullable();
      $table->time('hora_planeacion')->nullable();
      $table->string('barrio_auxiliar', 50)->nullable();
      $table->string('latitud', 25)->nullable();
      $table->string('longitud', 25)->nullable();
      $table
        ->boolean('contiene_amo')
        ->nullable()
        ->default(0);
      $table->boolean('inactivo')->default(1);
      $table->integer('sede_id')->default(5);
      $table
        ->dateTime('ultimo_reporte_grupo')
        ->nullable()
        ->default('2016-01-01 05:00:01');
      $table
        ->dateTime('ultimo_reporte_grupo_auxiliar')
        ->nullable()
        ->default('2016-01-01 05:00:01');
      $table->integer('rol_de_creacion_id')->nullable(); // antes tipo_usuario_de_creacion_id
      $table->integer('asistente_de_creacion_id')->nullable();
      $table->integer('indice_grafico_ministerial')->default(1);
      $table->integer('usuario_creacion_id')->nullable();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('grupos');
  }
};
