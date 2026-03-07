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
        Schema::create('tipos_elemento_formulario_actividad', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->boolean('tiene_respuesta');
            $table->string('clase');
            $table->string('componente_vue')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipos_elemento_formulario_actividad');
    }
};
