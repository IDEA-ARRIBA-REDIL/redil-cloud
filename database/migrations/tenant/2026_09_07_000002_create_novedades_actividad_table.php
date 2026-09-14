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
        Schema::create('novedades_actividad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actividad_id')->constrained('actividades')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('tipo_novedad_id')->constrained('tipos_novedad')->onDelete('restrict');

            // Campo para escuelas: materia o escuela a la que deseaba matricularse
            $table->foreignId('materia_id')->nullable()->constrained('materias')->onDelete('set null');
            $table->string('materia_nombre', 150)->nullable();

            // Datos de la persona que reporta
            $table->string('nombre', 150);
            $table->string('identificacion', 50);
            $table->string('telefono', 50);
            $table->string('email', 150);

            // Mensaje
            $table->string('asunto', 200);
            $table->text('descripcion');

            // Estados: no_revisado, iniciado, finalizado
            $table->string('estado', 30)->default('no_revisado');

            // Gestión y respuesta administrativa
            $table->text('respuesta')->nullable();
            $table->foreignId('respondido_por_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('fecha_respuesta')->nullable();

            $table->timestamps();

            // Índices para búsquedas y filtros de rendimiento
            $table->index('actividad_id');
            $table->index('estado');
            $table->index('identificacion');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('novedades_actividad');
    }
};
