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
    Schema::create('egresos', function (Blueprint $table) {
      $table->id();
      $table->date('fecha');
      $table->integer('proveedor_id')->nullable();
      $table->integer('documento_equivalente_id')->nullable();
      $table->integer('caja_finanzas_id')->nullable();
      $table->integer('centro_de_costos_egresos_id');
      $table->integer('tipo_egreso_id')->nullable();
      $table->decimal('valor', 10, 2); // Usamos 'decimal' en lugar de 'numeric' por convención en Laravel
      $table->text('descripcion')->nullable();
      $table->string('campo_adicional1', 100)->nullable();
      $table->string('campo_adicional2', 100)->nullable();
      $table->boolean('anulado')->default(false);
      $table->text('motivo_anulacion')->nullable(); // Esto suena más a texto que a un ID. Si es un ID, debería ser integer.
      $table->integer('usuario_anulacion_id')->nullable();
      $table->timestamp('fecha_anulacion')->nullable();
      $table->integer('sede_id')->nullable();
      $table->unsignedBigInteger('moneda_id')->nullable()->after('sede_id');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('egresos');
  }
};
