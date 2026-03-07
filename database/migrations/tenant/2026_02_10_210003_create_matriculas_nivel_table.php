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
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade'); // Asumiendo tabla users
            $table->foreignId('nivel_agrupacion_id')->constrained('niveles_agrupacion')->onDelete('cascade');
            $table->foreignId('periodo_id')->constrained('periodos')->onDelete('cascade'); // Asumiendo tabla periodos

            $table->enum('estado', ['activa', 'completada', 'reprobada', 'cancelada'])->default('activa');
            $table->timestamp('fecha_matricula')->useCurrent();
            $table->timestamp('fecha_finalizacion')->nullable();

            $table->text('observaciones')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Un estudiante solo debería tener una matrícula activa por nivel en un periodo
            $table->index(['usuario_id', 'periodo_id']);
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
