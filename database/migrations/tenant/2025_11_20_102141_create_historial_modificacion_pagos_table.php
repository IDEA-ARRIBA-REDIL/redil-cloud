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
        Schema::create('historial_modificacion_pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asesor_id')->constrained('users');
            $table->foreignId('caja_id')->constrained('cajas');
            $table->foreignId('punto_de_pago_id')->constrained('puntos_de_pago');
            $table->foreignId('compra_id')->constrained('compras');
            $table->foreignId('pago_id')->constrained('pagos');
            $table->foreignId('usuario_afectado_id')->constrained('users');
            $table->foreignId('actividad_id')->constrained('actividades');
            $table->foreignId('categoria_actividad_id')->constrained('actividad_categorias');
            $table->foreignId('tipo_pago_id')->constrained('tipos_pago'); 
            $table->decimal('valor', 10, 2);
            $table->text('motivo');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_modificacion_pagos');
    }
};
