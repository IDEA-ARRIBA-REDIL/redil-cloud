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
        // Tabla para Pasos de Crecimiento en Niveles (Inicio y Culminación)
        Schema::create('nivel_paso_crecimiento', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nivel_id');
            $table->unsignedBigInteger('paso_crecimiento_id');
            $table->integer('estado')->nullable()->comment('Estado legado');
            $table->integer('al_iniciar')->nullable()->comment('1 si es al iniciar, 0 si es al culminar');
            $table->integer('indice')->default(0);
            $table->unsignedBigInteger('estado_paso_crecimiento_usuario_id')->nullable();
            $table->timestamps();

            $table->index('nivel_id');
            $table->index('paso_crecimiento_id');
        });

        // Tabla para Procesos Prerrequisito en Niveles
        Schema::create('nivel_proceso_prerrequisito', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nivel_id');
            $table->unsignedBigInteger('paso_crecimiento_id');
            $table->integer('estado_proceso')->nullable();
            $table->integer('indice')->default(0);
            $table->unsignedBigInteger('estado_paso_crecimiento_usuario_id')->nullable();
            $table->timestamps();

            $table->index('nivel_id');
            $table->index('paso_crecimiento_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nivel_proceso_prerrequisito');
        Schema::dropIfExists('nivel_paso_crecimiento');
    }
};
