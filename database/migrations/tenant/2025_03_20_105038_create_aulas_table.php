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
         Schema::create('aulas', function (Blueprint $table) {
            $table->id(); // ID único del aula
            $table->string('nombre', 100); // Nombre del aula (ej: "Aula 101")
            $table->string('direccion', 100)->nullable();
            $table->text('descripcion')->nullable(); // Descripción del aula
            $table->integer('sede_id')->default(1);
            $table->integer('tipo_aula_id')->default(1);
            $table->boolean('activo')->default(TRUE);
            $table->timestamps(); // Fechas de creación y actualización
            $table->softDeletes(); // Soft deletes para mantener historial
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aulas');
    }
};
