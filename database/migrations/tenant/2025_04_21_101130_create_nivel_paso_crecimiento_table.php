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
        Schema::create('nivel_paso_crecimiento', function (Blueprint $table) {
            $table->id();
            $table->integer('nivel_id');
            $table->integer('paso_crecimiento_id');
            $table->boolean('al_iniciar')->nullable();
            $table->integer('estado')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nivel_paso_crecimiento');
    }
};
