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
         Schema::create('traslados_matricula_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matricula_id')->constrained('matriculas')->onDelete('cascade');
            $table->foreignId('origen_horario_id')->comment('ID del HorarioMateriaPeriodo original')->constrained('horarios_materia_periodo')->onDelete('cascade');
            $table->foreignId('destino_horario_id')->comment('ID del HorarioMateriaPeriodo nuevo')->constrained('horarios_materia_periodo')->onDelete('cascade');
            $table->foreignId('user_id')->comment('ID del administrador que realizó el traslado')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('traslados_matricula_log');
    }
};
