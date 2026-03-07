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
    Schema::create('ingresos', function (Blueprint $table) {
      $table->id();
      $table->date('fecha');
      $table->string('nombre', 100);
      $table->string('identificacion', 100);
      $table->string('tipo_identificacion', 100);
      $table->string('telefono', 200)->nullable();
      $table->string('direccion', 200)->nullable();
      $table->decimal('valor', 10, 2);
      $table->text('descripcion')->nullable();

      $table->unsignedBigInteger('tipo_ofrenda_id');
      $table->unsignedBigInteger('caja_finanzas_id');

      $table->string('campo_adicional1')->nullable();
      $table->string('campo_adicional2')->nullable();

      $table->boolean('anulado')->default(false);
      $table->text('motivo_anulacion')->nullable();
      $table->unsignedBigInteger('usuario_anulacion')->nullable();
      $table->timestamp('fecha_anulacion')->nullable();

      $table->unsignedBigInteger('user_id')->nullable();
      $table->unsignedBigInteger('sede_id')->nullable();
      $table->unsignedBigInteger('moneda_id');
      $table->unsignedBigInteger('ofrenda_id')->nullable();

      //$table->boolean('aprobado_reporte_grupo')->nullable(); creo que no es necesario, si hay algo en valor_real es porque esta aprobado en reporte_grupo
      $table->decimal('valor_real', 10, 2)->nullable();

      $table->unsignedBigInteger('compra_id')->nullable();
      $table->boolean('ingreso_por_grupo')->nullable();
      $table->boolean('ingreso_por_reunion')->nullable();
      $table->boolean('ingreso_por_actividades')->nullable();
      $table->boolean('ingreso_por_ofrenda_online')->nullable();
      $table->boolean('ingreso_manual')->nullable();
      $table->integer('centro_de_costos_ingresos_id')->default(1);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('ingresos');
  }
};
