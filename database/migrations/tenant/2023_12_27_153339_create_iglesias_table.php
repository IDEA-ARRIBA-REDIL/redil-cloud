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
    Schema::create('iglesias', function (Blueprint $table) {
      $table->id();
      $table->timestamps();
      $table->string('nombre', 100);
      $table->string('direccion', 200)->nullable();
      $table->string('telefono1', 20)->nullable();
      $table->string('telefono2', 20)->nullable();
      $table->string('rhema', 20)->nullable();
      $table->string('texto_rhema', 255)->nullable();
      $table->string('metas	character', 255)->nullable();
      $table->date('fecha_apertura')->nullable();
      $table->string('logo', 255);
      $table->integer('configuracion_id')->default(1);
      $table->integer('continente_id')->nullable();
      $table->integer('pais_id')->nullable();
      $table->integer('region_id')->nullable();
      $table->integer('departamento_id')->nullable();
      $table->integer('municipio_id')->nullable();
      $table->string('url_subdominio', 100)->nullable();
      $table->date('fecha_suscripcion')->nullable();
      $table->string('membresia_estimada', 100)->nullable();
      $table->string('website', 500)->nullable();
      $table->text('identificacion')->nullable();
      $table->date('fecha_vencimiento_licencia')->nullable();
      $table->text('mensaje_vencimiento_licencia')->nullable();
      $table->text('email_soporte')->nullable();
      $table->string('latitud', 25)->nullable();
      $table->string('longitud', 25)->nullable();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('iglesias');
  }
};
