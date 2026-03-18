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
        Schema::create('matriculas_nivel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->comment('Alumno matriculado')->constrained('users')->onDelete('cascade');
            $table->foreignId('nivel_escuela_id')->comment('Grado/Nivel seleccionado')->constrained('niveles_escuelas')->onDelete('cascade');
            $table->foreignId('periodo_id')->comment('Periodo académico')->constrained('periodos')->onDelete('cascade');
            $table->string('estado')->default('activa')->comment('Estado de la matrícula (activa, finalizada, etc.)');
            $table->timestamp('fecha_matricula')->useCurrent();
            $table->timestamp('fecha_finalizacion')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matriculas_nivel');
    }
};
