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
         Schema::create('diplomas', function (Blueprint $table) {
            $table->id(); // ID único del diploma
            $table->string('nombre', 100); // Nombre del diploma
            $table->string('logo1', 30)->nullable(); // Logo 1
            $table->string('logo2', 30)->nullable(); // Logo 2
            $table->string('encabezado1', 100)->nullable(); // Encabezado 1
            $table->string('encabezado2', 100)->nullable(); // Encabezado 2
            $table->string('encabezado3', 100)->nullable(); // Encabezado 3
            $table->string('titulo', 100)->nullable(); // Título del diploma
            $table->text('descripcion1')->nullable(); // Descripción 1
            $table->text('descripcion2')->nullable(); // Descripción 2
            $table->string('firma1', 30)->nullable(); // Firma 1
            $table->string('nombre_firma_1', 100)->nullable(); // Nombre de la firma 1
            $table->string('cargo_firma_1', 50)->nullable(); // Cargo de la firma 1
            $table->string('firma2', 30)->nullable(); // Firma 2
            $table->string('nombre_firma_2', 100)->nullable(); // Nombre de la firma 2
            $table->string('cargo_firma_2', 50)->nullable(); // Cargo de la firma 2
            $table->string('fondo', 30)->nullable(); // Fondo del diploma
            $table->timestamps(); // Fechas de creación y actualización
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diplomas');
    }
};
