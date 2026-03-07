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
        Schema::create('curso_foro_respuestas', function (Blueprint $table) {
            $table->id();

            // Relación con el hilo/pregunta principal
            $table->foreignId('hilo_id')->constrained('curso_foro_hilos')->onDelete('cascade');

            // Quien responde (puede ser el alumno, otro alumno, o el asesor/creador)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Contenido de la respuesta (el mensaje del chat)
            $table->text('cuerpo')->comment('Contenido de la respuesta en la conversación');

            // Bandera para destacar si la respuesta fue dada por un asesor/creador con permisos
            $table->boolean('es_respuesta_oficial')->default(false)->comment('Verdadero si lo respondió un asesor con privilegio');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curso_foro_respuestas');
    }
};
