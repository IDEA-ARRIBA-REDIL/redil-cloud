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
       // Esta tabla nace de la relación de muchos a muchos, entre el tipo_grupo y clasificaciones_asistentes.
        Schema::create('clasificacion_asistente_tipo_grupo', function (Blueprint $table) {
            $table->id();
            $table->integer('tipo_grupo_id');
            $table->integer('clasificacion_asistente_id'); // antes clasificacion_asistente_reporte_grupo_id
            $table->timestamps();

            //add comentario a la tabla
            $table->comment('Esta tabla nace de la relación de muchos a muchos, entre el tipo_grupo y clasificaciones_asistentes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clasificacion_asistente_tipo_grupo');
    }
};
