<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecutar las migraciones.
     */
    public function up(): void
    {
        Schema::create('curso_item_user', function (Blueprint $table) {
            $table->id();

            // Llaves foráneas para identificar al ítem y al usuario
            $table->foreignId('curso_item_id')->constrained('curso_items')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Estado actual del ítem para el estudiante
            // Usamos enum para restringir los valores a los permitidos
            $table->enum('estado', ['iniciado', 'completado'])->default('iniciado');

            // Fecha opcional que guarda el momento exacto en el que el ítem fue culminado
            $table->timestamp('fecha_completado')->nullable();

            $table->timestamps();

            // Aseguramos que un usuario no pueda tener dos registros para el mismo ítem
            $table->unique(['curso_item_id', 'user_id']);
        });
    }

    /**
     * Revertir las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('curso_item_user');
    }
};
