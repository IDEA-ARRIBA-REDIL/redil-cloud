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
      // Esta tabla guarda los campos que van a aparecer en el formulario de auto-editar tipo secciones (creado para manantial)
        Schema::create('campos_perfil_usuario', function (Blueprint $table) {
          $table->id();
          $table->timestamps();
          $table->string('nombre', 100);
          $table->string('placeholder', 100)->nullable();
          $table->string('nombre_bd', 100);
          $table->boolean('tiene_descargable')->default(0);
          $table->integer('seccion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campos_perfil_usuario');
    }
};
