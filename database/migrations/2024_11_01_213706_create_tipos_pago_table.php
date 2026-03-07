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
    Schema::create('tipos_pago', function (Blueprint $table) {
      $table->id();
      // Cadenas de texto
      $table->string('nombre', 30);
      $table->string('enlace', 100);
      $table->string('imagen', 100);
      $table->string('cuenta_sap', 30);
      $table->text('observaciones');
      $table->string('client_id', 500)->nullable();
      $table->string('key_id', 500)->nullable();
      $table->string('bussines_id', 500)->nullable();
      $table->string('url_retorno', 500)->nullable();
      $table->string('identity_token', 500)->nullable();
      $table->string('key_reservada', 50)->nullable();
      $table->string('account_id', 50)->nullable();
      $table->string('color', 30)->nullable();
      $table->string('fondo', 30)->nullable();
      $table->text('label_destinatario')->nullable();

      // Numéricos
      $table->integer('unica_moneda_id')->nullable();
      $table->integer('porcentaje_tax1')->nullable();
      $table->integer('porcentaje_tax2')->nullable();
      $table->integer('transaccion_minima')->nullable();
      $table->integer('transaccion_maxima')->nullable();
      $table->integer('incremento_pdp')->nullable();

      // Booleanos (CORREGIDO: sin comillas en true/false)
      $table->boolean('activo')->default(true)->nullable();
      $table->boolean('habilitado_punto_pago')->default(false)->nullable();
      $table->boolean('subir_archivo_pagos')->default(false)->nullable();
      $table->boolean('botones_valores_moneda')->default(false)->nullable();
      $table->boolean('habilitado_donacion')->default(false)->nullable();
      $table->boolean('tiene_limite_dinero_acumulado')->default(false)->nullable();
      $table->boolean('punto_de_pago')->default(false)->nullable();
      $table->boolean('permite_personas_externas')->default(false)->nullable();
      $table->boolean('codigo_datafono')->default(false)->nullable();

      $table->timestamps();
      $table->softDeletes();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('tipos_pago');
  }
};
