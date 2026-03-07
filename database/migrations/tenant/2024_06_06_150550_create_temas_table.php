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
        Schema::create('temas', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('titulo', 100)->nullable();
            $table->string('portada',500)->default("default.png")->nullable();
            $table->string('url', 500)->nullable();
            $table->boolean('estado')->default(0)->nullable();
            $table->text('contenido');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temas');
    }
};
