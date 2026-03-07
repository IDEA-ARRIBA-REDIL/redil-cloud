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
        Schema::create('estado_civil_filtro_consolidacion', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
               // La conexión al filtro
            $table->foreignId('filtro_consolidacion_id')->constrained('filtros_consolidacion')->onDelete('cascade');

            // La conexión a la tarea
            $table->foreignId('estado_civil_id')->constrained('estados_civiles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estado_civil_filtro_consolidacion');
    }
};
