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
        Schema::create('entidades_relacionadas', function (Blueprint $column) {
            $column->id();
            $column->string('nombre');
            $column->string('nit')->nullable();
            $column->string('direccion')->nullable();
            $column->string('telefono')->nullable();
            $column->string('representante_legal')->nullable();
            $column->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entidades_relacionadas');
    }
};
