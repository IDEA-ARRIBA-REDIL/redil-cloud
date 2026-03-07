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
        Schema::create('tipos_actividad', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->text('descripcion');
            $table->timestamps();
            $table->string('color', 30)->nullable();
            //$table->boolean('visualizada_por_todos')->default('false')->nullable();
            //$table->boolean('tipo_evento')->default('false')->nullable();
            //$table->integer('estado');
            $table->boolean('requiere_inscripcion')->default('false')->nullable();
            $table->boolean('unica_compra')->default('false')->nullable();
            $table->boolean('multiples_compras')->default('false')->nullable();
            $table->boolean('unica_inscripcion')->default('false')->nullable();
            $table->boolean('multiples_inscripciones')->default('false')->nullable();
            $table->boolean('requiere_inicio_sesion')->default('false')->nullable();
            $table->boolean('permite_abonos')->default('false')->nullable();
            $table->boolean('es_gratuita')->default('false')->nullable();
            //$table->boolean('visible')->default('false')->nullable();
            $table->boolean('tipo_escuelas')->default('false')->nullable();
            $table->boolean('inscripcion_parientes')->default('false')->nullable();
            $table->boolean('aplicar_restriccion_menores')->default('false')->nullable();
            $table->boolean('solo_menores_de_edad')->default('false')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipos_actividad');
    }
};
