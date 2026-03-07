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
        Schema::create('horarios_habituales', function (Blueprint $table) {
          $table->id();
          $table->foreignId('consejero_id')->constrained('consejeros')->onDelete('cascade');

          /** * Día de la semana. (1 = Lunes, ..., 7 = Domingo) */
          $table->unsignedTinyInteger('dia_semana');

          $table->time('hora_inicio');
          $table->time('hora_fin');

          $table->timestamps();

          // Índices
          $table->index(['consejero_id', 'dia_semana']);
          // Tu restricción para evitar duplicados exactos
          $table->unique(['consejero_id', 'dia_semana', 'hora_inicio']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horarios_habituales');
    }
};
