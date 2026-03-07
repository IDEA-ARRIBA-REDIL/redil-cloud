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
    Schema::create('ofrendas', function (Blueprint $table) {
      $table->id();
      $table->integer('tipo_ofrenda_id');
      $table->decimal('valor', 10, 2);
      $table->decimal('valor_real', 10, 2)->default(0);
      $table->date('fecha');
      $table->smallInteger('ingresada_por'); //ingresada_por: 0 reuniones - 1 grupos - 2 otros
      $table->text('observacion')->nullable();
      $table->integer('user_id')->nullable();
      $table->integer('estado_pago_id')->nullable();
      $table->integer('tipo_pago_id')->nullable();
      $table->integer('moneda_id')->default(1);
      $table->integer('persona_externa_id')->nullable();
      $table->string('referencia_donacion')->nullable();
      $table->integer('int_id_forma_pago')->nullable();
      $table->integer('destinatario')->nullable();
      $table->integer('registro_caja_id')->nullable();
      $table->integer('ofrenda_multiple_id')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('ofrendas');
  }
};
