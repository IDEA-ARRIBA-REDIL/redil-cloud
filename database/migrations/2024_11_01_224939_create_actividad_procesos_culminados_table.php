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
        Schema::create('actividad_procesos_culminados', function (Blueprint $table) {
            $table->id();
            $table->integer('actividad_id');
            $table->integer('paso_crecimiento_id');
             $table->integer('estado_paso_crecimiento_usuario_id');
            $table->integer('estado');
            $table->integer('indice');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actividad_procesos_culminados');
    }
};
