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
        Schema::create('materia_niveles_escuelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nivel_id')->constrained('niveles_escuelas')->onDelete('cascade');
            $table->foreignId('escuela_id')->constrained('escuelas')->onDelete('cascade');
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materia_niveles_escuelas');
    }
};
