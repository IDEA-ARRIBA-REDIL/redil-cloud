<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asesores_pdp', function (Blueprint $table) {
            $table->id();

            // Vincula al usuario único
            $table->foreignId('user_id')->constrained('users')->unique();

            // Nuevos campos para diferenciar el tipo de asesor
            $table->boolean('es_cajero')->default(false)->comment('Define si es un asesor de tipo Cajero');
            $table->boolean('es_encargado')->default(false)->comment('Define si es un asesor de tipo Encargado de PDP');

            // Campos idénticos al ejemplo de Maestro
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);

            $table->timestamps();
            $table->softDeletes(); // Buena práctica
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asesores_pdp');
    }
};
