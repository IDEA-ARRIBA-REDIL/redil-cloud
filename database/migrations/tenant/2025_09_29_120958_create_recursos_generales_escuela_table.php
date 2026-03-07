<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recursos_generales_escuela', function (Blueprint $table) {
            $table->id();
            // No necesitamos 'horario_materia_periodo_id'
            // $table->foreignId('escuela_id')->constrained('escuelas')->cascadeOnDelete(); // Opcional: si el recurso pertenece a una escuela
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('tipo', 50); // Video, Documento, Enlace, etc.
            $table->string('link_externo')->nullable();
            $table->string('link_youtube')->nullable();
            $table->string('nombre_archivo')->nullable();
            $table->string('ruta_archivo')->nullable();
            $table->boolean('visible')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recursos_generales_escuela');
    }
};
