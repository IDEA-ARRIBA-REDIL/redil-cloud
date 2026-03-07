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
    Schema::create('reporte_reuniones', function (Blueprint $table) {
      $table->id();
      $table->timestamps();
      $table->integer('reunion_id');
      $table->date('fecha');
      $table->string('portada', 500)->default("default.png")->nullable();
      $table->integer('predicador')->nullable();
      $table->integer('predicador_diezmos')->nullable();
      $table->string('predicador_invitado', 50)->nullable();
      $table->string('predicador_diezmos_invitado', 50)->nullable();
      $table->text('observaciones')->nullable();
      $table->integer('invitados')->nullable()->default(0);
      $table->text('sumatoria_adicional_clasificacion')->nullable();
      $table->text('clasificacion_asistentes')->nullable();
      $table->integer('cantidad_asistencias')->nullable()->default(0); // este guarda la totalidad en añadir asistentes
      $table->integer('total_ofrendas')->nullable();
      $table->integer('autor_creacion')->nullable();
      $table->smallinteger('conteo_preliminar')->nullable()->default(0);
      $table->boolean('habilitar_reserva')->nullable()->default(0);
      $table->integer('dias_plazo_reserva')->nullable();
      $table->integer('aforo')->nullable();
      $table->boolean('habilitar_reserva_invitados')->nullable()->default(0);
      $table->integer('cantidad_maxima_reserva_invitados')->nullable();
      $table->boolean('habilitar_reserva_familiares')->nullable()->default(0);
      $table->integer('aforo_ocupado')->nullable();
      $table->boolean('solo_reservados_pueden_asistir')->nullable()->default(0);
      $table->text('url')->nullable();
      $table->text('iframe')->nullable();
      $table->integer('visualizaciones')->nullable()->default(0);
      $table->boolean('habilitar_preregistro_iglesia_infantil')->nullable()->default(0);
      $table->time('hora');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('reporte_reuniones');
  }
};
