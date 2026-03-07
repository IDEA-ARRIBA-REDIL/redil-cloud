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
    Schema::create('reuniones', function (Blueprint $table) {
      $table->id();
      $table->time('hora');
      //$table->smallInteger('dia');
      $table->string('nombre', 100);
      $table->string('portada', 500)->default("default.png")->nullable();
      $table->text('descripcion')->nullable();
      $table->integer('sede_id')->nullable();
      $table->string('sedes_asistencia')->nullable();
      $table->string('genero', 100)->nullable();
      $table->boolean('habilitar_reserva')->nullable()->default(0);
      $table->smallInteger('dias_plazo_reporte')->nullable();
      $table->smallInteger('dias_plazo_reserva')->nullable()->default(false);
      $table->integer('aforo')->nullable();
      $table->boolean('habilitar_reserva_invitados')->nullable()->default(0);
      $table->integer('cantidad_maxima_reserva_invitados')->nullable();
      $table->boolean('habilitar_reserva_familiares')->nullable()->default(0);
      $table->boolean('solo_reservados_pueden_asistir')->nullable()->default(0);
      $table->time('hora_maxima_reportar_asistencia')->nullable()->default('11:59 PM');
      $table->boolean('habilitar_preregistro_iglesia_infantil')->nullable()->default(0);
      $table->softDeletes(); // deleted_at
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('reuniones');
  }
};
