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
        Schema::create('formulario_usuario_sede_excluida', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formulario_usuario_id')->constrained('formularios_usuario')->onDelete('cascade');
            $table->foreignId('sede_id')->constrained('sedes')->onDelete('cascade');
            $table->unique(['formulario_usuario_id', 'sede_id'], 'form_usuario_sede_excluida_unique');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formulario_usuario_sede_excluida');
    }
};
