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
        Schema::create('materias_aprobada_usuario', function (Blueprint $table) {
            $table->id();

            // --- Claves Foráneas ---
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('materia_id')->constrained('materias')->onDelete('cascade');

            // CORRECCIÓN: Se elimina ->nullable(). Esta columna es esencial para la clave única.
            $table->integer('materia_periodo_id')->nullable();

            // CORRECCIÓN: Se elimina ->nullable(). Un resultado siempre debe pertenecer a un periodo.
            $table->integer('periodo_id')->nullable();

            // --- Datos del Resultado ---
            $table->boolean('aprobado')->default(false)->comment('true: aprobado, false: reprobado');
            $table->decimal('nota_final', 5, 2)->nullable()->comment('Nota final ponderada calculada');
            $table->integer('total_asistencias')->nullable()->comment('Conteo final de asistencias');
            $table->string('motivo_reprobacion')->nullable()->comment('Motivo si el estado es reprobado');



            $table->boolean('es_homologacion')->default(false);
            $table->text('observacion_homologacion')->nullable();
            $table->integer('sede_id')->nullable()->default(2);
            $table->date('fecha_homologacion')->nullable();
            $table->integer('homologado_por_user_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materias_aprobada_usuario');
    }
};
