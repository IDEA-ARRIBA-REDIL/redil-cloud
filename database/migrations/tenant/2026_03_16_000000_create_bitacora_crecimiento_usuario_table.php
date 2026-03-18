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
        Schema::create('bitacora_crecimiento_usuario', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedInteger('paso_crecimiento_id');
            $table->unsignedSmallInteger('estado_id_anterior')->nullable();
            $table->unsignedSmallInteger('estado_id_nuevo');
            $table->unsignedBigInteger('autor_id')->nullable();
            $table->unsignedInteger('sede_id')->nullable();
            $table->date('fecha')->nullable();
            $table->text('detalle')->nullable();
            $table->timestamps();

            // Índices para mejorar rendimiento en reportes
            $table->index(['user_id', 'paso_crecimiento_id'], 'user_paso_index');
            $table->index('sede_id');
            $table->index('fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bitacora_crecimiento_usuario');
    }
};
