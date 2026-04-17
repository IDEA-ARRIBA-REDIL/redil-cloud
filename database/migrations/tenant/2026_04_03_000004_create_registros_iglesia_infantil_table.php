<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registros_iglesia_infantil', function (Blueprint $table) {
            $table->id();
            $table->integer('reporte_reunion_id');
            $table->integer('menor_user_id');
            $table->integer('adulto_ingreso_user_id');
            $table->integer('adulto_retiro_user_id')->nullable();
            $table->integer('servidor_ingreso_user_id');
            $table->integer('servidor_retiro_user_id')->nullable();
            $table->integer('salon_infantil_id');
            $table->integer('estacion_salon_infantil_id');
            $table->text('indicaciones_medicas')->nullable();
            $table->string('codigo_retiro', 10);
            // 1. en_custodia: menor aún está en la iglesia infantil
            // 2. entregado: menor fue devuelto al adulto responsable
            $table->string('estado', 30)->default('en_custodia');
            $table->date('fecha');
            $table->time('hora_entrada');
            $table->time('hora_entrega')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registros_iglesia_infantil');
    }
};
