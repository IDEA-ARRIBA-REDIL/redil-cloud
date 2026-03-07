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
    Schema::create('respuestas_formulario_elemento_compra', function (Blueprint $table) {
      $table->id();
      $table->string('respuesta_texto_corto', 100)->nullable();
      $table->text('respuesta_texto_largo')->nullable();
      $table->double('respuesta_moneda')->nullable();
      $table->integer('respuesta_numero')->nullable();
      $table->integer('respuesta_si_no')->nullable();
      $table->integer('pariente_usuario_id')->nullable();
      $table->integer('respuesta_unica')->nullable(); // ID del usuario que responde (puede ser nulo)
      $table->string('respuesta_multiple')->nullable();
      $table->date('respuesta_fecha')->nullable();
      $table->string('url_foto', 200)->nullable(); // Url para guardar las imagenes solo Jpg ó Png
      $table->string('url_archivo', 200)->nullable(); // Url para guardar archivos solo .docx y .pdf
      $table->integer('compra_id')->nullable();
      $table->integer('inscripcion_id')->nullable();
      $table->integer('elemento_formulario_actividad_id');
      $table->integer('user_id')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('respuestas_formulario_elemento_compra');
  }
};
