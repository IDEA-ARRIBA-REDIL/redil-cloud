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
        Schema::create('curso_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained('cursos')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Estado de la inscripción
            $table->enum('estado', ['activo', 'finalizado', 'suspendido'])->default('activo');

            // Control de tiempo
            $table->dateTime('fecha_inscripcion')->useCurrent();
            $table->dateTime('fecha_vencimiento_acceso')->nullable();

            // Seguimiento
            $table->integer('porcentaje_progreso')->default(0);

            $table->timestamps();

            // Evitar inscripciones duplicadas activas (o permitir historial pero controlando por software)
            // $table->unique(['curso_id', 'user_id']); // Opcional, dependiendo de si permites re-comprar al vencer.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curso_users');
    }
};
