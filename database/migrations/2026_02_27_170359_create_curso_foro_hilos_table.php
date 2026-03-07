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
        Schema::create('curso_foro_hilos', function (Blueprint $table) {
            $table->id();

            // Relación con el curso
            $table->foreignId('curso_id')->constrained('cursos')->onDelete('cascade');

            // Relación opcional con el ítem o lección exacta donde nace la duda
            $table->foreignId('curso_item_id')->nullable()->constrained('curso_items')->onDelete('cascade');

            // Usuario estudiante que realiza la pregunta
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Contenido principal de la pregunta
            $table->string('titulo')->nullable()->comment('Resumen o título opcional generado');
            $table->text('cuerpo')->comment('Contenido detallado de la duda o pregunta');

            // Estado de resolución
            // pendiente: Espera ser respondida
            // resuelto: Ya fue contestada satisfactoriamente
            // cerrado: No admite más respuestas
            $table->enum('estado', ['pendiente', 'resuelto', 'cerrado'])->default('pendiente');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curso_foro_hilos');
    }
};
