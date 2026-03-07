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
        Schema::create('puntos_de_pago', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->integer('sede_id');
            // El encargado del Punto de Pago (Administrador)
            $table->integer('encargado_id')->nullable();
            $table->boolean('estado')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('puntos_de_pago');
    }
};
