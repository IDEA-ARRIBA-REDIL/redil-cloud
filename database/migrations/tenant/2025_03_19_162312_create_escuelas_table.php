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
        Schema::create('escuelas', function (Blueprint $table) {
            $table->id(); // ID único de la escuela
            $table->string('nombre', 200); // Nombre de la escuela
            $table->text('descripcion')->nullable(); // Descripción de la escuela
            $table->enum('tipo_matricula', ['materias_independientes', 'niveles_agrupados'])->default('materias_independientes'); // Tipo de matrícula
            $table->boolean('habilitada_consilidacion')->default(false); /// este es para el informe consolidado de conectate solo manantial
            $table->string('portada', 500)->default("default.png")->nullable();
            $table->integer('diploma_id')->nullable(); // Relación con diplomas
            $table->smallinteger('horasDisponiblidadLinkAsistencia')->default(2);
            $table->tinyInteger('dia_inicio_semana')->default(0);
            $table->timestamps(); // Fechas de creación y actualización

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('escuelas');
    }
};
