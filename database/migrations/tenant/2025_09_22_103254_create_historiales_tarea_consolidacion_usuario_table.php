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
        Schema::create('historiales_tarea_consolidacion_usuario', function (Blueprint $table) {
            $table->id();
            $table->date('fecha')->nullable();
            $table->text('detalle')->nullable();
            $table->foreignId('tarea_consolidacion_usuario_id')
              ->constrained('tarea_consolidacion_usuario');
            $table->integer('usuario_creacion_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historiales_tarea_consolidacion_usuario');
    }
};
