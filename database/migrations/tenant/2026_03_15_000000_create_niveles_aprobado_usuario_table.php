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
        Schema::create('niveles_aprobado_usuario', function (Blueprint $table) {
            $table->id();

            // --- Claves Foráneas ---
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('nivel_id')->constrained('niveles_escuelas')->onDelete('cascade');
            $table->integer('periodo_id')->nullable();

            // --- Datos del Resultado ---
            $table->boolean('aprobado')->default(false)->comment('true: aprobado, false: reprobado');
            $table->decimal('nota_final', 5, 2)->nullable();

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
        Schema::dropIfExists('niveles_aprobado_usuario');
    }
};
