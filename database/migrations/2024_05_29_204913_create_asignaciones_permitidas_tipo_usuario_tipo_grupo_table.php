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
        Schema::create('asignaciones_permitidas_tipo_usuario_tipo_grupo', function (Blueprint $table) {
          $table->id();
          $table->timestamps();
          $table->integer('tipo_usuario_id'); // antes tipo_asistente_id
          $table->integer('tipo_grupo_id');
          $table->boolean('para_encargados')->default(0);
          $table->boolean('para_asistentes')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignaciones_permitidas_tipo_usuario_tipo_grupo');
    }
};
