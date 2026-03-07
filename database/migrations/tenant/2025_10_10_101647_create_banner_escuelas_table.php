<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     */
    public function up(): void
    {
      Schema::create('banner_escuelas', function (Blueprint $table) {
        $table->id();
        $table->string('imagen'); // Para guardar la ruta y nombre del archivo
        $table->text('descripcion')->nullable(); // La descripción puede ser opcional
        $table->boolean('activo')->default(true); // Por defecto, estará activo al crearse
        $table->timestamps(); // Campos created_at y updated_at
      });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('banner_escuelas');
    }
};
