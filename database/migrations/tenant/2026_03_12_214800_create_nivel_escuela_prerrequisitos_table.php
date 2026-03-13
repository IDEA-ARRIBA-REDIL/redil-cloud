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
        Schema::create('nivel_escuela_prerrequisitos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nivel_escuela_id_inicial')->comment('El nivel que se esta creando u editando');
            $table->unsignedBigInteger('nivel_escuela_requerido_id')->comment('El nivel que se requiere para poder inscribirse');
            $table->unsignedBigInteger('escuela_id');
            $table->timestamps();

            // Índices para mejorar el rendimiento
            $table->index('nivel_escuela_id_inicial');
            $table->index('nivel_escuela_requerido_id');
            $table->index('escuela_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nivel_escuela_prerrequisitos');
    }
};
