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
        Schema::create('materia_proceso_prerrequisito', function (Blueprint $table) {
            $table->id();
            $table->integer('materia_id');
            $table->integer('paso_crecimiento_id');
            $table->integer('estado_proceso');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materia_proceso_prerrequisito');
    }
};
