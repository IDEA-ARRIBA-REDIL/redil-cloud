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
        Schema::create('materia_paso_crecimiento', function (Blueprint $table) {
            $table->id();
            $table->integer('paso_crecimiento_id');
            $table->integer('materia_id');
            $table->integer('estado')->nullable();
            $table->integer('al_iniciar')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materia_paso_crecimiento');
    }
};
