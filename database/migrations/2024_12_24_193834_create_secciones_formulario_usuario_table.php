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
        Schema::create('secciones_formulario_usuario', function (Blueprint $table) {
          $table->id();
          $table->timestamps();
          $table->string('nombre', 200); //Este va ser para identificarlo en la plataforma
          $table->string('titulo', 200); // Este es el que se va mostrar para el usuario
          $table->integer('formulario_usuario_id');
          $table->smallInteger('orden');
          $table->string('icono', 200)->nullable(); // Esta es un icono que va a lado de titulo en la los formularios tipo step
          $table->string('logo', 200)->nullable(); // Esta es una imagen que va a lado de titulo en algunos secciones de algunos formulario
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('secciones_formulario_usuario');
    }
};
