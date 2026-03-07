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
        Schema::create('tipos_formulario_usuario', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('nombre', 100);
            $table->string('action', 300);
            $table->string('view', 100); // Esta variable se guarda la ruta de la vista que va abrir, ejemplo contenido.paginas.usuario.nuevo
            $table->string('redirect', 300)->nullable();
            $table->string('layout')->default('layouts/layoutMaster');
            $table->boolean('es_formulario_exterior')->default(0);
            $table->boolean('es_formulario_nuevo')->default(0);
            $table->boolean('es_formulario_autoeditar')->default(0);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipos_formulario_usuario');
    }
};
