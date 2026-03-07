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
    // antes se llamaba  clasificacion_asistentes_reporte_grupo
    Schema::create('clasificaciones_asistentes', function (Blueprint $table) {
      $table->id();
      $table->timestamps();
      $table->string('nombre', 50);
      $table->text('descripcion')->nullable();
      $table->boolean('tiene_sumatoria_adicional')->default(0)->comment('Este campo me indica si una de estas clasificaciones va tener botones de sumar y restar en el reporte.');
      $table->boolean('sumar_asistencias_encargados')->default(0)->comment('Al estar TRUE suma a la clasificación las asistencias de los encargados.');
      $table->smallInteger('genero')->nullable()->comment('este campo es 0 si es hombre y 1 si es mujer, se utiliza para indicar si una de la clasificaciones se delimita por género.  si es NULL se valen los dos géneros.');
      $table->boolean('todos_los_asistentes')->default(0)->comment('si es TRUE "incluye los asistentes que asistieron y los que no", si es FALSE "Solo los que asistieron al reporte"');
      $table->boolean('sumar_al_total_de_asistencias')->default(0);
      $table->boolean('clasificacion_encargado_por_clasificacion_individual')->default(0)->nullable();
      $table->boolean('cargar_por_default_en_informes')->default(0)->nullable()->comment('Si es true se pre-cargara en el filtro de clarificación que hay en los informes, por ejemplo en el informe de asistencia semanal a grupos');
      $table->smallInteger('orden')->default(0)->nullable();
      $table->comment('Esta tabla sirve para crear la clasificación de asistentes, la cual se va a mostrar en el reporte de grupo y tambien se usa en los reportes de reunion. ');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('clasificaciones_asistentes');
  }
};
