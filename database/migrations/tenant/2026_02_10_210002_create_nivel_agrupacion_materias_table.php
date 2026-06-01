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
        Schema::create('nivel_agrupacion_materias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nivel_agrupacion_id')->constrained('niveles_agrupacion')->onDelete('cascade');
            $table->foreignId('materia_id')->constrained('materias')->onDelete('cascade');

            $table->boolean('es_obligatoria')->default(true);
            $table->integer('orden')->default(0);
            $table->integer('creditos')->default(1);

            $table->timestamps();

            // Evitar duplicados: una materia solo puede estar una vez en un nivel (opcional, dependerá de la lógica, pero recomendado)
            $table->unique(['nivel_agrupacion_id', 'materia_id'], 'unique_nivel_materia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nivel_agrupacion_materias');
    }
};
