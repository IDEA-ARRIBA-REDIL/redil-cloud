<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_alumno_respuesta_items_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumno_respuesta_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->comment('ID del Alumno')->constrained('users')->onDelete('cascade');
            $table->foreignId('item_corte_materia_periodo_id')->comment('ID del Ítem calificado')->constrained('item_corte_materia_periodo')->onDelete('cascade');
            // $table->foreignId('matricula_horario_materia_periodo_id')->comment('FK a la inscripción del alumno en el horario (EstadoAcademico)')->constrained('matricula_horario_materia_periodo')->onDelete('cascade'); // Opcional, pero útil para contexto

            $table->decimal('nota_obtenida', 5, 2)->nullable();
            $table->text('respuesta_alumno')->nullable()->comment('Respuesta de texto del alumno');
            $table->string('enlace_documento_alumno', 1024)->nullable()->comment('Enlace al PDF u otro documento');
            // $table->string('ruta_documento_alumno', 1024)->nullable()->comment('Ruta en storage si subes el archivo al servidor');

            $table->foreignId('calificador_user_id')->nullable()->comment('ID del Usuario que calificó')->constrained('users')->onDelete('set null');
            $table->timestamp('fecha_calificacion')->nullable();
            $table->text('observaciones_maestro')->nullable();

            $table->timestamps();

            // Un alumno solo puede tener una respuesta/calificación por ítem
            $table->unique(['user_id', 'item_corte_materia_periodo_id'], 'alumno_item_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumno_respuesta_items');
    }
};