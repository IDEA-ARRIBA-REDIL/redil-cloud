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
        Schema::create('bloques_clasificacion_asistente', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('tipo_calculo')->default('sumatoria'); // sumatoria, promedio
            $table->timestamps();
        });

        Schema::create('bloque_clasif_asistente_clasif_asistente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bloque_id')->constrained('bloques_clasificacion_asistente')->onDelete('cascade');
            $table->foreignId('clasificacion_asistente_id')->constrained('clasificaciones_asistentes')->onDelete('cascade');
            
             // A diferencia del ejemplo anterior, NO hacemos unique el clasificacion_asistente_id solo
             // porque un asistente puede estar en varios bloques.
             // Pero SI debe ser único el par bloque-asistente para no repetirlo en el MISMO bloque.
            $table->unique(['bloque_id', 'clasificacion_asistente_id'], 'bloque_clasif_unique');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bloque_clasif_asistente_clasif_asistente');
        Schema::dropIfExists('bloques_clasificacion_asistente');
    }
};
