<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matriculas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->comment('Alumno que se matricula')->constrained('users')->onDelete('cascade');
            $table->foreignId('periodo_id')->comment('Periodo de la matrícula')->constrained('periodos')->onDelete('cascade');
            $table->foreignId('horario_materia_periodo_id')
                ->comment('Clase específica a la que se inscribe y paga')
                ->constrained('horarios_materia_periodo')
                ->onDelete('cascade'); // O restrict

            $table->string('referencia_pago')->nullable()->unique();
            $table->decimal('valor_a_pagar', 10, 2)->nullable();
            $table->decimal('valor_pagado', 10, 2)->nullable();
            $table->timestamp('fecha_pago')->nullable();
            $table->string('metodo_pago')->nullable();
            $table->string('estado_pago_matricula')->default('pendiente');
            $table->text('observacion')->nullable();
            $table->date('fecha_matricula');
            $table->integer('sede_id')->default(2);
            $table->integer('material_sede_id')->default(2)->nullable();
            $table->boolean('trasladado')->default('false');
            $table->timestamp('fecha_bloqueo')->nullable();
            $table->boolean('bloqueado')->default('false');
            $table->integer('escuela_id')->default(1);
            $table->timestamps();

            // Unicidad: Un usuario no debería tener dos "órdenes de matrícula/pago"
            // para el mismo horario.

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matriculas');
    }
};
