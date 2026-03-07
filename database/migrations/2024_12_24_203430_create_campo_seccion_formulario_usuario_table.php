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
        Schema::create('campo_seccion_formulario_usuario', function (Blueprint $table) {
          $table->id();
          $table->integer('seccion_id');
          $table->integer('campo_id');
          $table->boolean('requerido')->default(0);
          $table->string('class',200)->default('col-12');
          $table->string('informacion_de_apoyo')->nullable();
          $table->smallInteger('orden')->default(0);
          $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campo_seccion_formulario_usuario');
    }
};
