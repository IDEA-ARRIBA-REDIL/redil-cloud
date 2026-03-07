<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // La secuencia 'estados_tipo_pagos_id_seq' es manejada automáticamente por Laravel
        // al usar el método id() o bigIncrements(), por lo que no es necesario crearla manualmente.
        Schema::create('estados_pago', function (Blueprint $table) {
            $table->id(); // Equivalente a: integer DEFAULT nextval(...) NOT NULL y PRIMARY KEY
            $table->string('nombre', 40)->nullable();
            $table->string('color', 10)->nullable();

            // Definimos la clave foránea para tipo_pago_id
            $table->foreignId('tipo_pago_id')->nullable();

            $table->boolean('estado_inicial_defecto')->nullable();
            $table->boolean('estado_final_inscripcion')->nullable();

            // Este campo es clave para mapear los códigos de estado de ZonaPagos
            $table->integer('id_codigo_externo')->nullable()->comment('Mapea el código de estado de la pasarela de pago (ej: 1, 999, 1000 de ZonaPagos)');

            $table->boolean('estado_anulado_inscripcion')->nullable();
            $table->boolean('imprimir_recibo')->default(false);
            $table->boolean('modificar')->default(false);
            $table->boolean('eliminar')->default(false);
            $table->boolean('estado_pendiente')->default(false);

            // La tabla no parece usar timestamps created_at/updated_at según el SQL
            // por lo que no los añadimos.
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('estados_pago');
    }
};
