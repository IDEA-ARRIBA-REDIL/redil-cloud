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
            // ID del usuario que aprobó la materia
            $table->unsignedBigInteger('user_id');
            // ID de la materia período aprobada
         
            $table->integer('nivel_id');
            // Indicador de aprobación (true: aprobado, false: no aprobado)
            $table->boolean('aprobado')->default(false);
            $table->timestamps();

            // Claves foráneas
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
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
